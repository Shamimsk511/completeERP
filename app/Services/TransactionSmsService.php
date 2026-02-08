<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Xenon\LaravelBDSms\Facades\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Xenon\LaravelBDSms\Provider\Ssl;
use Illuminate\Support\Facades\Cache;
use Xenon\LaravelBDSms\Provider\GreenWeb;

class TransactionSmsService
{
    protected $greenwebApiKey;
    protected $sslApiKey;
    protected $businessName;
    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
        $this->greenwebApiKey = config('services.sms.greenweb.key');
        $this->sslApiKey = config('services.sms.ssl.key');
        $this->businessName = config('app.business_name', 'Your Business');
    }

    public function sendTransactionSms(Transaction $transaction)
    {
        try {
            // Load relationships
            if (!$transaction->relationLoaded('customer')) {
                $transaction->load('customer');
            }
            if (!$transaction->relationLoaded('invoice') && $transaction->invoice_id) {
                $transaction->load('invoice');
            }
            
            $customer = $transaction->customer;
            
            // Skip if no phone number
            if (!$customer || !$customer->phone) {
                Log::info("Skipping SMS for transaction {$transaction->id}: No phone number");
                return false;
            }
            
            // Clean and validate phone number
            $phone = $this->cleanPhoneNumber($customer->phone);
            if (!$this->isValidBangladeshiPhone($phone)) {
                Log::warning("Invalid phone number for customer {$customer->id}: {$customer->phone}");
                return false;
            }
            
            // Trigger payment allocation
            $this->paymentAllocationService->allocatePayments($customer->id);
            
            // For debit transactions, check for recent credit
            if ($transaction->type === 'debit' && $transaction->invoice_id) {
                $invoice = $transaction->invoice;
                
                $creditTransaction = Transaction::where('invoice_id', $invoice->id)
                    ->where('type', 'credit')
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->where('id', '<', $transaction->id)
                    ->first();
                
                if ($creditTransaction) {
                    Log::info("Skipping payment SMS for transaction {$transaction->id}: Combined SMS will be sent");
                    return false;
                }
            }
            
            // For credit transactions, check for recent debit
            if ($transaction->type === 'credit' && $transaction->invoice_id) {
                $invoice = $transaction->invoice;
                
                $debitTransaction = Transaction::where('invoice_id', $invoice->id)
                    ->where('type', 'debit')
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->where('id', '>', $transaction->id)
                    ->first();
                
                $message = $this->generateInvoiceSmsMessage($transaction, $this->getBusinessName(), $debitTransaction);
            } else {
                $message = $this->generateSmsMessage($transaction);
            }
            
            // Send SMS using old method
            $response = $this->sendSms($phone, $message);
            
            if ($response['success']) {
                Log::info("SMS sent for transaction {$transaction->id} to {$phone}", [
                    'provider' => $response['provider'],
                    'response' => $response['data']
                ]);
                return true;
            } else {
                Log::error("SMS failed for transaction {$transaction->id}: " . $response['error']);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("SMS error for transaction {$transaction->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification for product return
     * 
     * @param \App\Models\ProductReturn $productReturn
     * @return bool
     */
    public function sendProductReturnSms($productReturn)
    {
        try {
            // Load customer relationship if not already loaded
            if (!$productReturn->relationLoaded('customer')) {
                $productReturn->load('customer');
            }
            
            $customer = $productReturn->customer;
            
            // Skip if customer doesn't have a phone number
            if (!$customer || !$customer->phone) {
                Log::info("Skipping SMS for product return {$productReturn->id}: Customer has no phone number");
                return false;
            }
            
            // Clean and validate phone number
            $phone = $this->cleanPhoneNumber($customer->phone);
            if (!$this->isValidBangladeshiPhone($phone)) {
                Log::warning("Invalid phone number for customer {$customer->id}: {$customer->phone}");
                return false;
            }
            
            // Generate SMS message for product return
            $message = $this->generateProductReturnSmsMessage($productReturn);
            
            // Send SMS
            $response = $this->sendSms($phone, $message);

            if ($response['success']) {
                Log::info("Product return SMS sent successfully for return {$productReturn->id} to {$phone}", [
                    'provider' => $response['provider'],
                    'response' => $response['data']
                ]);
                return true;
            } else {
                Log::error("Product return SMS failed for return {$productReturn->id}: " . $response['error']);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("Product return SMS sending error for return {$productReturn->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS using the shoot method
     * 
     * @param string $phone
     * @param string $message
     * @return array
     */
    public function sendSms(string $phone, string $message): array
    {
        try {
            // Log the attempt
            Log::info("Attempting to send SMS using shoot method to {$phone}", [
                'message' => substr($message, 0, 100) . '...'
            ]);

            // Use the shoot method from the Laravel BD SMS package
            $response = SMS::shoot($phone, $message);

            Log::info("SMS shoot response", [
                'response' => $response
            ]);

            // The shoot method typically returns a response object or array
            // Check if the response indicates success
            if ($response) {
                // Different providers may return different response formats
                // Check for common success indicators
                $responseStr = is_string($response) ? $response : json_encode($response);
                
                if (is_object($response) && isset($response->status) && $response->status == 'success') {
                   Log::info("SMS sent successfully via shoot method");
                    return [
                        'success' => true,
                        'provider' => 'laravel-bd-sms',
                        'data' => $responseStr
                    ];
                } elseif (is_array($response) && isset($response['status']) && $response['status'] == 'success') {
                    Log::info("SMS sent successfully via shoot method");
                    return [
                        'success' => true,
                        'provider' => 'laravel-bd-sms',
                        'data' => $responseStr
                    ];
                } elseif (strpos(strtolower($responseStr), 'success') !== false || 
                         strpos(strtolower($responseStr), 'ok') !== false ||
                         strpos(strtolower($responseStr), 'acceptd') !== false ||
                         strpos(strtolower($responseStr), 'submitted') !== false) {
                    Log::info("SMS sent successfully via shoot method");
                    return [
                        'success' => true,
                        'provider' => 'laravel-bd-sms',
                        'data' => $responseStr
                    ];
                }
                
                // If no clear success indicator, but we got a response, assume it might be successful
                Log::warning("SMS shoot method returned unclear response, treating as success", [
                    'response' => $responseStr
                ]);
                return [
                    'success' => true,
                    'provider' => 'laravel-bd-sms',
                    'data' => $responseStr
                ];
            } else {
                // No response or false response
                Log::error("SMS shoot method failed - no response");
                return [
                    'success' => false,
                    'provider' => 'laravel-bd-sms',
                    'error' => 'No response from SMS shoot method'
                ];
            }

        } catch (\Exception $e) {
            Log::error("SMS shoot method exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'provider' => 'laravel-bd-sms',
                'error' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clean phone number to proper format
     * 
     * @param string $phone
     * @return string
     */
    public function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure it starts with +88 or 0
        if (substr($cleaned, 0, 3) === '+88') {
            $cleaned = substr($cleaned, 3);
        } elseif (substr($cleaned, 0, 1) !== '0') {
            $cleaned = '0' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Validate if phone number is a valid Bangladeshi number
     * 
     * @param string $phone
     * @return bool
     */
    public function isValidBangladeshiPhone(string $phone): bool
    {
        $phone = $this->cleanPhoneNumber($phone);
        // Bangladeshi mobile numbers: 01[3-9] followed by 8 digits
        return preg_match('/^01[3-9]\d{8}$/', $phone) === 1;
    }

    /**
     * Generate SMS message for transaction
     * 
     * @param Transaction $transaction
     * @return string
     */
    private function generateSmsMessage($transaction)
    {
        $customer = $transaction->customer;
        $date = $transaction->created_at->format('d/m/Y');
        $amount = number_format($transaction->amount, 2);
        
        // Get business name from settings
        $businessName = $this->getBusinessName();
        
        // Check if transaction is related to an invoice
        if ($transaction->invoice_id) {
            return $this->generateInvoiceSmsMessage($transaction, $businessName);
        }
        
        // Generate regular transaction message
        if ($transaction->type === 'debit') {
            // Payment received
            $message = "{$businessName}: Payment received {$amount} on {$date}. ";
            
            if ($transaction->discount_amount > 0) {
                $discount = number_format($transaction->discount_amount, 2);
                $message .= "Discount: {$discount}. ";
            }
            
            // Calculate remaining balance
            $remainingBalance = $this->getCustomerBalance($customer->id);
            if ($remainingBalance > 0) {
                $message .= "Balance: " . number_format($remainingBalance, 2);
            } else if ($remainingBalance < 0) {
                $message .= "Credit: " . number_format(abs($remainingBalance), 2);
            } else {
                $message .= "Paid in full.";
            }
            
        } else {
            // Invoice/charge created
            $message = "{$businessName}: Invoice {$amount} on {$date}. ";
            
            if ($transaction->reference) {
                $message .= "Ref: {$transaction->reference}. ";
            }
            
            // Calculate balance after this charge
            $remainingBalance = $this->getCustomerBalance($customer->id);
            $message .= "Due: " . number_format($remainingBalance, 2);
        }
        
        // Ensure message is within 160 characters
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }
        
        return $message;
    }
    
    private function generateInvoiceSmsMessage($transaction, $businessName, $debitTransaction = null)
    {
        $invoice = $transaction->invoice;
        if (!$invoice) {
            return $this->generateSmsMessage($transaction);
        }
        
        $invoiceNumber = $invoice->invoice_number;
        $date = $invoice->invoice_date->format('d/m/Y');
        $totalAmount = number_format($invoice->total, 2); // After-discount total
        $customer = $invoice->customer;
        
        if ($transaction->type === 'credit') {
            // Calculate previous balance
            $previousBalance = $this->calculatePreviousBalance($customer, $invoice);
            $previousBalanceText = $previousBalance != 0 
                ? ($previousBalance > 0 
                    ? "Prev Due: " . number_format($previousBalance, 2) 
                    : "Prev Credit: " . number_format(abs($previousBalance), 2))
                : "";
            
            // Get current balance
            $currentBalance = $this->getCustomerBalance($customer->id);
            
            if ($debitTransaction) {
                $paidAmount = number_format($debitTransaction->amount + ($debitTransaction->discount_amount ?? 0), 2);
                
                $message = "{$businessName}: {$invoiceNumber} {$date}. Total: {$totalAmount}";
                if ($previousBalanceText) {
                    $message .= ", {$previousBalanceText}";
                }
                $message .= ", Paid: {$paidAmount}, Balance: " . number_format($currentBalance, 2);
                
                if (strlen($message) > 160) {
                    $message = "{$businessName}: {$invoiceNumber} Total:{$totalAmount}";
                    if ($previousBalanceText) {
                        $message .= ", {$previousBalanceText}";
                    }
                    $message .= ", Paid:{$paidAmount}, Bal:{$currentBalance}";
                    if (strlen($message) > 160) {
                        $message = substr($message, 0, 157) . '...';
                    }
                }
            } else {
                $message = "{$businessName}: {$invoiceNumber} {$date}. Total: {$totalAmount}";
                if ($previousBalanceText) {
                    $message .= ", {$previousBalanceText}";
                }
                $message .= ", Balance: " . number_format($currentBalance, 2);
                
                if (strlen($message) > 160) {
                    $message = "{$businessName}: {$invoiceNumber} Total:{$totalAmount}";
                    if ($previousBalanceText) {
                        $message .= ", {$previousBalanceText}";
                    }
                    $message .= ", Bal:{$currentBalance}";
                    if (strlen($message) > 160) {
                        $message = substr($message, 0, 157) . '...';
                    }
                }
            }
        } else {
            $paymentAmount = number_format($transaction->amount + ($transaction->discount_amount ?? 0), 2);
            $invoice->refresh();
            $customerBalance = $this->getCustomerBalance($customer->id);
            
            $message = "{$businessName}: Payment {$paymentAmount} for {$invoiceNumber}.";
            if ($invoice->due_amount <= 0) {
                $message .= " Invoice Paid. Balance: " . number_format($customerBalance, 2);
            } else {
                $message .= " Due: " . number_format($invoice->due_amount, 2) . ", Balance: " . number_format($customerBalance, 2);
            }
            
            if (strlen($message) > 160) {
                $message = "{$businessName}: Pay {$paymentAmount} for {$invoiceNumber}. Bal:{$customerBalance}";
                if (strlen($message) > 160) {
                    $message = substr($message, 0, 157) . '...';
                }
            }
        }
        
        return $message;
    }

    private function calculatePreviousBalance($customer, $invoice)
    {
        // Sum credits before this invoice
        $creditsBefore = Transaction::where('customer_id', $customer->id)
            ->where('type', 'credit')
            ->where(function ($query) use ($invoice) {
                $query->where('created_at', '<', $invoice->created_at)
                      ->orWhere(function ($subQuery) use ($invoice) {
                          $subQuery->where('created_at', '=', $invoice->created_at)
                                   ->where('id', '<', $invoice->id);
                      });
            })
            ->sum('amount'); // Exclude discount_amount

        // Sum debits before this invoice
        $debitsBefore = Transaction::where('customer_id', $customer->id)
            ->where('type', 'debit')
            ->where(function ($query) use ($invoice) {
                $query->where('created_at', '<', $invoice->created_at)
                      ->orWhere(function ($subQuery) use ($invoice) {
                          $subQuery->where('created_at', '=', $invoice->created_at)
                                   ->where('id', '<', $invoice->id);
                      });
            })
            ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
            ->value('total');

        return $creditsBefore - $debitsBefore;
    }

    private function generateProductReturnSmsMessage($productReturn)
    {
        $businessName = $this->getBusinessName();
        $returnNumber = $productReturn->return_number;
        $returnAmount = number_format($productReturn->total, 2);
        $date = $productReturn->return_date->format('d/m/Y');
        $outstandingBalance = number_format($productReturn->customer->outstanding_balance, 2);
        
        // Create return message
        $message = "{$businessName}: Return {$returnNumber} processed on {$date}. Return Amount: {$returnAmount}. Outstanding Balance: {$outstandingBalance}";
        
        // Ensure message is within 160 characters
        if (strlen($message) > 160) {
            // Create shorter version
            $message = "{$businessName}: Return{$returnNumber} {$date}. Amount:{$returnAmount} Balance:{$outstandingBalance}";
        }
        
        return $message;
    }
    
    /**
     * Get business name from settings
     * 
     * @return string
     */
    public function getBusinessName(): string
    {
        return $this->businessName;
    }
    
    private function getCustomerBalance($customerId)
    {
        $this->paymentAllocationService->allocatePayments($customerId);
        $customer = Customer::findOrFail($customerId);
        return $customer->outstanding_balance;
    }

    private function sendSmsWithCurl(string $phone, string $message): array
    {
        // Try GreenWeb with cURL
        $url = 'http://api.greenweb.com.bd/api.php?' . http_build_query([
            'token' => $this->greenwebApiKey,
            'to' => $phone,
            'message' => $message
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200 && strpos($response, 'Ok') !== false) {
            return [
                'success' => true,
                'provider' => 'greenweb_curl',
                'data' => $response
            ];
        }

        // Try SSL SMS with cURL and disabled SSL verification
        $url = 'https://sms.sslwireless.com/pushapi/dynamic/server.php?' . http_build_query([
            'api_key' => $this->sslApiKey,
            'msisdn' => $phone,
            'sms' => $message,
            'csms_id' => uniqid()
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            return [
                'success' => true,
                'provider' => 'ssl_curl',
                'data' => $response
            ];
        }

        return [
            'success' => false,
            'provider' => 'curl_failed',
            'error' => 'cURL failed. HTTP Code: ' . $httpCode . '. Error: ' . $curlError . '. Response: ' . $response
        ];
    }

    public function sendReminderSms(Customer $customer)
    {
        try {
            if (!$customer->phone) {
                return [
                    'success' => false,
                    'message' => 'Customer does not have a phone number.'
                ];
            }

            if ($customer->outstanding_balance <= 0) {
                return [
                    'success' => false,
                    'message' => 'Customer has no outstanding balance.'
                ];
            }

            $phone = $this->cleanPhoneNumber($customer->phone);
            if (!$this->isValidBangladeshiPhone($phone)) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number.'
                ];
            }

            // Check rate limiting
            $cacheKey = "sms_reminder_{$customer->id}";
            if (Cache::has($cacheKey)) {
                return [
                    'success' => false,
                    'message' => 'SMS already sent recently. Please wait before sending again.'
                ];
            }

            $businessName = $this->getBusinessName();
            $balance = number_format($customer->outstanding_balance, 0);
            
            // Shorten business name if too long (max 15 chars)
            if (strlen($businessName) > 15) {
                $businessName = substr($businessName, 0, 12) . '...';
            }
            
            $message = "প্রিয় গ্রাহক, {$businessName} এ {$balance} টাকা বাকি। দয়া করে পরিশোধ করুন।";

            // Ensure message is within 160 characters
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }

            // Send SMS using the old method
            $result = $this->sendSms($phone, $message);

            if ($result['success']) {
                // Store rate limit in cache for 1 hour
                Cache::put($cacheKey, true, now()->addHour());

                Log::info("Reminder SMS sent for customer {$customer->id} to {$phone}", [
                    'provider' => $result['provider'],
                    'response' => $result['data'] ?? null,
                    'message' => $message,
                    'balance' => $customer->outstanding_balance
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully.',
                    'provider' => $result['provider']
                ];
            } else {
                Log::error("Reminder SMS failed for customer {$customer->id}: " . $result['error'], [
                    'phone' => $phone,
                    'message' => $message
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . $result['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error sending reminder SMS for customer {$customer->id}: " . $e->getMessage(), [
                'phone' => $customer->phone ?? 'N/A',
                'stack' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while sending SMS.'
            ];
        }
    }
}
