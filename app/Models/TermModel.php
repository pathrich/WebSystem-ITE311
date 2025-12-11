<?php

namespace App\Models;

use CodeIgniter\Model;

class TermModel extends Model
{
    protected $table = 'terms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'semester_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get terms by semester
     */
    public function getTermsBySemester($semesterId)
    {
        return $this->where('semester_id', $semesterId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all terms with semester and academic year info
     */
    public function getAllTerms()
    {
        return $this->select('terms.*, semesters.name as semester_name, semesters.academic_year_id, academic_years.year_start, academic_years.year_end')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('academic_years', 'academic_years.id = semesters.academic_year_id')
            ->orderBy('academic_years.year_start', 'DESC')
            ->orderBy('semesters.name', 'ASC')
            ->orderBy('terms.name', 'ASC')
            ->findAll();
    }
}
