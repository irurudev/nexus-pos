<?php

namespace App\Http\Requests\Kategori;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        // route model binding may provide the Kategori model directly under 'kategori'
        $routeKategori = $this->route('kategori');
        $id = null;

        if ($routeKategori instanceof \App\Models\Kategori) {
            $kategori = $routeKategori;
            $id = $kategori->id;
        } else {
            $id = $this->route('id') ?? $this->route('kategori');
            $kategori = $id ? \App\Models\Kategori::find($id) : null;
        }

        return $this->user() && ($kategori ? $this->user()->can('update', $kategori) : false);
    }

    public function rules(): array
    {
        $routeKategori = $this->route('kategori');
        $id = null;

        if ($routeKategori instanceof \App\Models\Kategori) {
            $id = $routeKategori->id;
        } else {
            $id = $this->route('id') ?? $this->route('kategori');
        }

        return [
            'nama_kategori' => 'required|string|max:50|unique:kategoris,nama_kategori,'.($id ?? 'NULL'),
        ];
    }
}
