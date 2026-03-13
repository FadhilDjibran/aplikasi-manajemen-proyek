<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keuangan extends Model
{
    protected $table = 'keuangan';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'mutasi_masuk' => 'decimal:2',
        'mutasi_keluar' => 'decimal:2',
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'no_akun', 'no_akun')
                    ->where('project_id', session('active_project_id'));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
