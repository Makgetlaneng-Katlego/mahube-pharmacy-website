<?php
// payfast_config.php

// Database credentials (same as your other files)
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

// ----- PayFast Credentials (from your PayFast dashboard) -----
// For testing, use these sandbox credentials:
// Merchant ID: 10000100
// Merchant Key: 46f0cd694581a
// Passphrase: (leave blank if none)
// But you must replace with your own after testing.

define('PF_MERCHANT_ID', '10000100');          // Replace with your actual merchant ID
define('PF_MERCHANT_KEY', '46f0cd694581a');    // Replace with your actual merchant key
define('PF_PASSPHRASE', '');                   // Set only if you have one

// Set to true for testing (sandbox), false for live
define('PF_IS_TEST', true);

// Your website URL (no trailing slash)
define('SITE_URL', 'https://yourdomain.com');  // Replace with your domain

// PayFast URLs
define('PF_ACTION_URL', PF_IS_TEST ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process');
?>