<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentSubmissionModel extends Model
{
    protected $table = 'assignment_submissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $tableReady;
    protected $allowedFields = [
        'assignment_id',
        'student_id',
        'file_path',
        'file_name',
        'submitted_at',
        'score',
        'feedback',
        'graded_at',
        'graded_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function isReady(): bool
    {
        if ($this->tableReady !== null) {
            return (bool) $this->tableReady;
        }

        try {
            $db = \Config\Database::connect();
            $this->tableReady = $db->tableExists($this->table);
        } catch (\Throwable $e) {
            $this->tableReady = false;
        }

        return (bool) $this->tableReady;
    }

    public function getSubmissionForStudent($assignmentId, $studentId)
    {
        if (!$this->isReady()) {
            return null;
        }

        try {
            return $this->where('assignment_id', (int) $assignmentId)
                ->where('student_id', (int) $studentId)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getSubmissionsForStudentByAssignments($studentId, array $assignmentIds)
    {
        if (!$this->isReady()) {
            return [];
        }

        $assignmentIds = array_values(array_filter(array_unique(array_map('intval', $assignmentIds))));
        if (empty($assignmentIds)) {
            return [];
        }

        try {
            $rows = $this->where('student_id', (int) $studentId)
                ->whereIn('assignment_id', $assignmentIds)
                ->findAll();
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['assignment_id']] = $row;
        }

        return $map;
    }

    public function upsertSubmission(array $data)
    {
        if (!$this->isReady()) {
            return false;
        }

        $assignmentId = (int) ($data['assignment_id'] ?? 0);
        $studentId = (int) ($data['student_id'] ?? 0);

        if ($assignmentId <= 0 || $studentId <= 0) {
            return false;
        }

        try {
            $existing = $this->getSubmissionForStudent($assignmentId, $studentId);
            if ($existing) {
                return $this->update($existing['id'], $data);
            }

            return $this->insert($data);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getGradesForStudent($studentId)
    {
        if (!$this->isReady()) {
            return [];
        }

        $studentId = (int) $studentId;

        try {
            $db = \Config\Database::connect();

            return $db->table('assignment_submissions sub')
                ->select('sub.*, a.title as assignment_title, a.due_date, a.max_score, a.course_id, c.title as course_title, u.name as instructor_name')
                ->join('assignments a', 'a.id = sub.assignment_id', 'inner')
                ->join('courses c', 'c.id = a.course_id', 'inner')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->join('enrollments e', 'e.course_id = a.course_id AND e.user_id = sub.student_id AND e.status = \'approved\'', 'inner')
                ->where('sub.student_id', $studentId)
                ->orderBy('c.title', 'ASC')
                ->orderBy('a.due_date', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}

