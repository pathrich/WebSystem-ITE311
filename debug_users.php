<?php
/**
 * Debug script to check users in database
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(FCPATH);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 */

// Load our paths config file
$pathsConfig = FCPATH . 'app/Config/Paths.php';
require $pathsConfig;

$paths = new Config\Paths();

// Location of the framework bootstrap file.
$appDirectory = $paths->appDirectory;
$systemDirectory = $paths->systemDirectory;

if (is_file($appDirectory . '/Config/Boot/development.php')) {
    require $appDirectory . '/Config/Boot/development.php';
} elseif (is_file($appDirectory . '/Config/Boot/production.php')) {
    require $appDirectory . '/Config/Boot/production.php';
} else {
    // Fallback to the system bootstrap file
    require $systemDirectory . '/Boot.php';
}

// Get the database connection
$db = \Config\Database::connect();

echo "=== DATABASE DEBUG ===\n\n";

// Check if users table exists
echo "1. Checking if users table exists:\n";
$tables = $db->listTables();
if (in_array('users', $tables)) {
    echo "✅ Users table exists\n";
} else {
    echo "❌ Users table does not exist\n";
    exit;
}

// Check table structure
echo "\n2. Users table structure:\n";
$fields = $db->getFieldNames('users');
echo "Fields: " . implode(', ', $fields) . "\n";

// Check if users exist
echo "\n3. Checking users in database:\n";
$users = $db->table('users')->get()->getResultArray();
echo "Total users found: " . count($users) . "\n";

if (!empty($users)) {
    echo "\nUsers:\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}\n";
    }
} else {
    echo "No users found in database.\n";
}

// Test UserModel
echo "\n4. Testing UserModel:\n";
$userModel = new \App\Models\UserModel();

try {
    $allUsers = $userModel->findAll();
    echo "UserModel->findAll() returned " . count($allUsers) . " users\n";

    if (!empty($allUsers)) {
        echo "First user: " . json_encode($allUsers[0]) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error with UserModel: " . $e->getMessage() . "\n";
}

// Test pagination
echo "\n5. Testing pagination:\n";
try {
    $paginatedUsers = $userModel->findAll(10, 0);
    echo "UserModel->findAll(10, 0) returned " . count($paginatedUsers) . " users\n";

    $total = $userModel->countAll();
    echo "Total users count: $total\n";
} catch (Exception $e) {
    echo "❌ Error with pagination: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
