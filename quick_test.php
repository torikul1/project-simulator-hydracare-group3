<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2> diagnostic checkpoint 1: Script loaded!</h2>";

// Check 1: Can it find the database config?
$db_file = __DIR__ . '/config/database.php';
if (file_exists($db_file)) {
    echo "✅ config/database.php found!<br>";
    require_once $db_file;
    $database = new Database();
    if($database->getConnection()) {
        echo "✅ Connected to MySQL successfully!<br>";
    }
} else {
    echo "❌ Cannot find database file at: " . $db_file . "<br>";
}

// Check 2: Can it find the model?
$model_file = __DIR__ . '/models/AdminModel.php';
if (file_exists($model_file)) {
    echo "✅ models/AdminModel.php found!<br>";
} else {
    echo "❌ Cannot find model file at: " . $model_file . "<br>";
}

// Check 3: Can it find your dashboard view?
$view_file = __DIR__ . '/views/dashboard.php';
if (file_exists($view_file)) {
    echo "✅ views/dashboard.php found!<br>";
} else {
    echo "❌ Cannot find view file at: " . $view_file . "<br>";
}
?>