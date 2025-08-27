<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'     => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Instructor One',
                'email'    => 'instructor1@example.com',
                'password' => password_hash('instructor123', PASSWORD_DEFAULT),
                'role'     => 'instructor',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Instructor Two',
                'email'    => 'instructor2@example.com',
                'password' => password_hash('instructor123', PASSWORD_DEFAULT),
                'role'     => 'instructor',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Student One',
                'email'    => 'student1@example.com',
                'password' => password_hash('student123', PASSWORD_DEFAULT),
                'role'     => 'student',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Student Two',
                'email'    => 'student2@example.com',
                'password' => password_hash('student123', PASSWORD_DEFAULT),
                'role'     => 'student',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert all at once
        $this->db->table('users')->insertBatch($data);
    }
}
