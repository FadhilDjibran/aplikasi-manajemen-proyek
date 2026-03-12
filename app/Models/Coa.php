<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Keuangan;

class Coa extends Model
{
    protected $table = 'coa';

    protected $guarded = ['id'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function keuangans(): HasMany
    {
        return $this->hasMany(Keuangan::class, 'no_akun', 'no_akun');
    }
}
