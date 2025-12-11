<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixTeacherRole extends Migration
{
    public function up()
    {
        // Update any user with email 'teacher@example.com' or name 'Teacher User' to have role 'teacher'
        // Also update any user with NULL or empty role that has 'teacher' in their email or name
        $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->orWhere('name', 'Teacher User')
            ->orWhere('email LIKE', '%teacher%')
            ->update(['role' => 'teacher']);
        
        // Also set default role for any user without a role
        $this->db->table('users')
            ->where('role IS NULL', null, false)
            ->orWhere('role', '')
            ->update(['role' => 'student']);
    }

    public function down()
    {
        // Optional: revert if needed
    }
}

