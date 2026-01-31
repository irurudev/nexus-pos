<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class DestroyPelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route parameter may be named 'id_pelanggan' or 'pelanggan' (apiResource default)
        $param = $this->route('id_pelanggan') ?? $this->route('pelanggan');

        if ($param instanceof \App\Models\Pelanggan) {
            $pelanggan = $param;
            $id = $pelanggan->id_pelanggan;
        } else {
            $id = $param;
            $pelanggan = $id ? \App\Models\Pelanggan::find($id) : null;
        }

        // Debug log to help diagnose authorization problems
        \Illuminate\Support\Facades\Log::debug('DestroyPelangganRequest authorize', [
            'route_param' => $param,
            'user_id' => $this->user()?->id,
            'user_role' => $this->user()?->role ?? null,
            'id' => $id,
            'pelanggan_found' => $pelanggan ? true : false,
            'can_delete' => $pelanggan ? ($this->user()?->can('delete', $pelanggan) ?? false) : false,
        ]);

        return $this->user() && ($pelanggan ? $this->user()->can('delete', $pelanggan) : false);
    }

    public function rules(): array
    {
        return [];
    }
}
