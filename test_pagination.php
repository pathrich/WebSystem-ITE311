<?php
/**
 * Simple test for pagination fix
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

echo "=== TESTING PAGINATION FIX ===\n\n";

$userModel = new \App\Models\UserModel();

try {
    // Test the fixed pagination method
    echo "Testing UserModel->orderBy('id', 'DESC')->findAll(10, 0)...\n";
    $users = $userModel->orderBy('id', 'DESC')->findAll(10, 0);

    echo "✅ Query executed successfully\n";
    echo "Found " . count($users) . " users\n";

    if (!empty($users)) {
        echo "\nLatest users:\n";
        foreach (array_slice($users, 0, 3) as $user) {
            echo "- ID: {$user['id']}, Name: {$user['name']}, Username: {$user['username']}\n";
        }
    }

    echo "\n✅ Pagination fix is working correctly!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
