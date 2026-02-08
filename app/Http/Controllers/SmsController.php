<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Models\SmsSettings;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        
        $this->middleware('permission:sms-manage', ['except' => ['dashboard']]);
        $this->middleware('permission:sms-view|sms-manage', ['only' => ['dashboard']]);
    }

    /**
     * SMS Dashboard
     */
    public function dashboard()
    {
        $stats = $this->smsService->getDashboardStats();
        $providers = SmsSettings::all();
        $recentLogs = SmsLog::with(['user', 'sendable'])
                           ->latest()
                           ->limit(10)
                           ->get();

        return view('sms.dashboard', compact('stats', 'providers', 'recentLogs'));
    }

    /**
     * SMS Settings Index
     */
    public function index()
    {
        $providers = SmsSettings::all();
        return view('sms.settings.index', compact('providers'));
    }

    /**
     * Create SMS Provider
     */
    public function create()
    {
        return view('sms.settings.create');
    }

    /**
     * Store SMS Provider
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|unique:sms_settings,provider',
            'provider_name' => 'required|string|max:255',
            'api_token' => 'required|string',
            'api_url' => 'required|url',
            'sender_id' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sms_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // If setting as active, deactivate others
        if ($request->is_active) {
            SmsSettings::where('is_active', true)->update(['is_active' => false]);
        }

        $provider = SmsSettings::create($request->all());

        return redirect()->route('sms.settings.index')
                        ->with('success', 'SMS provider created successfully.');
    }

    /**
     * Edit SMS Provider
     */
    public function edit(SmsSettings $smsSettings)
    {
        return view('sms.settings.edit', compact('smsSettings'));
    }

    /**
     * Update SMS Provider
     */
    public function update(Request $request, SmsSettings $smsSettings)
    {
        $validator = Validator::make($request->all(), [
            'provider_name' => 'required|string|max:255',
            'api_token' => 'required|string',
            'api_url' => 'required|url',
            'sender_id' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sms_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // If setting as active, deactivate others
        if ($request->is_active && !$smsSettings->is_active) {
            SmsSettings::where('is_active', true)->update(['is_active' => false]);
        }

        $smsSettings->update($request->all());

        return redirect()->route('sms.settings.index')
                        ->with('success', 'SMS provider updated successfully.');
    }

    /**
     * Delete SMS Provider
     */
    public function destroy(SmsSettings $smsSettings)
    {
        $smsSettings->delete();

        return redirect()->route('sms.settings.index')
                        ->with('success', 'SMS provider deleted successfully.');
    }

    /**
     * Toggle SMS Enable/Disable
     */
    public function toggleSms(Request $request)
    {
        $provider = SmsSettings::find($request->provider_id);
        
        if ($provider) {
            $provider->update(['sms_enabled' => !$provider->sms_enabled]);
            
            $status = $provider->sms_enabled ? 'enabled' : 'disabled';
            return response()->json([
                'success' => true,
                'message' => "SMS {$status} successfully",
                'status' => $status
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Provider not found'], 404);
    }

    /**
     * Set Active Provider
     */
    public function setActive(Request $request)
    {
        $provider = SmsSettings::find($request->provider_id);
        
        if ($provider) {
            // Deactivate all others
            SmsSettings::where('is_active', true)->update(['is_active' => false]);
            
            // Activate this one
            $provider->update(['is_active' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Provider activated successfully'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Provider not found'], 404);
    }

    /**
     * Check Balance
     */
public function checkBalance(Request $request)
{
    try {
        Log::info("Balance check requested", [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        // Check if SMS service is available
        $smsService = app(SmsService::class);
        
        if (!$smsService) {
            Log::error("SMS Service not available");
            return response()->json([
                'success' => false,
                'message' => 'SMS service not available'
            ], 500);
        }

        // Get active provider
        $activeProvider = SmsSettings::getActiveProvider();

        if (!$activeProvider) {
            Log::warning("No active SMS provider found");
            return response()->json([
                'success' => false,
                'message' => 'No active SMS provider configured'
            ], 422);
        }

        if (!$activeProvider->api_token) {
            Log::warning("Active provider has no API token", ['provider' => $activeProvider->provider]);
            return response()->json([
                'success' => false,
                'message' => 'API token not configured for active provider'
            ], 422);
        }

        Log::info("Checking balance for provider", [
            'provider' => $activeProvider->provider,
            'provider_name' => $activeProvider->provider_name
        ]);

        // Attempt balance check
        $result = $smsService->getBalance();
        
        Log::info("Balance check result", [
            'success' => $result['success'] ?? false,
            'error' => $result['error'] ?? null,
            'balance' => $result['balance'] ?? 'N/A'
        ]);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'balance' => $result['balance'],
                'provider' => $activeProvider->provider_name,
                'message' => 'Balance updated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to check balance'
            ], 500);
        }

    } catch (\Exception $e) {
        Log::error("Balance check exception", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while checking balance',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Get Statistics
     */
    public function getStatistics(Request $request)
    {
        $result = $this->smsService->getStatistics();
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error']
        ], 500);
    }

    /**
     * SMS Logs
     */
    public function logs(Request $request)
    {
        if ($request->ajax()) {
            $query = SmsLog::with(['user', 'sendable'])
                          ->select(['id', 'provider', 'phone', 'message', 'status', 'cost', 'user_id', 'created_at']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('phone_formatted', function($row) {
                    return $row->formatted_phone;
                })
                ->addColumn('message_truncated', function($row) {
                    return $row->getTruncatedMessageAttribute(50);
                })
                ->addColumn('status_badge', function($row) {
                    return '<span class="badge badge-' . $row->status_color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('user_name', function($row) {
                    return $row->user ? $row->user->name : 'System';
                })
                ->addColumn('cost_formatted', function($row) {
                    return '৳' . number_format($row->cost, 4);
                })
                ->addColumn('date_formatted', function($row) {
                    return $row->created_at->format('M d, Y H:i');
                })
                ->addColumn('actions', function($row) {
                    return '<button class="btn btn-sm btn-info view-details" data-id="' . $row->id . '" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('sms.logs.index');
    }

    /**
     * Get SMS Log Details
     */
    public function getLogDetails(SmsLog $smsLog)
    {
        $smsLog->load(['user', 'sendable']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $smsLog->id,
                'provider' => $smsLog->provider,
                'phone' => $smsLog->phone,
                'message' => $smsLog->message,
                'status' => $smsLog->status,
                'response' => $smsLog->response,
                'reference_id' => $smsLog->reference_id,
                'cost' => $smsLog->cost,
                'user' => $smsLog->user ? $smsLog->user->name : 'System',
                'sendable_type' => $smsLog->sendable_type,
                'sendable_id' => $smsLog->sendable_id,
                'created_at' => $smsLog->created_at->format('M d, Y H:i:s'),
                'updated_at' => $smsLog->updated_at->format('M d, Y H:i:s')
            ]
        ]);
    }

    /**
     * Test SMS
     */
    public function testSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->smsService->sendSms(
            $request->phone, 
            $request->message, 
            null, 
            Auth::id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully',
                'provider' => $result['provider']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error']
        ], 500);
    }

    /**
     * Bulk SMS Send
     */
    public function bulkSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($request->phones as $phone) {
            $result = $this->smsService->sendSms(
                $phone, 
                $request->message, 
                null, 
               Auth::id()
            );

            $results[] = [
                'phone' => $phone,
                'success' => $result['success'],
                'message' => $result['success'] ? 'Sent' : $result['error']
            ];

            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'summary' => [
                'total' => count($request->phones),
                'successful' => $successful,
                'failed' => $failed
            ]
        ]);
    }
}