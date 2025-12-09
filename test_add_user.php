<?php
/**
 * Test script for add user functionality
 * Run this script from the project root directory
 */

// Include the CodeIgniter bootstrap
require_once 'system/bootstrap.php';

// Initialize the framework
$app = \Config\Services::codeigniter();
$app->initialize();

// Get the database connection
$db = \Config\Database::connect();

// Test data
$testUsers = [
    [
        'name' => 'Test User 1',
        'username' => 'testuser1',
        'email' => 'testuser1@example.com',
        'password' => 'password123',
        'password_confirm' => 'password123',
        'role' => 'student',
        'status' => 'active'
    ],
    [
        'name' => 'Test User 2',
        'username' => 'testuser2',
        'email' => 'testuser2@example.com',
        'password' => 'password123',
        'password_confirm' => 'password123',
        'role' => 'teacher',
        'status' => 'active'
    ]
];

echo "=== ADD USER FUNCTIONALITY TEST ===\n\n";

// Test 1: Valid user creation
echo "Test 1: Creating valid users\n";
foreach ($testUsers as $index => $userData) {
    echo "Creating user " . ($index + 1) . ": {$userData['name']}\n";

    // Simulate controller validation
    $validation = \Config\Services::validation();
    $rules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        'password_confirm' => 'required|matches[password]',
        'role' => 'required|in_list[admin,teacher,student]',
        'status' => 'required|in_list[active,inactive]',
    ];

    if (!$validation->setRules($rules)->run($userData)) {
        echo "❌ Validation failed: " . implode(', ', $validation->getErrors()) . "\n";
        continue;
    }

    // Create user using model
    $userModel = new \App\Models\UserModel();
    $userId = $userModel->insert($userData);

    if ($userId) {
        echo "✅ User created successfully with ID: $userId\n";

        // Verify user was created
        $createdUser = $userModel->find($userId);
        if ($createdUser) {
            echo "   - Name: {$createdUser['name']}\n";
            echo "   - Username: {$createdUser['username']}\n";
            echo "   - Email: {$createdUser['email']}\n";
            echo "   - Role: {$createdUser['role']}\n";
            echo "   - Status: {$createdUser['status']}\n";
            echo "   - Password hashed: " . (password_verify($userData['password'], $createdUser['password']) ? 'Yes' : 'No') . "\n";
        }

        // Check activity log
        $activityLogModel = new \App\Models\UserActivityLogModel();
        $logs = $activityLogModel->where('user_id', 1)->where('action', 'create')->orderBy('created_at', 'DESC')->limit(1)->findAll();
        if (!empty($logs)) {
            echo "   - Activity logged: Yes\n";
        } else {
            echo "   - Activity logged: No\n";
        }

    } else {
        echo "❌ Failed to create user\n";
    }

    echo "\n";
}

// Test 2: Invalid data tests
echo "Test 2: Testing invalid data handling\n";

$invalidTests = [
    [
        'name' => 'Test',
        'username' => 'tu', // too short
        'email' => 'invalid-email',
        'password' => '12345', // too short
        'password_confirm' => '12345',
        'role' => 'invalid_role',
        'status' => 'invalid_status'
    ],
    [
        'name' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'password_confirm' => '',
        'role' => '',
        'status' => ''
    ],
    [
        'name' => 'Test User',
        'username' => 'testuser1', // duplicate
        'email' => 'testuser1@example.com', // duplicate
        'password' => 'password123',
        'password_confirm' => 'password123',
        'role' => 'student',
        'status' => 'active'
    ]
];

foreach ($invalidTests as $index => $invalidData) {
    echo "Invalid test " . ($index + 1) . ":\n";

    $validation = \Config\Services::validation();
    $rules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        'password_confirm' => 'required|matches[password]',
        'role' => 'required|in_list[admin,teacher,student]',
        'status' => 'required|in_list[active,inactive]',
    ];

    if (!$validation->setRules($rules)->run($invalidData)) {
        echo "✅ Validation correctly failed: " . implode(', ', $validation->getErrors()) . "\n";
    } else {
        echo "❌ Validation should have failed but passed\n";
    }

    echo "\n";
}

// Test 3: Password confirmation mismatch
echo "Test 3: Testing password confirmation mismatch\n";

$mismatchData = [
    'name' => 'Test User',
    'username' => 'testuser3',
    'email' => 'testuser3@example.com',
    'password' => 'password123',
    'password_confirm' => 'differentpassword',
    'role' => 'student',
    'status' => 'active'
];

$validation = \Config\Services::validation();
$rules = [
    'name' => 'required|min_length[2]|max_length[100]',
    'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
    'email' => 'required|valid_email|is_unique[users.email]',
    'password' => 'required|min_length[6]',
    'password_confirm' => 'required|matches[password]',
    'role' => 'required|in_list[admin,teacher,student]',
    'status' => 'required|in_list[active,inactive]',
];

if (!$validation->setRules($rules)->run($mismatchData)) {
    echo "✅ Password confirmation mismatch correctly detected: " . implode(', ', $validation->getErrors()) . "\n";
} else {
    echo "❌ Password confirmation mismatch should have failed but passed\n";
}

echo "\n";

// Test 4: Clean up test users
echo "Test 4: Cleaning up test users\n";
$userModel = new \App\Models\UserModel();
$activityLogModel = new \App\Models\UserActivityLogModel();

foreach ($testUsers as $userData) {
    $user = $userModel->where('username', $userData['username'])->first();
    if ($user) {
        $userModel->delete($user['id']);
        $activityLogModel->where('user_id', 1)->where('description LIKE', "%{$userData['name']}%")->delete();
        echo "✅ Cleaned up user: {$userData['username']}\n";
    }
}

echo "\n=== TEST COMPLETED ===\n";
?>
