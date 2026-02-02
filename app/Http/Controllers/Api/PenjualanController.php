<?php

namespace App\Http\Controllers\Api;

use App\Actions\Penjualan\CreatePenjualanAction;
use App\DTOs\ItemPenjualanData;
use App\DTOs\PenjualanData;
use App\Http\Requests\Penjualan\StorePenjualanRequest;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Penjualans",
 *   description="Operasi untuk penjualan dan transaksi"
 * )
 *
 * @OA\Schema(
 *   schema="ItemPenjualan",
 *   type="object",
 *
 *   @OA\Property(property="kode_barang", type="string", example="BRG001"),
 *   @OA\Property(property="qty", type="integer", example=2),
 *   @OA\Property(property="harga_satuan", type="number", format="float", example=15000),
 *   @OA\Property(property="jumlah", type="number", format="float", example=30000)
 * )
 *
 * @OA\Schema(
 *   schema="PenjualanCreate",
 *   type="object",
 *   required={"id_nota","tgl","items"},
 *
 *   @OA\Property(property="id_nota", type="string", example="INV-20250101-0001"),
 *   @OA\Property(property="tgl", type="string", format="date-time", example="2025-01-01 10:00:00"),
 *   @OA\Property(property="kode_pelanggan", type="string", example="PLG001", nullable=true),
 *   @OA\Property(property="diskon", type="number", example=0),
 *   @OA\Property(property="pajak", type="number", example=0),
 *   @OA\Property(
 *       property="items",
 *       type="array",
 *
 *       @OA\Items(ref="#/components/schemas/ItemPenjualan")
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="Penjualan",
 *   type="object",
 *
 *   @OA\Property(property="id_nota", type="string"),
 *   @OA\Property(property="tgl", type="string", format="date-time"),
 *   @OA\Property(property="kode_pelanggan", type="string", nullable=true),
 *   @OA\Property(property="user_id", type="integer"),
 *   @OA\Property(property="subtotal", type="number"),
 *   @OA\Property(property="diskon", type="number"),
 *   @OA\Property(property="pajak", type="number"),
 *   @OA\Property(property="total_akhir", type="number"),
 *   @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemPenjualan"))
 * )
 */
class PenjualanController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/penjualans",
     *     operationId="getPenjualans",
     *     tags={"Penjualans"},
     *     summary="Dapatkan semua penjualan (filter & pagination)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-01")),
     *     @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-31")),
     *     @OA\Parameter(name="user_id", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string", example="INV-20250101")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Pagination")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // authorization: ensure user can view penjualans
            $this->authorize('viewAny', Penjualan::class);

            $perPage = $request->get('per_page', 15);
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $user_id = $request->get('user_id');

            $query = Penjualan::with(['itemPenjualans' => function ($q) {
                $q->latest();
            }, 'pelanggan', 'user']);

            if ($startDate) {
                $query->whereDate('tgl', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('tgl', '<=', $endDate);
            }

            if ($user_id) {
                $query->where('user_id', $user_id);
            }

            $search = $request->get('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id_nota', 'like', "%{$search}%")
                      ->orWhere('total_akhir', 'like', "%{$search}%")
                      ->orWhereHas('pelanggan', function ($qq) use ($search) {
                          $qq->where('nama', 'like', "%{$search}%");
                      })
                      ->orWhereHas('itemPenjualans', function ($qq) use ($search) {
                          $qq->where('nama_barang', 'like', "%{$search}%");
                      });
                });
            }

            $penjualans = $query->latest('tgl')->paginate($perPage);

            return $this->paginatedResponse($penjualans, 'Data penjualan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil data penjualan',
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/penjualans/{penjualan}",
     *     operationId="getPenjualanDetail",
     *     tags={"Penjualans"},
     *     summary="Dapatkan detail penjualan",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="penjualan",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="INV-20250101-0001")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Penjualan $penjualan)
    {
        try {
            $penjualan->load([ 'itemPenjualans' => function ($q) { $q->latest(); }, 'itemPenjualans.barang' => function ($q) { $q->withTrashed(); }, 'pelanggan', 'user' ]);

            $this->authorize('view', $penjualan);

            return $this->successResponse($penjualan, 'Data penjualan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan',
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/penjualans",
     *     operationId="createPenjualan",
     *     tags={"Penjualans"},
     *     summary="Buat transaksi penjualan",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/PenjualanCreate")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Penjualan berhasil dibuat",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StorePenjualanRequest $request)
    {
        try {
            // authorization: ensure user can create a penjualan
            $this->authorize('create', Penjualan::class);

            $validated = $request->validated();

            // ensure backend generates id_nota and tgl when not provided
            $validated['id_nota'] = $validated['id_nota'] ?? Penjualan::generateId();
            $validated['tgl'] = $validated['tgl'] ?? now()->toDateTimeString();
            $validated['user_id'] = $request->user()->id;

            // calculate jumlah for each item if not provided
            $validated['items'] = array_map(function (array $item) {
                $item['jumlah'] = $item['jumlah'] ?? ((float) ($item['qty'] ?? 0) * (float) ($item['harga_satuan'] ?? 0));

                return $item;
            }, $validated['items'] ?? []);

            // calculate subtotal from items
            $validated['subtotal'] = (float) collect($validated['items'])->sum(fn ($i) => (float) ($i['jumlah'] ?? 0));

            $penjualanData = PenjualanData::from($validated);

            $action = new CreatePenjualanAction;
            $penjualan = $action->execute($penjualanData);

            // ensure itemPenjualans include barang (including trashed) for detail responses
            $penjualan->load(['itemPenjualans.barang' => function ($q) { $q->withTrashed(); }, 'pelanggan', 'user']);

            return $this->successResponse(
                $penjualan,
                'Penjualan berhasil dibuat',
                201
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorResponse('Database error saat membuat penjualan', 500, ['error' => $e->getMessage()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Validasi gagal',
                422,
                $e->errors()
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorResponse('Database error saat membuat penjualan', 500, ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat membuat penjualan: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/penjualans/summary",
     *     operationId="getPenjualanSummary",
     *     tags={"Penjualans"},
     *     summary="Dapatkan rangkuman penjualan (summary)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-01")),
     *     @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date", example="2025-01-31")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Summary data",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="periode", type="object", @OA\Property(property="start_date", type="string"), @OA\Property(property="end_date", type="string")),
     *             @OA\Property(property="total_penjualan", type="number"),
     *             @OA\Property(property="total_diskon", type="number"),
     *             @OA\Property(property="total_pajak", type="number"),
     *             @OA\Property(property="total_laba", type="number"),
     *             @OA\Property(property="jumlah_transaksi", type="integer"),
     *             @OA\Property(property="rata_rata_transaksi", type="number")
     *         )
     *     )
     * )
     */
    public function summary(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $penjualans = Penjualan::whereBetween('tgl', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->with(['itemPenjualans' => function ($q) {
                    $q->latest();
                }])
                ->get();

            $totalPenjualan = $penjualans->sum('total_akhir');
            $totalDiskon = $penjualans->sum('diskon');
            $totalPajak = $penjualans->sum('pajak');
            $jumlahTransaksi = $penjualans->count();

            // Calculate profit (laba)
            $totalLaba = 0;
            foreach ($penjualans as $penjualan) {
                foreach ($penjualan->itemPenjualans as $item) {
                    $hargaBeli = $item->barang->harga_beli;
                    $totalLaba += ($item->harga_satuan - $hargaBeli) * $item->qty;
                }
            }

            return $this->successResponse([
                'periode' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total_penjualan' => round($totalPenjualan, 2),
                'total_diskon' => round($totalDiskon, 2),
                'total_pajak' => round($totalPajak, 2),
                'total_laba' => round($totalLaba, 2),
                'jumlah_transaksi' => $jumlahTransaksi,
                'rata_rata_transaksi' => $jumlahTransaksi > 0 ? round($totalPenjualan / $jumlahTransaksi, 2) : 0,
            ], 'Summary penjualan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil summary penjualan',
                500
            );
        }
    }
}
