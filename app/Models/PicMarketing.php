<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Userstamps;

class PicMarketing extends Model
{
    use Userstamps;
    protected $table = 'pic_marketing';
    protected $primaryKey = 'id_pic';
    protected $guarded = [];
    protected $fillable = [
        'user_id',
        'project_id',
        'nama_pic',
        'role_pic',
        'up_convert',
        'down_convert',
        'is_active',
        'kpi_target',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function leads() { return $this->hasMany(Lead::class, 'id_pic', 'id_pic'); }
}
