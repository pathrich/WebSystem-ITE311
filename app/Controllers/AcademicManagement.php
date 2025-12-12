<?php

namespace App\Controllers;

use App\Models\AcademicYearModel;
use App\Models\SemesterModel;
use App\Models\TermModel;
use App\Models\CourseModel;
use App\Models\UserModel;

class AcademicManagement extends BaseController
{
    protected $academicYearModel;
    protected $semesterModel;
    protected $termModel;
    protected $courseModel;

    public function __construct()
    {
        $this->academicYearModel = new AcademicYearModel();
        $this->semesterModel = new SemesterModel();
        $this->termModel = new TermModel();
        $this->courseModel = new CourseModel();
    }

    /**
     * Check if user is admin
     */
    private function checkAdmin()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }
        
        $userRole = $session->get('userRole');
        if ($userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin only.');
        }
        
        return null;
    }

    /**
     * Main Academic Management page
     */
    public function index()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $data = [
            'academicYears' => [],
            'semesters' => [],
            'terms' => [],
        ];

        try {
            $data['academicYears'] = $this->academicYearModel->getAllAcademicYears();
        } catch (\Exception $e) {
            log_message('debug', 'Academic years table not found');
        }

        try {
            $data['semesters'] = $this->semesterModel->getAllSemesters();
        } catch (\Exception $e) {
            log_message('debug', 'Semesters table not found');
        }

        try {
            $data['terms'] = $this->termModel->getAllTerms();
        } catch (\Exception $e) {
            log_message('debug', 'Terms table not found');
        }

        return view('academic_management/index', $data);
    }

    /**
     * Academic Years Management - GET
     */
    public function academicYears()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $data = [];
        $data['academicYears'] = [];
        try {
            $data['academicYears'] = $this->academicYearModel->getAllAcademicYears();
        } catch (\Exception $e) {
            log_message('debug', 'Could not load academic years: ' . $e->getMessage());
        }

        return view('academic_management/academic_years', $data);
    }

    /**
     * Save Academic Year - POST (API endpoint)
     */
    public function saveAcademicYear()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first.'
            ]);
        }
        
        $userRole = $session->get('userRole');
        if ($userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin only.'
            ]);
        }

        $action = $this->request->getPost('action');
        $id = $this->request->getPost('id');

        if ($action === 'create') {
            $yearStart = trim((string)$this->request->getPost('year_start'));
            $yearEnd = trim((string)$this->request->getPost('year_end'));
            $isActivePost = $this->request->getPost('is_active');
            $isActive = ($isActivePost == '1' || $isActivePost === true || $isActivePost === 'on') ? 1 : 0;

            // Validation
            if (empty($yearStart) || empty($yearEnd)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Year start and year end are required.'
                ]);
            }

            // Convert to integers for YEAR type
            $yearStartInt = (int)$yearStart;
            $yearEndInt = (int)$yearEnd;
            
            $data = [
                'year_start' => $yearStartInt,
                'year_end' => $yearEndInt,
                'is_active' => $isActive,
            ];

            // Check if table exists
            try {
                $db = \Config\Database::connect();
                $tables = $db->listTables();
                
                if (!in_array('academic_years', $tables)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Academic years table does not exist. Please run migrations: php spark migrate'
                    ]);
                }

                // If setting as active, deactivate all others
                if ($data['is_active']) {
                    $db->table('academic_years')->set('is_active', 0)->update();
                }

                // Insert the data
                $result = $db->table('academic_years')->insert($data);
                $insertId = $db->insertID();
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Academic year created successfully.',
                        'id' => $insertId
                    ]);
                } else {
                    $error = $db->error();
                    $errorDetails = !empty($error['message']) ? ' Error: ' . $error['message'] : '';
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to insert academic year.' . $errorDetails
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create academic year: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'update' && $id) {
            $yearStart = trim((string)$this->request->getPost('year_start'));
            $yearEnd = trim((string)$this->request->getPost('year_end'));
            $isActivePost = $this->request->getPost('is_active');
            $isActive = ($isActivePost == '1' || $isActivePost === true || $isActivePost === 'on') ? 1 : 0;

            // Validation
            if (empty($yearStart) || empty($yearEnd)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Year start and year end are required.'
                ]);
            }

            $data = [
                'year_start' => (int)$yearStart,
                'year_end' => (int)$yearEnd,
                'is_active' => $isActive,
            ];

            try {
                $db = \Config\Database::connect();
                
                // If setting as active, deactivate all others
                if ($data['is_active']) {
                    $db->table('academic_years')->where('id !=', $id)->set('is_active', 0)->update();
                }

                $result = $db->table('academic_years')->where('id', $id)->update($data);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Academic year updated successfully.'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to update academic year. Record may not exist.'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update academic year: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'delete' && $id) {
            try {
                $this->academicYearModel->delete($id);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Academic year deleted successfully.'
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete academic year: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid action.'
        ]);
    }

    /**
     * Semesters Management - GET
     */
    public function semesters()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $data = [
            'academicYears' => [],
            'semesters' => [],
        ];

        try {
            $data['academicYears'] = $this->academicYearModel->getAllAcademicYears();
            $data['semesters'] = $this->semesterModel->getAllSemesters();
        } catch (\Exception $e) {
            // Tables don't exist
        }

        return view('academic_management/semesters', $data);
    }

    /**
     * Save Semester - POST (API endpoint)
     */
    public function saveSemester()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first.'
            ]);
        }
        
        $userRole = $session->get('userRole');
        if ($userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin only.'
            ]);
        }

        $action = $this->request->getPost('action');
        $id = $this->request->getPost('id');

        if ($action === 'create') {
            $academicYearId = (int)$this->request->getPost('academic_year_id');
            $name = trim((string)$this->request->getPost('name'));

            // Validation
            if (empty($academicYearId) || empty($name)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Academic year and semester name are required.'
                ]);
            }

            $data = [
                'academic_year_id' => $academicYearId,
                'name' => $name,
            ];

            try {
                $result = $this->semesterModel->insert($data);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Semester created successfully.',
                        'id' => $result
                    ]);
                } else {
                    $errors = $this->semesterModel->errors();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to create semester: ' . implode(', ', $errors)
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create semester: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'update' && $id) {
            $academicYearId = (int)$this->request->getPost('academic_year_id');
            $name = trim((string)$this->request->getPost('name'));

            // Validation
            if (empty($academicYearId) || empty($name)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Academic year and semester name are required.'
                ]);
            }

            $data = [
                'academic_year_id' => $academicYearId,
                'name' => $name,
            ];

            try {
                $result = $this->semesterModel->update($id, $data);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Semester updated successfully.'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to update semester. Record may not exist.'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update semester: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'delete' && $id) {
            try {
                $this->semesterModel->delete($id);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Semester deleted successfully.'
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete semester: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid action.'
        ]);
    }

    /**
     * Terms Management - GET
     */
    public function terms()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        $data = [
            'semesters' => [],
            'terms' => [],
        ];

        try {
            $data['semesters'] = $this->semesterModel->getAllSemesters();
            $data['terms'] = $this->termModel->getAllTerms();
        } catch (\Exception $e) {
            // Tables don't exist
        }

        return view('academic_management/terms', $data);
    }

    /**
     * Save Term - POST (API endpoint)
     */
    public function saveTerm()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first.'
            ]);
        }
        
        $userRole = $session->get('userRole');
        if ($userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin only.'
            ]);
        }

        $action = $this->request->getPost('action');
        $id = $this->request->getPost('id');

        if ($action === 'create') {
            $semesterId = (int)$this->request->getPost('semester_id');
            $name = trim((string)$this->request->getPost('name'));
            $startDate = $this->request->getPost('start_date') ?: null;
            $endDate = $this->request->getPost('end_date') ?: null;
            $isActivePost = $this->request->getPost('is_active');
            $isActive = ($isActivePost == '1' || $isActivePost === true || $isActivePost === 'on') ? 1 : 0;

            // Validation
            if (empty($semesterId) || empty($name)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Semester and term name are required.'
                ]);
            }

            $data = [
                'semester_id' => $semesterId,
                'name' => $name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => $isActive,
            ];

            try {
                // If setting as active, deactivate all others
                if ($data['is_active']) {
                    $db = \Config\Database::connect();
                    $db->table('terms')->set('is_active', 0)->update();
                }

                $result = $this->termModel->insert($data);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Term created successfully.',
                        'id' => $result
                    ]);
                } else {
                    $errors = $this->termModel->errors();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to create term: ' . implode(', ', $errors)
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create term: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'update' && $id) {
            $semesterId = (int)$this->request->getPost('semester_id');
            $name = trim((string)$this->request->getPost('name'));
            $startDate = $this->request->getPost('start_date') ?: null;
            $endDate = $this->request->getPost('end_date') ?: null;
            $isActivePost = $this->request->getPost('is_active');
            $isActive = ($isActivePost == '1' || $isActivePost === true || $isActivePost === 'on') ? 1 : 0;

            // Validation
            if (empty($semesterId) || empty($name)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Semester and term name are required.'
                ]);
            }

            $data = [
                'semester_id' => $semesterId,
                'name' => $name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => $isActive,
            ];

            try {
                // If setting as active, deactivate all others
                if ($data['is_active']) {
                    $db = \Config\Database::connect();
                    $db->table('terms')->where('id !=', $id)->set('is_active', 0)->update();
                }

                $result = $this->termModel->update($id, $data);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Term updated successfully.'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to update term. Record may not exist.'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update term: ' . $e->getMessage()
                ]);
            }
        } elseif ($action === 'delete' && $id) {
            try {
                $this->termModel->delete($id);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Term deleted successfully.'
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete term: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid action.'
        ]);
    }

    /**
     * Course Numbers and Units Management
     */
    public function courses()
    {
        $check = $this->checkAdmin();
        if ($check) return $check;

        if ($this->request->getMethod() === 'post') {
            $action = $this->request->getPost('action');
            $id = $this->request->getPost('id');
            $isAjax = $this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

            if ($action === 'update' && $id) {
                $data = [];
                
                // Check which fields exist
                $db = \Config\Database::connect();
                $fields = $db->getFieldData('courses');
                $fieldNames = [];
                foreach ($fields as $field) {
                    $fieldNames[] = $field->name;
                }

                // Always include title if it exists and field exists
                if (in_array('title', $fieldNames)) {
                    $title = trim((string)$this->request->getPost('title'));
                    if ($title !== '') {
                        $data['title'] = $title;
                    }
                }

                // Include course_number if field exists (can be empty/null)
                if (in_array('course_number', $fieldNames)) {
                    $courseNumberInput = trim((string)$this->request->getPost('course_number'));
                    if ($courseNumberInput !== '') {
                        // Format CN: Add "CN-" prefix, ensure exactly 4 digits
                        $numbersOnly = preg_replace('/[^0-9]/', '', $courseNumberInput);
                        
                        // Validate: must be exactly 4 digits
                        if (strlen($numbersOnly) !== 4) {
                            if ($isAjax) {
                                return $this->response->setJSON([
                                    'success' => false,
                                    'message' => 'Control Number (CN) must be exactly 4 digits (e.g., 0001).'
                                ]);
                            }
                            return redirect()->back()->with('error', 'Control Number (CN) must be exactly 4 digits (e.g., 0001).');
                        }
                        
                        // Pad with zeros to ensure 4 digits (e.g., 1 becomes 0001)
                        $paddedNumber = str_pad($numbersOnly, 4, '0', STR_PAD_LEFT);
                        $courseNumber = 'CN-' . $paddedNumber;
                        
                        // Check if CN is unique (excluding current course)
                        $existingCourse = $this->courseModel->where('course_number', $courseNumber)
                            ->where('id !=', $id)
                            ->first();
                        if ($existingCourse) {
                            if ($isAjax) {
                                return $this->response->setJSON([
                                    'success' => false,
                                    'message' => 'Control Number (CN) already exists. Please use a different number.'
                                ]);
                            }
                            return redirect()->back()->with('error', 'Control Number (CN) already exists. Please use a different number.');
                        }
                        
                        $data['course_number'] = $courseNumber;
                    } else {
                        $data['course_number'] = null;
                    }
                }
                
                // Include units if field exists (can be empty/null)
                if (in_array('units', $fieldNames)) {
                    $units = $this->request->getPost('units');
                    if ($units !== '' && $units !== null) {
                        $data['units'] = (float)$units;
                    } else {
                        $data['units'] = null;
                    }
                }
                
                log_message('debug', 'Update data prepared: ' . json_encode($data));

                if (!empty($data)) {
                    try {
                        // Set JSON response header for AJAX requests
                        if ($isAjax) {
                            $this->response->setContentType('application/json');
                        }
                        
                        // Log the update attempt
                        log_message('debug', 'Updating course ID: ' . $id . ' with data: ' . json_encode($data));
                        
                        // Verify course exists before update
                        $existingCourse = $this->courseModel->find($id);
                        if (!$existingCourse) {
                            if ($isAjax) {
                                return $this->response->setJSON([
                                    'success' => false,
                                    'message' => 'Course not found.'
                                ]);
                            }
                            return redirect()->back()->with('error', 'Course not found.');
                        }
                        
                        // Perform the update using database builder directly
                        $db = \Config\Database::connect();
                        $updateResult = $db->table('courses')
                            ->where('id', $id)
                            ->update($data);
                        
                        $rowsAffected = $db->affectedRows();
                        $dbError = $db->error();
                        
                        log_message('debug', 'Update executed. Result: ' . ($updateResult ? 'true' : 'false') . ', Rows affected: ' . $rowsAffected);
                        if (!empty($dbError)) {
                            log_message('error', 'Database error: ' . json_encode($dbError));
                        }
                        
                        // Check if update was successful
                        if ($updateResult !== false) {
                            // Fetch updated course with instructor info to verify
                            $updatedCourse = $db->table('courses')
                                ->select('courses.*, users.name as instructor_name, users.id as instructor_id')
                                ->join('users', 'users.id = courses.instructor_id', 'left')
                                ->where('courses.id', $id)
                                ->get()
                                ->getRowArray();
                            
                            // Ensure units is properly formatted
                            if (isset($updatedCourse['units'])) {
                                $updatedCourse['units'] = $updatedCourse['units'] !== null ? (float)$updatedCourse['units'] : null;
                            }
                            
                            log_message('debug', 'Course after update: ' . json_encode($updatedCourse));
                            
                            if ($isAjax) {
                                return $this->response->setJSON([
                                    'success' => true,
                                    'message' => 'Course updated successfully.',
                                    'data' => $updatedCourse
                                ]);
                            }
                            return redirect()->to(base_url('academic-management/courses'))->with('success', 'Course updated successfully.');
                        } else {
                            $error = $dbError;
                            log_message('error', 'Course update failed. DB Error: ' . json_encode($error));
                            if ($isAjax) {
                                return $this->response->setJSON([
                                    'success' => false,
                                    'message' => 'Failed to update course. Database error occurred.'
                                ]);
                            }
                            return redirect()->back()->with('error', 'Failed to update course.');
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Exception updating course: ' . $e->getMessage());
                        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                        if ($isAjax) {
                            return $this->response->setJSON([
                                'success' => false,
                                'message' => 'Error updating course: ' . $e->getMessage()
                            ]);
                        }
                        return redirect()->back()->with('error', 'Error updating course: ' . $e->getMessage());
                    }
                } else {
                    if ($isAjax) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'No data to update.'
                        ]);
                    }
                    return redirect()->back()->with('error', 'No data to update.');
                }
            }
        }

        $data['courses'] = [];
        $data['teachers'] = [];
        
        try {
            // Use fresh query builder to ensure we get the latest data (no caching)
            $db = \Config\Database::connect();
            $builder = $db->table('courses');
            $builder->select('courses.*, users.name as instructor_name, users.id as instructor_id');
            $builder->join('users', 'users.id = courses.instructor_id', 'left');
            $builder->orderBy('courses.created_at', 'DESC');
            $data['courses'] = $builder->get()->getResultArray();
            
            // Convert result objects to arrays if needed
            if (!empty($data['courses']) && is_object($data['courses'][0])) {
                $data['courses'] = array_map(function($item) {
                    return (array)$item;
                }, $data['courses']);
            }
            
            // Add schedules to each course
            $scheduleModel = new \App\Models\ScheduleModel();
            foreach ($data['courses'] as &$course) {
                $course['schedules'] = $scheduleModel->getSchedulesByCourse($course['id']);
            }
            
            log_message('debug', 'Fetched ' . count($data['courses']) . ' courses');
        } catch (\Exception $e) {
            // Table might not exist
            log_message('error', 'Error fetching courses: ' . $e->getMessage());
            $data['courses'] = [];
        }

        // Get all teachers (only users with role 'teacher')
        try {
            $userModel = new UserModel();
            $data['teachers'] = $userModel->where('role', 'teacher')
                ->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            // Table might not exist
        }

        return view('academic_management/courses', $data);
    }

    /**
     * Assign teacher to course - POST (API endpoint)
     */
    public function assignTeacher()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first.'
            ]);
        }
        
        $userRole = $session->get('userRole');
        if ($userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin only.'
            ]);
        }

        $courseId = (int)$this->request->getPost('course_id');
        $teacherId = (int)$this->request->getPost('teacher_id');

        if (empty($courseId) || empty($teacherId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course ID and Teacher ID are required.'
            ]);
        }

        try {
            // Verify course exists
            $course = $this->courseModel->find($courseId);
            if (!$course) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Course not found.'
                ]);
            }

            // Verify teacher exists and is a teacher
            $userModel = new UserModel();
            $teacher = $userModel->where('id', $teacherId)
                ->where('role', 'teacher')
                ->where('status', 'active')
                ->first();

            if (!$teacher) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Teacher not found or is not active.'
                ]);
            }

            // Check for schedule conflicts with teacher's existing courses
            $scheduleModel = new \App\Models\ScheduleModel();
            $teacherCourses = $this->courseModel->where('instructor_id', $teacherId)->findAll();
            
            $conflictingCourses = [];
            $conflictDetails = [];
            
            foreach ($teacherCourses as $teacherCourse) {
                if ($teacherCourse['id'] != $courseId) { // Don't check against the same course
                    $overlaps = $scheduleModel->getScheduleOverlapDetails($courseId, $teacherCourse['id']);
                    if (!empty($overlaps)) {
                        $conflictingCourses[] = $teacherCourse['title'];

                        foreach ($overlaps as $overlap) {
                            $start1 = date('g:i A', strtotime($overlap['course1_start_time']));
                            $end1 = date('g:i A', strtotime($overlap['course1_end_time']));
                            $start2 = date('g:i A', strtotime($overlap['course2_start_time']));
                            $end2 = date('g:i A', strtotime($overlap['course2_end_time']));

                            $conflictDetails[] = sprintf(
                                '%s: %s %s-%s conflicts with %s %s-%s',
                                $teacherCourse['title'],
                                $overlap['day_of_week'],
                                $start1,
                                $end1,
                                $overlap['day_of_week'],
                                $start2,
                                $end2
                            );
                        }
                    }
                }
            }

            if (!empty($conflictingCourses)) {
                $conflictList = implode(', ', $conflictingCourses);
                $conflictMessage = 'Schedule conflict detected! This course conflicts with the teacher\'s existing courses: ' . $conflictList . '.';
                
                if (!empty($conflictDetails)) {
                    $conflictMessage .= ' Details: ' . implode('; ', array_unique($conflictDetails));
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $conflictMessage
                ]);
            }

            // Update course instructor
            $result = $this->courseModel->update($courseId, ['instructor_id' => $teacherId]);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Teacher assigned successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to assign teacher.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to assign teacher: ' . $e->getMessage()
            ]);
        }
    }
}
