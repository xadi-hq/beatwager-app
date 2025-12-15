<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Badge Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk to use for storing and serving badge images.
    | Use 's3' for cloud storage (Scaleway, AWS, etc.) or 'public' for local.
    |
    */

    'storage_disk' => env('BADGE_STORAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Badge Image Path Prefix
    |--------------------------------------------------------------------------
    |
    | The path prefix for badge images within the storage disk.
    | For S3, this is the folder path within the bucket (e.g., 'assets/bw-badges').
    |
    */

    'storage_path' => env('BADGE_STORAGE_PATH', 'badges'),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Settings for badge award notifications.
    |
    */

    'notifications' => [
        // Send announcement to group chat when badge is earned
        'announce_in_group' => env('BADGE_ANNOUNCE_IN_GROUP', true),

        // Send direct message to user when badge is earned
        'send_dm' => env('BADGE_SEND_DM', true),
    ],

];
