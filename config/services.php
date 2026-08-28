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

    /*
    | Platform-level Meta app credentials for the WhatsApp Cloud API.
    | One Meta app serves every business — a business's own number, phone
    | number id and access token are configured per business instead, in
    | Company Settings (see App\Services\WhatsAppService).
    */
    'whatsapp' => [
        'app_id' => env('WHATSAPP_META_APP_ID'),
        'app_secret' => env('WHATSAPP_META_APP_SECRET'),
        'webhook_verify_token' => env('WHATSAPP_META_WEBHOOK_VERIFY_TOKEN'),
        'graph_version' => env('WHATSAPP_GRAPH_API_VERSION', 'v20.0'),
    ],

];
