<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penjualans';

    protected $primaryKey = 'id_nota';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_nota',
        'tgl',
        'kode_pelanggan',
        'user_id',
        'subtotal',
        'diskon',
        'pajak',
        'total_akhir',
    ];

    protected $casts = [
        'tgl' => 'datetime',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'pajak' => 'decimal:2',
        'total_akhir' => 'decimal:2',
    ];

    /**
     * Get the pelanggan for this penjualan.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan');
    }

    /**
     * Get the user (kasir) for this penjualan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all item_penjualans for this penjualan.
     */
    public function itemPenjualans(): HasMany
    {
        return $this->hasMany(ItemPenjualan::class, 'nota');
    }

    /**
     * Generate a unique id_nota, format: INVYYYYMMDD-XXXX
     */
    public static function generateId(): string
    {
        $seq = \App\Models\Sequence::next('penjualan');
        $code = 'INV'.now()->format('Ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        return $code;
    }
}
