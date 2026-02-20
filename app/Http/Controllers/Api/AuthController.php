<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user and return token
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'sometimes|string', // Optional for mobile apps
            'tenant_id' => 'nullable|integer|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $requestedTenantId = $request->filled('tenant_id') ? (int) $request->tenant_id : null;
        $selectedTenantId = $requestedTenantId ?: ($user->tenant_id ? (int) $user->tenant_id : null);

        if ($selectedTenantId) {
            if (method_exists($user, 'canAccessTenant') && !$user->canAccessTenant($selectedTenantId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to the selected company.'
                ], 403);
            }
        }

        // Delete all existing tokens for this user (optional - for single session)
        $user->tokens()->delete();

        // Create new token with device name
        $deviceName = $request->device_name ?? 'mobile-app';
        $tokenName = $this->buildTokenName($deviceName, $selectedTenantId);
        $token = $user->createToken($tokenName)->plainTextToken;

        $tenant = null;
        if ($selectedTenantId) {
            $tenant = Tenant::query()->find($selectedTenantId, ['id', 'name']);
        }

        $availableTenants = $this->tenantOptionsForUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'selected_tenant_id' => $selectedTenantId,
                'selected_tenant_name' => $tenant?->name,
                'available_tenants' => $availableTenants,
            ]
        ], 200);
    }

    public function loginCompanies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Company list loaded',
            'data' => [
                'companies' => $this->tenantOptionsForUser($user),
            ]
        ], 200);
    }

    /**
     * Get authenticated user details
     */
    public function profile(Request $request)
    {
        $tenantId = $this->tenantIdFromTokenName((string) ($request->user()?->currentAccessToken()?->name ?? ''));
        if (!$tenantId && $request->user()?->tenant_id) {
            $tenantId = (int) $request->user()->tenant_id;
        }

        $tenantName = null;
        if ($tenantId) {
            $tenantName = Tenant::query()->whereKey($tenantId)->value('name');
        }

        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'meta' => [
                'selected_tenant_id' => $tenantId,
                'selected_tenant_name' => $tenantName,
            ],
        ], 200);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $currentTokenName = (string) ($request->user()?->currentAccessToken()?->name ?? 'mobile-app');
        $tenantId = $this->tenantIdFromTokenName($currentTokenName);
        $deviceName = $this->deviceNameFromTokenName($currentTokenName);
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken($this->buildTokenName($deviceName, $tenantId))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'selected_tenant_id' => $tenantId,
            ]
        ], 200);
    }

    protected function buildTokenName(string $deviceName, ?int $tenantId): string
    {
        $name = trim($deviceName) !== '' ? trim($deviceName) : 'mobile-app';
        if ($tenantId) {
            return $name . '|tenant:' . $tenantId;
        }

        return $name;
    }

    protected function tenantIdFromTokenName(string $tokenName): ?int
    {
        if (preg_match('/\|tenant:(\d+)$/', $tokenName, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function deviceNameFromTokenName(string $tokenName): string
    {
        $tenantId = $this->tenantIdFromTokenName($tokenName);
        if (!$tenantId) {
            return $tokenName !== '' ? $tokenName : 'mobile-app';
        }

        return str_replace('|tenant:' . $tenantId, '', $tokenName);
    }

    protected function tenantOptionsForUser(User $user): array
    {
        $tenantIds = collect();

        if ($user->tenant_id) {
            $tenantIds->push((int) $user->tenant_id);
        }

        if ($user->hasRole('Super Admin') && method_exists($user, 'tenants')) {
            $extraTenantIds = $user->tenants()
                ->pluck('tenants.id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $tenantIds = $tenantIds->merge($extraTenantIds);
        }

        $tenantIds = $tenantIds->unique()->values();

        if ($tenantIds->isEmpty()) {
            return [];
        }

        return Tenant::query()
            ->whereIn('id', $tenantIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($tenant) => ['id' => (int) $tenant->id, 'name' => $tenant->name])
            ->values()
            ->all();
    }
}
