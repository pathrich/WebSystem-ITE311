<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'course_id',
        'file_name',
        'file_path',
        'created_at'
    ];

    protected $useTimestamps = false;

    /**
     * Insert a new material record
     */
    public function insertMaterial($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Get all materials for a specific course
     */
    public function getMaterialsByCourse($course_id)
    {
        return $this->where('course_id', $course_id)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a material by ID
     */
    public function getMaterialById($id)
    {
        return $this->find($id);
    }

    /**
     * Delete a material by ID
     */
    public function deleteMaterial($id)
    {
        return $this->delete($id);
    }
}
