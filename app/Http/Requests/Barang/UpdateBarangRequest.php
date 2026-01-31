<?php

namespace App\Http\Requests\Barang;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route parameter may be named 'kode_barang' or the resource key 'barang' (apiResource default).
        $param = $this->route('kode_barang') ?? $this->route('barang');

        if ($param instanceof \App\Models\Barang) {
            $barang = $param;
            $kode = $barang->kode_barang;
        } else {
            $kode = $param;
            $barang = $kode ? \App\Models\Barang::find($kode) : null;
        }

        // Debug logging for authorization issues
        \Illuminate\Support\Facades\Log::debug('UpdateBarangRequest authorize', [
            'route_param' => $param,
            'user_id' => $this->user()?->id,
            'user_role' => $this->user()?->role ?? null,
            'kode' => $kode,
            'barang_found' => $barang ? true : false,
            'can_update' => $barang ? ($this->user()?->can('update', $barang) ?? false) : false,
        ]);

        return $this->user() && ($barang ? $this->user()->can('update', $barang) : false);
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'sometimes|exists:kategoris,id',
            'nama' => 'sometimes|string|max:100',
            'harga_beli' => 'sometimes|numeric|min:0',
            'harga_jual' => 'sometimes|numeric|min:0',
            'stok' => 'sometimes|integer|min:0',
        ];
    }
}
