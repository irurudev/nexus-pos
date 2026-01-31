<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;

class StorePenjualanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users with permission may create penjualan
        return $this->user() && $this->user()->can('create', \App\Models\Penjualan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // allow backend to generate id_nota and tgl if not provided
            'id_nota' => 'nullable|string|max:20|unique:penjualans,id_nota',
            'tgl' => 'nullable|date_format:Y-m-d H:i:s',
            'kode_pelanggan' => 'nullable|string|max:20|exists:pelanggans,id_pelanggan',
            'diskon' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|max:20|exists:barangs,kode_barang',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        // After base validation, ensure requested qty does not exceed current stock
        $validator->after(function ($v) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $kode = $item['kode_barang'] ?? null;
                $qty = isset($item['qty']) ? (int) $item['qty'] : null;

                if ($kode && $qty !== null) {
                    $barang = \App\Models\Barang::withTrashed()->where('kode_barang', $kode)->first();

                    if (! $barang) {
                        // exists rule will catch this, but double-check
                        $v->errors()->add("items.$index.kode_barang", 'Barang tidak ditemukan');
                        continue;
                    }

                    $stok = (int) ($barang->stok ?? 0);

                    if ($qty > $stok) {
                        $v->errors()->add("items.$index.qty", "Stok tidak mencukupi (tersisa: $stok)");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_nota.required' => 'ID Nota harus diisi',
            'id_nota.unique' => 'ID Nota sudah digunakan',
            'tgl.required' => 'Tanggal harus diisi',
            'tgl.date_format' => 'Format tanggal harus Y-m-d H:i:s',
            'kode_pelanggan.exists' => 'Pelanggan tidak ditemukan',
            'items.required' => 'Item penjualan minimal 1',
            'items.*.kode_barang.required' => 'Kode barang harus diisi',
            'items.*.kode_barang.exists' => 'Barang tidak ditemukan',
            'items.*.qty.required' => 'Jumlah harus diisi',
            'items.*.qty.min' => 'Jumlah minimal 1',
            'items.*.harga_satuan.required' => 'Harga satuan harus diisi',
        ];
    }
}
