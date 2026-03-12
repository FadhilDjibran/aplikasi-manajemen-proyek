<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Userstamps;

class TransaksiLead extends Model
{
    use Userstamps;
    use HasFactory;
    protected $table = 'transaksi_lead';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'project_id',
        'id_lead',
        'jenis_pembayaran',
        'nominal',
        'tgl_pembayaran',
        'keterangan'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'id_lead', 'id_lead');
    }
}
