<?php

namespace App\Actions\Penjualan;

use App\DTOs\PenjualanData;
use App\Models\Barang;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;

class CreatePenjualanAction
{
    /**
     * Create a new penjualan with items and update stok barang.
     *
     * @throws \Exception
     */
    public function execute(PenjualanData $data): Penjualan
    {
        return DB::transaction(function () use ($data) {
            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($data->items as $item) {
                $subtotal += $item->jumlah;
            }

            // Create penjualan record
            $penjualan = Penjualan::create([
                'id_nota' => $data->id_nota,
                'tgl' => $data->tgl,
                'kode_pelanggan' => $data->kode_pelanggan,
                'user_id' => $data->user_id,
                'subtotal' => $subtotal,
                'diskon' => $data->diskon,
                'pajak' => $data->pajak,
                'total_akhir' => $subtotal - $data->diskon + $data->pajak,
            ]);

            // Create item penjualans and update stok
            foreach ($data->items as $index => $item) {
                // lock the barang row to prevent race conditions
                $barang = Barang::where('kode_barang', $item->kode_barang)->lockForUpdate()->first();

                $stokAvailable = $barang ? (int) $barang->stok : 0;

                if (! $barang || $stokAvailable < $item->qty) {
                    // throw validation-like exception with message mapped to item index
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.$index.qty" => ["Stok tidak mencukupi untuk {$item->kode_barang} (tersisa: $stokAvailable)"],
                    ]);
                }

                $penjualan->itemPenjualans()->create([
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $barang?->nama ?? null,
                    'qty' => $item->qty,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah,
                ]);

                // Update stok barang
                Barang::where('kode_barang', $item->kode_barang)
                    ->decrement('stok', $item->qty);
            }

            return $penjualan->load('itemPenjualans');
        });
    }
}
