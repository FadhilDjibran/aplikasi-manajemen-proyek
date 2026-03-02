<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeRumah extends Model
{
    protected $table = 'tipe_rumah';
    protected $primaryKey = 'id_tipe';

    protected $fillable = [
        'project_id',
        'nama_tipe'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'id_tipe_rumah_minat', 'id_tipe');
    }
}
