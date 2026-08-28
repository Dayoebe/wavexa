<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'radio_browser' => [
        'base_url' => env('RADIO_BROWSER_BASE_URL', 'https://de1.api.radio-browser.info'),
        'timeout' => (int) env('RADIO_BROWSER_TIMEOUT', 15),
        'user_agent' => env('RADIO_BROWSER_USER_AGENT', 'Wavexa/1.0'),
    ],

    'free_tv' => [
        'playlist_url' => env('FREE_TV_PLAYLIST_URL', 'https://raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u8'),
        'timeout' => (int) env('FREE_TV_TIMEOUT', 30),
        'user_agent' => env('FREE_TV_USER_AGENT', 'Wavexa/1.0'),
    ],

];
