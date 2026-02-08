<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class CustomerAuthProvider implements UserProvider
{
    protected $model;

    public function __construct()
    {
        $this->model = Customer::class;
    }

    public function retrieveById($identifier)
    {
        return $this->model::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        $model = $this->model::find($identifier);
        
        if (!$model) {
            return null;
        }

        $rememberToken = $model->getRememberToken();
        
        return $rememberToken && hash_equals($rememberToken, $token) ? $model : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        if (method_exists($user, 'setRememberToken')) {
            $user->setRememberToken($token);
            
            // Only save if remember_token column exists
            if (Schema::hasColumn('customers', 'remember_token')) {
                $user->save();
            }
        }
    }

public function retrieveByCredentials(array $credentials)
{
    if (empty($credentials) || 
        (count($credentials) === 1 && 
         array_key_exists('password', $credentials))) {
        return null;
    }

    $identifier = trim($credentials['username'] ?? '');
    
    if (empty($identifier)) {
        return null;
    }

    // Method 1: Direct Customer ID lookup
    if (preg_match('/^\d+$/', $identifier)) {
        $customerId = (int) $identifier;
        $customer = $this->model::find($customerId);
        if ($customer && !empty($customer->phone)) {
            return $customer;
        }
    }

    // Method 2: Username format (firstname + customer ID)
    // Updated regex to allow dots, hyphens, and underscores in the name part
    if (preg_match('/^([a-zA-Z.\-_]+)(\d+)$/', $identifier, $matches)) {
        $firstName = strtolower($matches[1]);
        $customerId = (int) $matches[2];

        $customer = $this->model::find($customerId);
        if ($customer && !empty($customer->phone)) {
            // Extract first name and handle dots/hyphens
            $customerFirstName = strtolower(explode(' ', trim($customer->name))[0]);
            // Remove any trailing dots or special characters for comparison
            $customerFirstName = rtrim($customerFirstName, '.-_');
            $firstName = rtrim($firstName, '.-_');
            
            if ($customerFirstName === $firstName) {
                return $customer;
            }
        }
    }

    // Method 3: Search by first name only (including dots and hyphens)
    if (preg_match('/^[a-zA-Z.\-_]+$/', $identifier)) {
        $firstName = strtolower($identifier);
        
        // Handle names with dots or hyphens
        $searchName = rtrim($firstName, '.-_');
        
        $customers = $this->model::whereRaw('LOWER(SUBSTRING_INDEX(REPLACE(REPLACE(name, ".", ""), "-", ""), " ", 1)) = ?', [$searchName])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();
        
        if ($customers->count() === 1) {
            return $customers->first();
        }
        
        // Also try exact match with dots/hyphens
        if ($customers->count() === 0) {
            $customers = $this->model::whereRaw('LOWER(SUBSTRING_INDEX(name, " ", 1)) = ?', [$firstName])
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();
            
            if ($customers->count() === 1) {
                return $customers->first();
            }
        }
    }

    return null;
}

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $inputPassword = $credentials['password'] ?? '';
        $customerPhone = $user->phone ?? '';

        if (!empty($user->password)) {
            return Hash::check($inputPassword, $user->password);
        }
        
        if (empty($customerPhone) || empty($inputPassword)) {
            return false;
        }

        // Clean both phone numbers
        $cleanInput = preg_replace('/[^0-9+]/', '', $inputPassword);
        $cleanStored = preg_replace('/[^0-9+]/', '', $customerPhone);
        
        // Direct match
        if ($cleanStored === $cleanInput) {
            return true;
        }
        
        // Try variations
        $variations = [
            $inputPassword,
            $cleanInput,
            ltrim($cleanInput, '+88'),
            ltrim($cleanInput, '88'),
            ltrim($cleanInput, '+'),
            '88' . ltrim($cleanInput, '+88'),
            '+88' . ltrim($cleanInput, '+88'),
        ];
        
        foreach ($variations as $variation) {
            $cleanVariation = preg_replace('/[^0-9+]/', '', $variation);
            if ($cleanStored === $cleanVariation) {
                return true;
            }
        }
        
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, $force = false)
    {
        // Do nothing - we don't hash phone numbers
    }
}
