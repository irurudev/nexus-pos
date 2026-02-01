<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class AnalyticsController extends BaseController
{
    /**
     * @OA\Get(
     *   path="/analytics/summary",
     *   operationId="analyticsSummary",
     *   tags={"Analytics"},
     *   summary="Ringkasan penjualan (total, pajak, diskon, laba)",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-01")),
     *   @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-31")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(type="object", @OA\Property(property="data", type="object"))
     *   )
     * )
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
     * @OA\Get(
     *   path="/analytics/top-kategori",
     *   operationId="analyticsTopKategori",
     *   tags={"Analytics"},
     *   summary="Top kategori berdasarkan qty terjual",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-01")),
     *   @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-31")),
     *   @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", example=10)),
     *
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(type="object"))
     * )
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
     * @OA\Get(
     *   path="/analytics/kasir-performance",
     *   operationId="analyticsKasirPerformance",
     *   tags={"Analytics"},
     *   summary="Performa kasir per bulan",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="year", in="query", @OA\Schema(type="integer", example=2025)),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(type="object"))
     * )
     */
    public function kasirPerformance(Request $request)
    {
        try {
            $year = (int) $request->get('year', now()->year);
            $driver = DB::getDriverName();

            // Use driver-appropriate month/year extraction to support sqlite during tests.
            if ($driver === 'sqlite') {
                $monthExpr = "CAST(strftime('%m', p.tgl) AS INTEGER)";
                $yearCondition = ["strftime('%Y', p.tgl) = ?", [(string) $year]];

                $rows = DB::table('penjualans as p')
                    ->join('users as u', 'u.id', '=', 'p.user_id')
                    ->whereRaw($yearCondition[0], $yearCondition[1])
                    ->groupBy('u.id', 'u.name', 'u.username', DB::raw($monthExpr))
                    ->select('u.id', 'u.name', 'u.username')
                    ->selectRaw("{$monthExpr} as bulan")
                    ->selectRaw('SUM(p.total_akhir) as total_penjualan')
                    ->selectRaw('COUNT(*) as jumlah_transaksi')
                    ->orderBy('u.id')
                    ->orderBy('bulan')
                    ->get();
            } else {
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
            }

            return $this->successResponse([
                'year' => $year,
                'items' => $rows,
            ], 'Performa kasir berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil performa kasir', 500);
        }
    }
}
