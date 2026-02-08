<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait PreventsDuplicateSubmissions
{
    protected function claimIdempotency(Request $request, string $scope, int $ttlSeconds = 600): bool
    {
        $key = $this->getIdempotencyCacheKey($request, $scope);
        if (!$key) {
            return true;
        }

        return Cache::add($key, true, $ttlSeconds);
    }

    protected function releaseIdempotency(Request $request, string $scope): void
    {
        $key = $this->getIdempotencyCacheKey($request, $scope);
        if ($key) {
            Cache::forget($key);
        }
    }

    protected function getIdempotencyCacheKey(Request $request, string $scope): ?string
    {
        $idempotencyKey = (string) $request->input('idempotency_key', '');
        if ($idempotencyKey === '') {
            return null;
        }

        $userPart = Auth::id() ?: $request->ip();
        return "idempotency:{$scope}:{$userPart}:{$idempotencyKey}";
    }
}
