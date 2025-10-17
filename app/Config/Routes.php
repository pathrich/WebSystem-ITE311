<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');

// Custom routes
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

// Auth & Dashboard
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attempt');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Auth::dashboard');
// Registration
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::store');

// Announcements
$routes->get('/announcements', 'Announcement::index');

// Role-specific dashboards
$routes->get('/teacher/dashboard', 'Teacher::dashboard');
$routes->get('/admin/dashboard', 'Admin::dashboard');

// Protect admin and teacher groups using RoleAuth filter (applied here at route-level)
$routes->group('admin', ['filter' => 'roleauth'], function($routes){
    $routes->get('dashboard', 'Admin::dashboard');
});

$routes->group('teacher', ['filter' => 'roleauth'], function($routes){
    $routes->get('dashboard', 'Teacher::dashboard');
});

// Course Management
$routes->post('/course/enroll', 'Course::enroll');

// Labs
$routes->get('/lab5', static function() {
    return view('labs/lab5');
});

