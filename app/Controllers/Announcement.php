<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;

class Announcement extends BaseController
{
    public function index()
    {
        $model = new AnnouncementModel();
        $announcements = $model->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'announcements' => $announcements,
        ];

        return view('announcements', $data);
    }
}
