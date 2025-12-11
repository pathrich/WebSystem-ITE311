<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ForceTeacherRole extends Migration
{
    public function up()
    {
        // Force update teacher user to have 'teacher' role
        // Update by email first
        $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->update(['role' => 'teacher']);
        
        // Also update by name if email doesn't match
        $this->db->table('users')
            ->where('name', 'Teacher User')
            ->where('role !=', 'teacher')
            ->orWhere('role IS NULL', null, false)
            ->update(['role' => 'teacher']);
        
        // If no teacher user exists, create one
        $teacherExists = $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->countAllResults();
        
        if ($teacherExists == 0) {
            $this->db->table('users')->insert([
                'name' => 'Teacher User',
                'username' => 'teacher',
                'email' => 'teacher@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function down()
    {
        // Optional revert
    }
}
