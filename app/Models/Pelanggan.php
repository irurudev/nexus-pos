<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggans';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pelanggan',
        'nama',
        'domisili',
        'jenis_kelamin',
        'poin',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    /**
     * Get all penjualans for this pelanggan.
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'kode_pelanggan');
    }

    /**
     * Generate a unique id_pelanggan in format: prefix PGN + three digits (e.g. "PGN123").
     * Attempts up to 50 times to avoid collisions.
     */
    public static function generateId(): string
    {
        $seq = \App\Models\Sequence::next('pelanggan');

        return 'PGN'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
