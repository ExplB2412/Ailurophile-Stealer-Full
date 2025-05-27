<?php
// File path to the file you want to upload
$filePath = __DIR__ . "/test.zip";

// Initialize cURL session
$ch = curl_init();

// API URL
$url = 'https://api.filedoge.com/upload';

// Set the cURL options for the POST request
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));

// Set the file to upload in the POST form data
curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => new CURLFile($filePath)));

// Execute the cURL request
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Decode the response and print it
    $responseData = json_decode($response, true);
    echo "Response from FileDoge:\n";
    print_r($responseData);
}

// Close the cURL session
curl_close($ch);
