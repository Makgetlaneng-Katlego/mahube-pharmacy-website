<?php
// payfast_debug.php
// TEMPORARY DEBUG SCRIPT – remove after debugging

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Log all incoming data
$raw = file_get_contents('php://input');
$postData = $_POST;  // for form-urlencoded

// Save everything to a log file
$log = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log .= "RAW INPUT: " . $raw . "\n";
$log .= "POST ARRAY: " . print_r($postData, true) . "\n";

// 2. Include your PayFast config (if you have one)
// If not, define the signature function locally
require_once 'payfast_config.php'; // assumes this exists with generatePayFastSignature()

// 3. If we have a signature, calculate and compare
if (isset($postData['signature'])) {
    $receivedSig = $postData['signature'];
    unset($postData['signature']);
    $calculatedSig = generatePayFastSignature($postData, PF_PASSPHRASE);
    
    $log .= "RECEIVED SIGNATURE: $receivedSig\n";
    $log .= "CALCULATED SIGNATURE: $calculatedSig\n";
    $log .= "MATCH? " . ($receivedSig === $calculatedSig ? "YES" : "NO") . "\n";
    
    // Also compare the raw query string without signature
    $dataWithoutSig = $postData;
    ksort($dataWithoutSig);
    $queryString = http_build_query($dataWithoutSig);
    if (PF_PASSPHRASE) {
        $queryString .= '&passphrase=' . urlencode(PF_PASSPHRASE);
    }
    $log .= "QUERY STRING (before hash): " . $queryString . "\n";
    $log .= "MD5 of that: " . md5($queryString) . "\n";
}

// 4. Save log to a file
file_put_contents('payfast_debug.log', $log, FILE_APPEND);

// 5. Display on screen for quick viewing
header('Content-Type: text/plain');
echo $log;