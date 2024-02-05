<?php

require __DIR__ . '/vendor/autoload.php';

use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\table;

if (PHP_SAPI !== 'cli') {
    http_response_code(400);
    die("This application can only be run via the PHP CLI.");
}

define('DEFAULT_CONTENT_OWNER_ID', 'IOlF98XwT7nxqeIz8ii0iw');

// Get input from user
$searchInput = text(
    label: 'Please provide a YouTube Content Owner ID or Partner Code',
    placeholder: 'E.g. Divimove_NL',
    required: true,
    hint: 'You can separate multiple values using a comma.'
);

// Initialize Google client
$googleClient = new Google_Client();
$googleClient->setAuthConfig('service-account.json');
$googleClient->setScopes([
    'https://www.googleapis.com/auth/youtubepartner'
]);

// Initialize YouTube Partner service
$youtubePartner = new Google_Service_YouTubePartner($googleClient);

// Search for content owners
$contentOwnersSearch = $youtubePartner->contentOwners->listContentOwners([
    'id' => $searchInput,
    'onBehalfOfContentOwner' => DEFAULT_CONTENT_OWNER_ID
]);

// Check if any results are found
if (empty($contentOwnersSearch->items)) {
    error('No matching results found for the provided YouTube Content Owner ID or Partner Code.');
} else {
    // Extract relevant information and store it in $contentOwners array
    $contentOwners = array_map(function ($contentOwner) {
        return [
            'Display Name' => $contentOwner->displayName,
            'Conflict Notification Email' => $contentOwner->conflictNotificationEmail
        ];
    }, $contentOwnersSearch->items);

    // Display results in a table
    table(
        ['Display Name', 'Conflict Notification Email'],
        $contentOwners
    );
}