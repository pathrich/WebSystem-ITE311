<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateRoleEnumToIncludeTeacher extends Migration
{
    public function up()
    {
        // First, update any existing 'instructor' roles to 'teacher'
        $this->db->table('users')
            ->where('role', 'instructor')
            ->update(['role' => 'teacher']);
        
        // Modify the ENUM column to include 'teacher' instead of 'instructor'
        // MySQL/MariaDB requires dropping and recreating the column to change ENUM values
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('student', 'teacher', 'admin') DEFAULT 'student'");
        
        // Verify and update any NULL or invalid roles
        $this->db->query("UPDATE `users` SET `role` = 'student' WHERE `role` IS NULL OR `role` NOT IN ('student', 'teacher', 'admin')");
        
        // Force update teacher users
        $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->orWhere('name', 'Teacher User')
            ->orWhere('email LIKE', '%teacher%')
            ->update(['role' => 'teacher']);
    }

    public function down()
    {
        // Revert back to instructor (if needed)
        $this->db->table('users')
            ->where('role', 'teacher')
            ->update(['role' => 'instructor']);
        
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('student', 'instructor', 'admin') DEFAULT 'student'");
    }
}

