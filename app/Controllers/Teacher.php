<?php

namespace App\Controllers;

class Teacher extends BaseController
{
    public function dashboard()
    {
        return view('teacher_dashboard');
    }
}
