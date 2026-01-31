<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategoris';

    protected $fillable = [
        'nama_kategori',
    ];

    protected $appends = ['id_kategori'];

    protected $hidden = ['id'];

    /**
     * Get the id_kategori attribute.
     */
    public function getIdKategoriAttribute()
    {
        return $this->id;
    }

    /**
     * Get all barangs for this kategori.
     */
    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
