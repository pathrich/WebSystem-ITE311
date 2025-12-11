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

// Course Management
$routes->post('/course/enroll', 'Course::enroll');
$routes->post('/course/approve-enrollment', 'Course::approveEnrollment');
$routes->post('/course/reject-enrollment', 'Course::rejectEnrollment');
$routes->get('/course/create', 'Course::create');
$routes->post('/course/store', 'Course::store');
// Course search (AJAX and regular)
$routes->get('courses/search', 'Course::search');
// List all courses (non-AJAX view)
$routes->get('courses', 'Course::search');

// Materials Management
$routes->get('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->post('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');

// Assignments Management (Teacher/Admin)
$routes->get('/assignments/(:num)', 'Assignment::index/$1');
$routes->post('/assignments/upload/(:num)', 'Assignment::upload/$1');
$routes->get('/assignments/download/(:num)', 'Assignment::download/$1');
$routes->match(['get', 'post'], '/assignments/delete/(:num)', 'Assignment::delete/$1');

// Student Records (Teacher/Admin)
$routes->get('/student-records', 'StudentRecords::index');
$routes->get('/student-records/course/(:num)', 'StudentRecords::course/$1');
$routes->post('/student-records/unenroll', 'StudentRecords::unenroll');
$routes->post('/student-records/enroll', 'StudentRecords::enrollStudent');

// Notifications API
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
$routes->get('/notifications/all', 'Notifications::get_all');

// User Management (Admin Only)
$routes->group('users', function($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('create', 'UserController::create');
    $routes->post('store', 'UserController::store');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');
    // Allow both GET and POST for delete to support form POSTs and direct links
    $routes->match(['get', 'post'], 'delete/(:num)', 'UserController::delete/$1');
    $routes->match(['get', 'post'], 'restore/(:num)', 'UserController::restoreUser/$1');
    $routes->get('toggle-status/(:num)', 'UserController::toggleStatus/$1');
    $routes->post('update-role/(:num)', 'UserController::updateRole/$1');
    $routes->get('activity-logs', 'UserController::activityLogs');
    $routes->get('activity-logs/(:num)', 'UserController::activityLogs/$1');
});

// Academic Management (Admin Only)
$routes->group('academic-management', function($routes) {
    $routes->get('/', 'AcademicManagement::index');
    $routes->get('academic-years', 'AcademicManagement::academicYears');
    $routes->post('save-academic-year', 'AcademicManagement::saveAcademicYear');
    $routes->get('semesters', 'AcademicManagement::semesters');
    $routes->post('save-semester', 'AcademicManagement::saveSemester');
    $routes->get('terms', 'AcademicManagement::terms');
    $routes->post('save-term', 'AcademicManagement::saveTerm');
    $routes->match(['get', 'post'], 'courses', 'AcademicManagement::courses');
    $routes->post('assign-teacher', 'AcademicManagement::assignTeacher');
});
