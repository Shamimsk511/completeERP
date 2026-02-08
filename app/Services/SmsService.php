<?php

namespace App\Services;

use App\Models\SmsSettings;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsService
{
    protected $activeProvider;

    public function __construct()
    {
        $this->activeProvider = SmsSettings::getActiveProvider();
    }

    /**
     * Send SMS using the active provider
     */
    public function sendSms(string $phone, string $message, $sendable = null, $userId = null): array
    {
        // Check if SMS is globally enabled
        if (!SmsSettings::isSmsEnabled()) {
            //Log::info("SMS sending disabled globally");
            return [
                'success' => false,
                'provider' => 'disabled',
                'error' => 'SMS sending is currently disabled'
            ];
        }

        // Check if we have an active provider
        if (!$this->activeProvider) {
            Log::error("No active SMS provider configured");
            return [
                'success' => false,
                'provider' => 'none',
                'error' => 'No active SMS provider configured'
            ];
        }

        // Clean phone number
        $cleanPhone = $this->cleanPhoneNumber($phone);
        if (!$this->isValidBangladeshiPhone($cleanPhone)) {
            return [
                'success' => false,
                'provider' => $this->activeProvider->provider,
                'error' => 'Invalid phone number'
            ];
        }

        // Log the attempt
        $smsLog = SmsLog::create([
            'provider' => $this->activeProvider->provider,
            'phone' => $cleanPhone,
            'message' => $message,
            'status' => 'pending',
            'sendable_type' => $sendable ? get_class($sendable) : null,
            'sendable_id' => $sendable ? $sendable->id : null,
            'user_id' => $userId
        ]);

        try {
            $result = $this->sendViaBdBulkSms($cleanPhone, $message);
            
            // Update log with result
            $smsLog->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result['data'] ?? $result['error'] ?? ''),
                'reference_id' => $result['reference_id'] ?? null,
                'cost' => $result['cost'] ?? 0
            ]);

            // Update provider statistics if successful
            if ($result['success']) {
                $this->activeProvider->increment('total_sent');
                $this->activeProvider->increment('monthly_sent');
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());
            
            $smsLog->update([
                'status' => 'failed',
                'response' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'provider' => $this->activeProvider->provider,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via BD Bulk SMS
     */
protected function sendViaBdBulkSms(string $phone, string $message): array
{
    if (!$this->activeProvider->api_token) {
        throw new \Exception('BD Bulk SMS API token not configured');
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // Use the correct SMS sending endpoint: https://api.bdbulksms.net/api.php
        $smsUrl = 'https://api.bdbulksms.net/api.php';
        
        // Log::info("BD Bulk SMS Send Request", [
        //     'url' => $smsUrl,
        //     'phone' => $phone,
        //     'message_length' => strlen($message)
        // ]);

        // Try POST method first (recommended)
        $response = Http::timeout(30)->post($smsUrl, [
            'token' => $token,
            'to' => $phone,
            'message' => $message
        ]);

        // Log::info("BD Bulk SMS Send Response", [
        //     'status' => $response->status(),
        //     'body' => $response->body(),
        //     'successful' => $response->successful()
        // ]);

        if ($response->successful()) {
            $responseBody = $response->body();
            
            // Check for success indicators
            $lowerResponse = strtolower($responseBody);
            
            if (strpos($lowerResponse, 'success') !== false || 
                strpos($lowerResponse, 'sent') !== false ||
                strpos($lowerResponse, 'ok') !== false ||
                strpos($responseBody, '100') !== false ||
                is_numeric(trim($responseBody))) { // BD Bulk SMS often returns numeric message ID
                
                return [
                    'success' => true,
                    'provider' => 'bdbulksms',
                    'data' => $responseBody,
                    'reference_id' => is_numeric(trim($responseBody)) ? trim($responseBody) : null,
                    'cost' => 0.50 // Default cost estimate
                ];
            } else {
                return [
                    'success' => false,
                    'provider' => 'bdbulksms',
                    'error' => $responseBody ?: 'Unknown error from BD Bulk SMS',
                    'data' => $responseBody
                ];
            }
        } else {
            // Try GET method as fallback
            $getUrl = $smsUrl . '?' . http_build_query([
                'token' => $token,
                'to' => $phone,
                'message' => $message
            ]);
            
            $getResponse = Http::timeout(30)->get($getUrl);
            
            if ($getResponse->successful()) {
                $responseBody = $getResponse->body();
                $lowerResponse = strtolower($responseBody);
                
                if (strpos($lowerResponse, 'success') !== false || 
                    strpos($lowerResponse, 'sent') !== false ||
                    strpos($lowerResponse, 'ok') !== false ||
                    is_numeric(trim($responseBody))) {
                    
                    return [
                        'success' => true,
                        'provider' => 'bdbulksms',
                        'data' => $responseBody,
                        'reference_id' => is_numeric(trim($responseBody)) ? trim($responseBody) : null,
                        'cost' => 0.50
                    ];
                }
            }
            
            return [
                'success' => false,
                'provider' => 'bdbulksms',
                'error' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()
            ];
        }

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Send Error: " . $e->getMessage());
        throw $e;
    }
}



 private function extractReferenceId(string $response): ?string
{
    // Try to extract reference/message ID from response
    $patterns = [
        '/id[:\s]*([0-9a-zA-Z]+)/i',
        '/reference[:\s]*([0-9a-zA-Z]+)/i',
        '/msg[:\s]*([0-9a-zA-Z]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $response, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}   /**
     * Get account balance
     */
   public function getBalance(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        switch ($this->activeProvider->provider) {
            case 'bdbulksms':
                return $this->getBdBulkSmsBalance();
                
            case 'greenweb':
                return $this->getGreenwebBalance();
                
            case 'ssl':
                return $this->getSslBalance();
                
            default:
                return ['success' => false, 'error' => 'Provider not supported for balance check'];
        }

    } catch (\Exception $e) {
        Log::error("Balance check failed: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


    /**
     * Get SMS statistics
     */
    public function getStatistics(): array
    {
        if (!$this->activeProvider || !$this->activeProvider->api_token) {
            return ['success' => false, 'error' => 'No API token configured'];
        }

        try {
            $response = Http::timeout(30)->get($this->activeProvider->api_url, [
                'token' => $this->activeProvider->api_token,
                'expiry' => '1',
                'rate' => '1',
                'tokensms' => '1',
                'totalsms' => '1',
                'monthlysms' => '1',
                'tokenmonthlysms' => '1',
                'json' => '1'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update stored data
                if (isset($data['expiry'])) {
                    $this->activeProvider->update([
                        'expiry_date' => Carbon::parse($data['expiry']),
                        'total_sent' => $data['tokensms'] ?? $this->activeProvider->total_sent,
                        'monthly_sent' => $data['tokenmonthlysms'] ?? $this->activeProvider->monthly_sent
                    ]);
                }

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return ['success' => false, 'error' => 'Failed to fetch statistics'];

        } catch (\Exception $e) {
            Log::error("Statistics fetch failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clean phone number
     */
    public function cleanPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($cleaned, 0, 3) === '880') {
            $cleaned = '0' . substr($cleaned, 3);
        } elseif (substr($cleaned, 0, 2) === '88') {
            $cleaned = substr($cleaned, 2);
        } elseif (substr($cleaned, 0, 1) !== '0') {
            $cleaned = '0' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Validate Bangladeshi phone number
     */
    public function isValidBangladeshiPhone(string $phone): bool
    {
        $phone = $this->cleanPhoneNumber($phone);
        return preg_match('/^01[3-9]\d{8}$/', $phone) === 1;
    }

    /**
     * Get SMS logs with pagination
     */
    public function getLogs($perPage = 15, $filters = [])
    {
        $query = SmsLog::with(['user', 'sendable'])
                       ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_sent' => SmsLog::successful()->count(),
            'total_failed' => SmsLog::failed()->count(),
            'today_sent' => SmsLog::successful()->today()->count(),
            'this_month_sent' => SmsLog::successful()->thisMonth()->count(),
            'balance' => $this->activeProvider ? $this->activeProvider->balance : 0,
            'provider' => $this->activeProvider ? $this->activeProvider->provider_name : 'None',
            'sms_enabled' => SmsSettings::isSmsEnabled()
        ];
    }
public function getBdBulkSmsBalance(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // Balance check URL: https://api.bdbulksms.net/g_api.php?token=TOKEN&balance&json
        $url = "https://api.bdbulksms.net/g_api.php?token={$token}&balance&json";

        Log::info("BD Bulk SMS Balance Check: " . $url);

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $responseBody = $response->body();
            
            // Try JSON first
            $data = json_decode($responseBody, true);
            
            if ($data && isset($data['balance'])) {
                $balance = (float) $data['balance'];
                
                $this->activeProvider->update([
                    'balance' => $balance,
                    'last_balance_check' => now()
                ]);

                return [
                    'success' => true,
                    'balance' => $balance,
                    'data' => $data
                ];
            } else {
                // Try plain text version
                $plainUrl = "https://api.bdbulksms.net/g_api.php?token={$token}&balance";
                $plainResponse = Http::timeout(30)->get($plainUrl);
                
                if ($plainResponse->successful()) {
                    $balance = $this->parseBalanceFromText($plainResponse->body());
                    if ($balance !== null) {
                        $this->activeProvider->update([
                            'balance' => $balance,
                            'last_balance_check' => now()
                        ]);

                        return [
                            'success' => true,
                            'balance' => $balance,
                            'data' => $plainResponse->body()
                        ];
                    }
                }
            }
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch balance: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Balance Check Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function getBdBulkSmsBalanceJson(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        // JSON format: https://api.bdbulksms.net/g_api.php?token=TOKEN&balance&json
        $url = "https://api.bdbulksms.net/g_api.php?token={$this->activeProvider->api_token}&balance&json";

        Log::info("BD Bulk SMS Balance Check (JSON) URL: " . $url);

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['balance'])) {
                $balance = (float) $data['balance'];
                
                $this->activeProvider->update([
                    'balance' => $balance,
                    'last_balance_check' => now()
                ]);

                return [
                    'success' => true,
                    'balance' => $balance,
                    'data' => $data
                ];
            }
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch balance: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Balance Check (JSON) Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function getBdBulkSmsStatistics(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // All statistics with JSON: https://api.bdbulksms.net/g_api.php?token=TOKEN&expiry&rate&tokensms&totalsms&monthlysms&tokenmonthlysms&json
        $url = "https://api.bdbulksms.net/g_api.php?token={$token}&expiry&rate&tokensms&totalsms&monthlysms&tokenmonthlysms&json";

        Log::info("BD Bulk SMS Statistics: " . $url);

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            
            if ($data) {
                // Update stored data
                $updateData = [];
                
                if (isset($data['balance'])) {
                    $updateData['balance'] = (float) $data['balance'];
                }
                
                if (isset($data['expiry'])) {
                    try {
                        $updateData['expiry_date'] = Carbon::parse($data['expiry']);
                    } catch (\Exception $e) {
                        Log::warning("Could not parse expiry date: " . $data['expiry']);
                    }
                }
                
                if (isset($data['tokensms'])) {
                    $updateData['total_sent'] = (int) $data['tokensms'];
                }
                
                if (isset($data['tokenmonthlysms'])) {
                    $updateData['monthly_sent'] = (int) $data['tokenmonthlysms'];
                }
                
                if (!empty($updateData)) {
                    $this->activeProvider->update($updateData);
                }

                return [
                    'success' => true,
                    'data' => $data
                ];
            }
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch statistics: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Statistics Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function getBdBulkSmsRate(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // Rate check: https://api.bdbulksms.net/g_api.php?token=TOKEN&rate
        $url = "https://api.bdbulksms.net/g_api.php?token={$token}&rate";

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            return [
                'success' => true,
                'rate' => $response->body(),
                'data' => $response->body()
            ];
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch rate: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Rate Check Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

private function getGreenwebBalance(): array
{
    // GreenWeb doesn't have a balance check API, so return stored balance
    return [
        'success' => true,
        'balance' => $this->activeProvider->balance,
        'data' => ['message' => 'Balance check not supported by GreenWeb. Showing stored balance.']
    ];
}
public function getBdBulkSmsTokenCount(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // Token SMS count: https://api.bdbulksms.net/g_api.php?token=TOKEN&tokensms
        $url = "https://api.bdbulksms.net/g_api.php?token={$token}&tokensms";

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $count = (int) trim($response->body());
            
            $this->activeProvider->update(['total_sent' => $count]);
            
            return [
                'success' => true,
                'token_sms_count' => $count,
                'data' => $response->body()
            ];
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch token SMS count: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Token Count Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function getBdBulkSmsMonthlyCount($month = null): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        if ($month) {
            // Specific month: https://api.bdbulksms.net/g_api.php?token=TOKEN&tokenmonthlysms=06-2025
            $url = "https://api.bdbulksms.net/g_api.php?token={$token}&tokenmonthlysms={$month}";
        } else {
            // Current month: https://api.bdbulksms.net/g_api.php?token=TOKEN&tokenmonthlysms
            $url = "https://api.bdbulksms.net/g_api.php?token={$token}&tokenmonthlysms";
        }

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $count = (int) trim($response->body());
            
            if (!$month) {
                $this->activeProvider->update(['monthly_sent' => $count]);
            }
            
            return [
                'success' => true,
                'monthly_sms_count' => $count,
                'month' => $month ?: 'current',
                'data' => $response->body()
            ];
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch monthly SMS count: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Monthly Count Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function getBdBulkSmsExpiry(): array
{
    if (!$this->activeProvider || !$this->activeProvider->api_token) {
        return ['success' => false, 'error' => 'No API token configured'];
    }

    try {
        $token = $this->activeProvider->api_token;
        
        // Expiry check: https://api.bdbulksms.net/g_api.php?token=TOKEN&expiry
        $url = "https://api.bdbulksms.net/g_api.php?token={$token}&expiry";

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            $expiryText = trim($response->body());
            
            try {
                $expiryDate = Carbon::parse($expiryText);
                $this->activeProvider->update(['expiry_date' => $expiryDate]);
                
                return [
                    'success' => true,
                    'expiry_date' => $expiryDate->toDateString(),
                    'expiry_formatted' => $expiryDate->format('M d, Y'),
                    'days_remaining' => $expiryDate->diffInDays(now()),
                    'data' => $expiryText
                ];
            } catch (\Exception $e) {
                return [
                    'success' => true,
                    'expiry_raw' => $expiryText,
                    'data' => $expiryText,
                    'note' => 'Could not parse expiry date format'
                ];
            }
        }

        return [
            'success' => false, 
            'error' => 'Failed to fetch expiry: ' . $response->body()
        ];

    } catch (\Exception $e) {
        Log::error("BD Bulk SMS Expiry Check Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
private function parseBalanceFromText(string $text): ?float
{
    // Remove any non-numeric characters except decimal point
    $cleanText = preg_replace('/[^0-9.]/', '', $text);
    
    if (is_numeric($cleanText)) {
        return (float) $cleanText;
    }
    
    // Try other patterns
    $patterns = [
        '/balance[:\s]*([0-9]+\.?[0-9]*)/i',
        '/([0-9]+\.?[0-9]*)\s*taka/i',
        '/([0-9]+\.?[0-9]*)\s*tk/i',
        '/([0-9]+\.?[0-9]*)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return (float) $matches[1];
        }
    }
    
    return null;
}
public function testAllBdBulkEndpoints(): array
{
    if (!$this->activeProvider || $this->activeProvider->provider !== 'bdbulksms') {
        return ['success' => false, 'error' => 'BD Bulk SMS provider not active'];
    }

    $results = [];
    
    // Test balance check
    $results['balance'] = $this->getBdBulkSmsBalance();
    
    // Test rate check
    $results['rate'] = $this->getBdBulkSmsRate();
    
    // Test token SMS count
    $results['token_count'] = $this->getBdBulkSmsTokenCount();
    
    // Test monthly count
    $results['monthly_count'] = $this->getBdBulkSmsMonthlyCount();
    
    // Test expiry
    $results['expiry'] = $this->getBdBulkSmsExpiry();
    
    // Test all statistics
    $results['all_statistics'] = $this->getBdBulkSmsStatistics();
    
    return [
        'success' => true,
        'provider' => 'bdbulksms',
        'token_preview' => substr($this->activeProvider->api_token, 0, 8) . '...' . substr($this->activeProvider->api_token, -4),
        'tests' => $results,
        'summary' => [
            'total_tests' => count($results),
            'successful_tests' => count(array_filter($results, function($test) {
                return $test['success'] ?? false;
            }))
        ]
    ];
}
}