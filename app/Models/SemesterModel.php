<?php

namespace App\Models;

use CodeIgniter\Model;

class SemesterModel extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'academic_year_id',
        'name',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get semesters by academic year
     */
    public function getSemestersByAcademicYear($academicYearId)
    {
        return $this->where('academic_year_id', $academicYearId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all semesters with academic year info
     */
    public function getAllSemesters()
    {
        return $this->select('semesters.*, academic_years.year_start, academic_years.year_end')
            ->join('academic_years', 'academic_years.id = semesters.academic_year_id')
            ->orderBy('academic_years.year_start', 'DESC')
            ->orderBy('semesters.name', 'ASC')
            ->findAll();
    }
}

