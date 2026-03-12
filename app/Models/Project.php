<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Userstamps;

class Project extends Model
{
    use Userstamps;
    protected $table = 'projects';
    protected $guarded = [];

    public function tipeRumah()
    {
        return $this->hasMany(TipeRumah::class, 'project_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'project_id');
    }
}
