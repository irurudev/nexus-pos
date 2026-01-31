<?php

namespace App\DTOs;

final readonly class PenjualanData
{
    /**
     * @param  array<ItemPenjualanData>  $items
     */
    public function __construct(
        public string $id_nota,
        public string $tgl,
        public ?string $kode_pelanggan,
        public int $user_id,
        public float $subtotal,
        public float $diskon = 0,
        public float $pajak = 0,
        public array $items = [],
    ) {}

    public static function from(array $data): self
    {
        $items = array_map(
            fn (array $item) => ItemPenjualanData::from($item),
            $data['items'] ?? []
        );

        return new self(
            id_nota: $data['id_nota'],
            tgl: $data['tgl'],
            kode_pelanggan: $data['kode_pelanggan'] ?? null,
            user_id: (int) $data['user_id'],
            subtotal: (float) $data['subtotal'],
            diskon: (float) ($data['diskon'] ?? 0),
            pajak: (float) ($data['pajak'] ?? 0),
            items: $items,
        );
    }

    public function totalAkhir(): float
    {
        return $this->subtotal - $this->diskon + $this->pajak;
    }

    public function toArray(): array
    {
        return [
            'id_nota' => $this->id_nota,
            'tgl' => $this->tgl,
            'kode_pelanggan' => $this->kode_pelanggan,
            'user_id' => $this->user_id,
            'subtotal' => $this->subtotal,
            'diskon' => $this->diskon,
            'pajak' => $this->pajak,
            'total_akhir' => $this->totalAkhir(),
            'items' => array_map(fn (ItemPenjualanData $item) => $item->toArray(), $this->items),
        ];
    }
}
