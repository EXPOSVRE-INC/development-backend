<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file. This JSON file can be
    | downloaded from your Firebase project settings -> Service accounts.
    | Make sure the file is **not in the public folder** for security reasons.
    |
    | You can also set the path in your .env file:
    | FIREBASE_CREDENTIALS_FILE=storage/firebase/firebase-service-account.json
    |
    */

    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_FILE', base_path('storage/app/firebase/firebase-service-account.json')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Optionally, you can specify the project ID explicitly.
    | If left null, the SDK will read it from your service account JSON.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Default Database
    |--------------------------------------------------------------------------
    |
    | If you plan to use Firebase Realtime Database, you can set the default
    | database URL here. Not required for FCM push notifications.
    |
    */

    'database_url' => env('FIREBASE_DATABASE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Default Storage Bucket
    |--------------------------------------------------------------------------
    |
    | If you plan to use Firebase Storage, you can set the default storage
    | bucket here. Not required for FCM push notifications.
    |
    */

    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', null),

    /*
    |--------------------------------------------------------------------------
    | Default Messaging Service
    |--------------------------------------------------------------------------
    |
    | Optional settings for Firebase Cloud Messaging.
    | Mostly handled by laravel-notification-channels/fcm automatically.
    |
    */

    'messaging' => [
        // no additional config needed; uses project_id from JSON
    ],

];
