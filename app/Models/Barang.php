<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barangs';

    protected $primaryKey = 'kode_barang';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'kategori_id',
        'nama',
        'harga_beli',
        'harga_jual',
        'stok',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
    ];

    /**
     * Get the kategori for this barang.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Get all item_penjualans for this barang.
     */
    public function itemPenjualans(): HasMany
    {
        return $this->hasMany(ItemPenjualan::class, 'kode_barang');
    }

    /**
     * Generate a unique kode_barang in format: prefix BRG + three digits (e.g. "BRG123").
     * Attempts up to 50 times to avoid collisions.
     */
    public static function generateKode(): string
    {
        // Use sequence table for safe auto-increment suffix
        $seq = \App\Models\Sequence::next('barang');

        $code = 'BRG'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

        return $code;
    }
}
