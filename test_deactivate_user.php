<?php
/**
 * Test script for deactivate user functionality
 * Run this script from the project root directory
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(FCPATH);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// Load our paths config file
// This is the line that might need to be changed, depending on your folder structure.
$pathsConfig = FCPATH . 'app/Config/Paths.php';
// ^^^ Change this if you move your application folder

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

echo "=== DEACTIVATE USER FUNCTIONALITY TEST ===\n\n";

// Test data
$testUser = [
    'name' => 'Test Deactivate User',
    'username' => 'testdeactivate',
    'email' => 'testdeactivate@example.com',
    'password' => 'password123',
    'password_confirm' => 'password123',
    'role' => 'student',
    'status' => 'active'
];

echo "Test 1: Creating test user\n";
$userModel = new \App\Models\UserModel();
$userId = $userModel->insert($testUser);

if ($userId) {
    echo "✅ Test user created with ID: $userId\n";

    // Test 2: Verify user can login (simulate login check)
    echo "\nTest 2: Testing login with active user\n";
    $user = $userModel->where('email', $testUser['email'])->first();

    if ($user && $user['status'] === 'active' && password_verify($testUser['password'], $user['password'])) {
        echo "✅ Active user can login\n";
    } else {
        echo "❌ Active user login failed\n";
    }

    // Test 3: Deactivate user
    echo "\nTest 3: Deactivating user\n";
    if ($userModel->updateStatus($userId, 'inactive')) {
        echo "✅ User deactivated successfully\n";

        // Test 4: Verify deactivated user cannot login
        echo "\nTest 4: Testing login with deactivated user\n";
        $deactivatedUser = $userModel->where('email', $testUser['email'])->first();

        if ($deactivatedUser && $deactivatedUser['status'] === 'inactive') {
            echo "✅ User status is inactive\n";

            // Simulate login attempt - should fail due to inactive status
            if ($deactivatedUser['status'] === 'active' && password_verify($testUser['password'], $deactivatedUser['password'])) {
                echo "❌ Deactivated user can still login (BUG!)\n";
            } else {
                echo "✅ Deactivated user cannot login\n";
            }
        } else {
            echo "❌ User deactivation failed\n";
        }

        // Test 5: Reactivate user
        echo "\nTest 5: Reactivating user\n";
        if ($userModel->updateStatus($userId, 'active')) {
            echo "✅ User reactivated successfully\n";

            // Test 6: Verify reactivated user can login again
            echo "\nTest 6: Testing login with reactivated user\n";
            $reactivatedUser = $userModel->where('email', $testUser['email'])->first();

            if ($reactivatedUser && $reactivatedUser['status'] === 'active' && password_verify($testUser['password'], $reactivatedUser['password'])) {
                echo "✅ Reactivated user can login again\n";
            } else {
                echo "❌ Reactivated user login failed\n";
            }
        } else {
            echo "❌ User reactivation failed\n";
        }

    } else {
        echo "❌ User deactivation failed\n";
    }

    // Cleanup
    echo "\nTest 7: Cleaning up test user\n";
    $userModel->delete($userId);
    echo "✅ Test user deleted\n";

} else {
    echo "❌ Failed to create test user\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>
