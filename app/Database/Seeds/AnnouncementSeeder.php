<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => 'Welcome Back to Campus',
                'content' => 'We are excited to welcome all students for the new semester. Please check your schedules and announcements frequently.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'title' => 'System Maintenance',
                'content' => 'The portal will be undergoing scheduled maintenance this weekend. Expect intermittent downtime.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('announcements')->insert($row);
        }
    }
}
