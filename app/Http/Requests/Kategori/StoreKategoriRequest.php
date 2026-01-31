<?php

namespace App\Http\Requests\Kategori;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\Kategori::class);
    }

    public function rules(): array
    {
        return [
            'nama_kategori' => 'required|string|max:50|unique:kategoris,nama_kategori',
        ];
    }
}
