<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class StorePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\Pelanggan::class);
    }

    public function rules(): array
    {
        return [
            'id_pelanggan' => 'sometimes|nullable|string|max:20|unique:pelanggans,id_pelanggan',
            'nama' => 'required|string|max:100',
            'domisili' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:PRIA,WANITA',
            'poin' => 'nullable|integer|min:0',
        ];
    }
}
