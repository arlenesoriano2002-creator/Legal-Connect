<?php

return [
    'default' => env('IMAP_DEFAULT_ACCOUNT', 'gmail'),

    'accounts' => [
        'gmail' => [
            'host' => env('IMAP_HOST', 'imap.gmail.com'),
            'port' => env('IMAP_PORT', 993),
            'protocol' => env('IMAP_PROTOCOL', 'imap'),
            'encryption' => env('IMAP_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'authentication' => null,
        ],
    ],

    'options' => [
        'delimiter' => '/',
        'fetch' => \Webklex\PHPIMAP\IMAP::FT_PEEK,
        'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
        'fetch_body' => true,
        'fetch_flags' => true,
        'soft_fail' => false,
        'rfc822' => true,
        'debug' => env('APP_DEBUG', false),
        'uid_cache' => true,
        'message_key' => 'list',
        'fetch_order' => 'desc',
        'dispositions' => ['attachment', 'inline'],
        'common_folders' => [
            "root" => "INBOX",
            "junk" => "INBOX/Junk",
            "draft" => "INBOX/Drafts",
            "sent" => "INBOX/Sent",
            "trash" => "INBOX/Trash",
        ],
    ],
];