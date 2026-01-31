<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends BaseController
{
    /**
     * Summary analytics: total penjualan, laba, pajak, diskon, transaksi.
     */
    public function summary(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $penjualans = DB::table('penjualans')
                ->whereBetween('tgl', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('SUM(total_akhir) as total_penjualan')
                ->selectRaw('SUM(diskon) as total_diskon')
                ->selectRaw('SUM(pajak) as total_pajak')
                ->first();

            $laba = DB::table('item_penjualans as ip')
                ->join('barangs as b', 'b.kode_barang', '=', 'ip.kode_barang')
                ->join('penjualans as p', 'p.id_nota', '=', 'ip.nota')
                ->whereBetween('p.tgl', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->selectRaw('SUM((ip.harga_satuan - b.harga_beli) * ip.qty) as total_laba')
                ->value('total_laba');

            $jumlahTransaksi = (int) ($penjualans->jumlah_transaksi ?? 0);
            $totalPenjualan = (float) ($penjualans->total_penjualan ?? 0);

            return $this->successResponse([
                'periode' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total_penjualan' => round($totalPenjualan, 2),
                'total_diskon' => round((float) ($penjualans->total_diskon ?? 0), 2),
                'total_pajak' => round((float) ($penjualans->total_pajak ?? 0), 2),
                'total_laba' => round((float) ($laba ?? 0), 2),
                'jumlah_transaksi' => $jumlahTransaksi,
                'rata_rata_transaksi' => $jumlahTransaksi > 0 ? round($totalPenjualan / $jumlahTransaksi, 2) : 0,
            ], 'Summary analytics berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil summary analytics', 500);
        }
    }

    /**
     * Top kategori berdasarkan qty terjual.
     */
    public function topKategori(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());
            $limit = (int) $request->get('limit', 10);

            $top = DB::table('item_penjualans as ip')
                ->join('barangs as b', 'b.kode_barang', '=', 'ip.kode_barang')
                ->join('kategoris as k', 'k.id', '=', 'b.kategori_id')
                ->join('penjualans as p', 'p.id_nota', '=', 'ip.nota')
                ->whereBetween('p.tgl', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->groupBy('k.id', 'k.nama_kategori')
                ->select('k.id', 'k.nama_kategori')
                ->selectRaw('SUM(ip.qty) as total_qty')
                ->selectRaw('SUM(ip.jumlah) as total_penjualan')
                ->orderByDesc('total_qty')
                ->limit($limit)
                ->get();

            return $this->successResponse([
                'periode' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'items' => $top,
            ], 'Top kategori berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil top kategori', 500);
        }
    }

    /**
     * Performa kasir berdasarkan total penjualan per bulan.
     */
    public function kasirPerformance(Request $request)
    {
        try {
            $year = (int) $request->get('year', now()->year);

            $rows = DB::table('penjualans as p')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->whereYear('p.tgl', $year)
                ->groupBy('u.id', 'u.name', 'u.username', DB::raw('MONTH(p.tgl)'))
                ->select('u.id', 'u.name', 'u.username')
                ->selectRaw('MONTH(p.tgl) as bulan')
                ->selectRaw('SUM(p.total_akhir) as total_penjualan')
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->orderBy('u.id')
                ->orderBy('bulan')
                ->get();

            return $this->successResponse([
                'year' => $year,
                'items' => $rows,
            ], 'Performa kasir berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil performa kasir', 500);
        }
    }
}
