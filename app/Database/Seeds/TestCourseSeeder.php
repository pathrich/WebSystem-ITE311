<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestCourseSeeder extends Seeder
{
    public function run()
    {
        // Get teacher user ID
        $teacher = $this->db->table('users')->where('email', 'teacher@test.com')->get()->getRowArray();
        if (!$teacher) {
            echo "No teacher user found. Please run TestUserSeeder first.\n";
            return;
        }

        $courses = [
            [
                'title' => 'Introduction to Computer Science',
                'description' => 'Learn the fundamentals of computer science including algorithms, data structures, and programming concepts.',
                'instructor_id' => $teacher['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'Web Development Fundamentals',
                'description' => 'Master HTML, CSS, and JavaScript to build modern web applications.',
                'instructor_id' => $teacher['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'Database Design and Management',
                'description' => 'Learn database design principles, SQL, and database management systems.',
                'instructor_id' => $teacher['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('courses')->insertBatch($courses);
    }
}
