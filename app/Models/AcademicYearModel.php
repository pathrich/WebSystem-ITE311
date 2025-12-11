<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicYearModel extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'year_start',
        'year_end',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [];
    protected $skipValidation = true;

    /**
     * Get all academic years
     */
    public function getAllAcademicYears()
    {
        return $this->orderBy('year_start', 'DESC')->findAll();
    }

    /**
     * Get active academic year
     */
    public function getActiveAcademicYear()
    {
        return $this->where('is_active', 1)->first();
    }
}

