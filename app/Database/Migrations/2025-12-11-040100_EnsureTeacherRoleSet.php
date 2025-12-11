<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureTeacherRoleSet extends Migration
{
    public function up()
    {
        // Ensure teacher user has 'teacher' role
        // Update by email
        $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->update(['role' => 'teacher']);
        
        // Update by name
        $this->db->table('users')
            ->where('name', 'Teacher User')
            ->update(['role' => 'teacher']);
        
        // Update any user with 'teacher' in email
        $this->db->query("UPDATE `users` SET `role` = 'teacher' WHERE `email` LIKE '%teacher%' AND `role` != 'teacher'");
        
        // Verify: Show current roles
        $users = $this->db->table('users')
            ->select('id, name, email, role')
            ->get()
            ->getResultArray();
        
        log_message('info', 'User roles after migration: ' . json_encode($users));
    }

    public function down()
    {
        // Optional revert
    }
}

