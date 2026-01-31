<?php

namespace App\Http\Requests\Barang;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\Barang::class);
    }

    public function rules(): array
    {
        return [
            'kode_barang' => 'sometimes|nullable|string|max:20|unique:barangs,kode_barang',
            'kategori_id' => 'required|exists:kategoris,id',
            'nama' => 'required|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori harus diisi',
            'kategori_id.exists' => 'Kategori tidak ditemukan',
        ];
    }
}
