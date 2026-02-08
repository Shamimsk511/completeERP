<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsSettings;

class SmsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default BD Bulk SMS provider
        SmsSettings::create([
            'provider' => 'bdbulksms',
            'provider_name' => 'BD Bulk SMS',
            'api_token' => '', // Will be filled by admin
            'api_url' => 'https://api.bdbulksms.net/g_api.php',
            'sender_id' => null,
            'is_active' => true,
            'sms_enabled' => false, // Disabled until token is configured
            'settings' => [
                'supports_json' => true,
                'supports_balance_check' => true,
                'supports_statistics' => true,
                'rate_limit_per_minute' => 60
            ],
            'balance' => 0,
            'total_sent' => 0,
            'monthly_sent' => 0
        ]);

        // Create GreenWeb SMS provider (backup)
        SmsSettings::create([
            'provider' => 'greenweb',
            'provider_name' => 'GreenWeb SMS',
            'api_token' => '', // Will be filled by admin
            'api_url' => 'http://api.greenweb.com.bd/api.php',
            'sender_id' => null,
            'is_active' => false,
            'sms_enabled' => false,
            'settings' => [
                'supports_json' => false,
                'supports_balance_check' => false,
                'supports_statistics' => false,
                'rate_limit_per_minute' => 100
            ],
            'balance' => 0,
            'total_sent' => 0,
            'monthly_sent' => 0
        ]);

        // Create SSL Wireless provider (backup)
        SmsSettings::create([
            'provider' => 'ssl',
            'provider_name' => 'SSL Wireless',
            'api_token' => '', // Will be filled by admin
            'api_url' => 'https://sms.sslwireless.com/pushapi/dynamic/server.php',
            'sender_id' => null,
            'is_active' => false,
            'sms_enabled' => false,
            'settings' => [
                'supports_json' => false,
                'supports_balance_check' => false,
                'supports_statistics' => false,
                'rate_limit_per_minute' => 50
            ],
            'balance' => 0,
            'total_sent' => 0,
            'monthly_sent' => 0
        ]);
    }
}