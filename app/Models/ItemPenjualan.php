<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'item_penjualans';

    protected $fillable = [
        'nota',
        'kode_barang',
        'nama_barang',
        'qty',
        'harga_satuan',
        'jumlah',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the penjualan for this item.
     */
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'nota');
    }

    /**
     * Get the barang for this item.
     */
    public function barang(): BelongsTo
    {
        // Include trashed barang so we can still access historical data if a barang was soft-deleted
        return $this->belongsTo(Barang::class, 'kode_barang')->withTrashed();
    }
}
