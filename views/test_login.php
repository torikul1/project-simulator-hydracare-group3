<?php
// test_login.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================================
// ⚠️ THE FIX: LOADING ALL SYSTEM IDENTITY KEYS
// ==========================================================================
// We populate BOTH sets of keys because your dashboard and your teammate's
// controller look for different names/IDs!
$_SESSION['user_id']   = 20;            
$_SESSION['id']        = 20;            

$_SESSION['name']      = 'ara';         
$_SESSION['user_name'] = 'ara';         

// ⚠️ CHANGE THIS VALUE IF IT STILL COMPLAINS:
// If AdminController rejects the 'vendor' string, change this to 'admin' 
// to instantly bypass the barrier with master rights.
$_SESSION['role']      = 'vendor'; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Developer Login Mock Tool</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f1f5f9;
            padding: 50px;
            text-align: center;
        }
        .mock-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: left;
            max-width: 500px;
        }
        h2 { color: #16a34a; margin-top: 0; }
        pre { background: #f8fafc; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 15px; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>

    <div class="mock-box">
        <h2>✅ Mock Session Initialized!</h2>
        <p>The necessary authentication keys have been successfully saved into your active browser session.</p>
        
        <strong>Active Session Array:</strong>
        <pre><?php print_r($_SESSION); ?></pre>

        <a href="views/vendor_dashboard.php" class="btn">Go to Vendor Dashboard →</a>
    </div>

</body>
</html>