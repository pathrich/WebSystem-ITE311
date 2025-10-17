<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function dashboard()
    {
        return view('admin_dashboard');
    }
}
