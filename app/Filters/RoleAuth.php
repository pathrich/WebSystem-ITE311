<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleAuth implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$session = session();
		$role = strtolower((string) $session->get('userRole'));
		$uri = $request->getUri()->getPath();

		// Normalize leading slash
		$path = '/' . ltrim($uri, '/');

		// Admin can access /admin/*
		if (str_starts_with($path, '/admin')) {
			if ($role !== 'admin') {
				$session->setFlashdata('error', 'Access Denied: Insufficient Permissions');
				return redirect()->to(base_url('announcements'));
			}
			return;
		}

		// Teacher can access /teacher/*
		if (str_starts_with($path, '/teacher')) {
			if ($role !== 'teacher') {
				$session->setFlashdata('error', 'Access Denied: Insufficient Permissions');
				return redirect()->to(base_url('announcements'));
			}
			return;
		}

		// Student can access /student/* and /announcements
		if (str_starts_with($path, '/student')) {
			if ($role !== 'student') {
				$session->setFlashdata('error', 'Access Denied: Insufficient Permissions');
				return redirect()->to(base_url('announcements'));
			}
			return;
		}

		// Allow announcements for any logged-in user
		if ($path === '/announcements' || $path === 'announcements') {
			if (! $session->get('isLoggedIn')) {
				// Not logged in -> redirect to login
				return redirect()->to(base_url('login'));
			}
			return;
		}

		// For other routes, don't block here
		return;
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// No after logic
	}
}

