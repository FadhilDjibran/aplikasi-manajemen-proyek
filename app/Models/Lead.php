<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';
    protected $primaryKey = 'id_lead';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];
    protected $fillable = [
    'id_lead',
    'project_id',
    'tgl_masuk',
    'nama_lead',
    'no_whatsapp',
    'sumber_lead',
    'id_tipe_rumah_minat',
    'perkiraan_budget',
    'status_lead',
    'follow_up_count',
    'id_pic',
    'cara_kontak',
    'kota_domisili',
    'alamat',
    'status_pekerjaan',
    'rencana_pembayaran',
    'catatan',
    'alasan_gagal',
    'catatan_gagal',
    'tgl_gagal',
];

    public function project() { return $this->belongsTo(Project::class); }
    public function tipeRumah() { return $this->belongsTo(TipeRumah::class, 'id_tipe_rumah_minat', 'id_tipe'); }
    public function picMarketing() { return $this->belongsTo(PicMarketing::class, 'id_pic', 'id_pic'); }
    public function followUps() { return $this->hasMany(FollowUp::class, 'id_lead', 'id_lead'); }
    public function latestFollowUp()
    {
        return $this->hasOne(FollowUp::class, 'id_lead', 'id_lead')->ofMany('id_follow_up', 'max');
    }
    public function transaksi() { return $this->hasMany(TransaksiLead::class, 'id_lead', 'id_lead'); }
        public function pic()
    {
        return $this->belongsTo(PicMarketing::class, 'id_pic', 'id_pic');
    }
    protected static function booted()
    {
        static::updated(function ($lead) {
            if ($lead->wasChanged('status_lead')) {

                $oldStatus = $lead->getOriginal('status_lead');
                $newStatus = $lead->status_lead;

                if ($lead->id_pic) {

                    $isUpConvert =
                        ($oldStatus === 'Cold Lead' && $newStatus === 'Warm Lead') ||
                        ($oldStatus === 'Warm Lead' && $newStatus === 'Hot Prospek');

                    if ($isUpConvert) {
                        PicMarketing::where('id_pic', $lead->id_pic)->increment('up_convert');
                    }

                    $isDownConvert =
                        ($oldStatus === 'Hot Prospek' && in_array($newStatus, ['Warm Lead', 'Cold Lead', 'Gagal Closing'])) ||
                        ($oldStatus === 'Warm Lead' && in_array($newStatus, ['Cold Lead', 'Gagal Closing']));

                    if ($isDownConvert) {
                        PicMarketing::where('id_pic', $lead->id_pic)->increment('down_convert');
                    }
                }
            }
        });
    }
}
