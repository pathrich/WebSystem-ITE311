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
// Course search (AJAX and regular)
$routes->get('courses/search', 'Course::search');
// List all courses (non-AJAX view)
$routes->get('courses', 'Course::search');

// Materials Management
$routes->get('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->post('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');

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
