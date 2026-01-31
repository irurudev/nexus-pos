<?php

namespace App\DTOs;

final readonly class ItemPenjualanData
{
    public function __construct(
        public string $kode_barang,
        public int $qty,
        public float $harga_satuan,
        public float $jumlah,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            kode_barang: $data['kode_barang'],
            qty: (int) $data['qty'],
            harga_satuan: (float) $data['harga_satuan'],
            jumlah: (float) $data['jumlah'],
        );
    }

    public function toArray(): array
    {
        return [
            'kode_barang' => $this->kode_barang,
            'qty' => $this->qty,
            'harga_satuan' => $this->harga_satuan,
            'jumlah' => $this->jumlah,
        ];
    }
}
