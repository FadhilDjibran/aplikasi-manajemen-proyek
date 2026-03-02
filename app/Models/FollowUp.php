<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $table = 'follow_up';
    protected $primaryKey = 'id_follow_up';
    protected $guarded = [];

    public function lead() { return $this->belongsTo(Lead::class, 'id_lead', 'id_lead'); }
    public function pic()
    {
        return $this->belongsTo(PicMarketing::class, 'id_pic', 'id_pic');
    }
}
