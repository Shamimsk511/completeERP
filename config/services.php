<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'facebook' => [
    'app_id' => env('FACEBOOK_APP_ID'),
    'app_secret' => env('FACEBOOK_APP_SECRET'),
    'page_id' => env('FACEBOOK_PAGE_ID'),
    'pixel_id' => env('FACEBOOK_PIXEL_ID'),
],

'google' => [
    'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    'tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
],
 'sms' => [
        'greenweb' => [
            'key' => env('GREENWEB_SMS_KEY'),
        ],
        'ssl' => [
            'key' => env('SSL_SMS_KEY'),
        ],
    ],
];
