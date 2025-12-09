<?php
// Debug script to check enrollments
require_once 'app/Config/Paths.php';
require_once 'system/bootstrap.php';

use Config\Database;
use CodeIgniter\Database\ConnectionInterface;

$db = Database::connect();

// Check enrollments table
$enrollments = $db->table('enrollments')->get()->getResultArray();
echo "Total enrollments: " . count($enrollments) . "\n";

if (!empty($enrollments)) {
    echo "Enrollments data:\n";
    foreach ($enrollments as $enrollment) {
        echo "User ID: {$enrollment['user_id']}, Course ID: {$enrollment['course_id']}, Enrolled At: {$enrollment['enrolled_at']}\n";
    }
} else {
    echo "No enrollments found in database.\n";
}

// Check users table
$users = $db->table('users')->get()->getResultArray();
echo "\nTotal users: " . count($users) . "\n";

if (!empty($users)) {
    echo "Users data:\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
}

// Check courses table
$courses = $db->table('courses')->get()->getResultArray();
echo "\nTotal courses: " . count($courses) . "\n";

if (!empty($courses)) {
    echo "Courses data:\n";
    foreach ($courses as $course) {
        echo "ID: {$course['id']}, Title: {$course['title']}, Instructor ID: {$course['instructor_id']}\n";
    }
}
