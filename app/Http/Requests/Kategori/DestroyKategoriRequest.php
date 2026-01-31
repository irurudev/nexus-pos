<?php

namespace App\Http\Requests\Kategori;

use Illuminate\Foundation\Http\FormRequest;

class DestroyKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        $routeKategori = $this->route('kategori');

        if ($routeKategori instanceof \App\Models\Kategori) {
            $kategori = $routeKategori;
        } else {
            $id = $this->route('id') ?? $this->route('kategori');
            $kategori = $id ? \App\Models\Kategori::find($id) : null;
        }

        return $this->user() && ($kategori ? $this->user()->can('delete', $kategori) : false);
    }

    public function rules(): array
    {
        return [];
    }
}
