<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Barang\DestroyBarangRequest;
use App\Http\Requests\Barang\StoreBarangRequest;
use App\Http\Requests\Barang\UpdateBarangRequest;
use App\Models\Barang;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Barangs",
 *   description="Operasi CRUD untuk barang"
 * )
 *
 * @OA\Schema(
 *   schema="Barang",
 *   type="object",
 *
 *   @OA\Property(property="kode_barang", type="string", example="BRG001"),
 *   @OA\Property(property="kategori_id", type="integer", example=1),
 *   @OA\Property(property="nama", type="string", example="Beras 5kg"),
 *   @OA\Property(property="harga_beli", type="number", format="float", example=50000),
 *   @OA\Property(property="harga_jual", type="number", format="float", example=65000),
 *   @OA\Property(property="stok", type="integer", example=10),
 *   @OA\Property(property="kategori", type="object")
 * )
 *
 * @OA\Schema(
 *   schema="BarangCreate",
 *   type="object",
 *   required={"kategori_id","nama","harga_beli","harga_jual","stok"},
 *
 *   @OA\Property(property="kode_barang", type="string", example="BRG123", description="Optional. Jika tidak diisi, sistem akan mengenerate otomatis (prefix BRG + 3 angka)."),
 *   @OA\Property(property="kategori_id", type="integer", example=1),
 *   @OA\Property(property="nama", type="string", example="Beras 5kg"),
 *   @OA\Property(property="harga_beli", type="number", format="float", example=50000),
 *   @OA\Property(property="harga_jual", type="number", format="float", example=65000),
 *   @OA\Property(property="stok", type="integer", example=10)
 * )
 */
class BarangController extends BaseController
{
    /**
     * @OA\Get(
     *   path="/barangs",
     *   operationId="getBarangs",
     *   tags={"Barangs"},
     *   summary="Dapatkan semua barang (pagination)",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *
     *     @OA\Schema(type="integer", example=15)
     *   ),
     *
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     required=false,
     *
     *     @OA\Schema(type="string", example="beras")
     *   ),
     *
     *   @OA\Parameter(
     *     name="kategori_id",
     *     in="query",
     *     required=false,
     *
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(ref="#/components/schemas/Pagination")
     *   )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $kategori_id = $request->get('kategori_id');

            $query = Barang::with('kategori');

            if ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            }

            if ($kategori_id) {
                $query->where('kategori_id', $kategori_id);
            }

            // Order by newest created records first
            $barangs = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($barangs, 'Data barang berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil data barang',
                500
            );
        }
    }

    /**
     * @OA\Get(
     *   path="/barangs/{kode_barang}",
     *   operationId="getBarangDetail",
     *   tags={"Barangs"},
     *   summary="Dapatkan detail barang",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="kode_barang",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(type="string", example="BRG001")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Barang $barang)
    {
        try {
            $barang->load('kategori');

            return $this->successResponse($barang, 'Data barang berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan',
                500
            );
        }
    }

    /**
     * @OA\Post(
     *   path="/barangs",
     *   operationId="createBarang",
     *   tags={"Barangs"},
     *   summary="Buat barang baru",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/BarangCreate")
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Created",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreBarangRequest $request)
    {
        try {
            $validated = $request->validated();

            // Generate kode_barang if not provided
            if (empty($validated['kode_barang'])) {
                $validated['kode_barang'] = Barang::generateKode();
            }

            $barang = Barang::create($validated);

            return $this->successResponse($barang, 'Barang berhasil dibuat', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat membuat barang',
                500
            );
        }
    }

    /**
     * @OA\Put(
     *   path="/barangs/{kode_barang}",
     *   operationId="updateBarang",
     *   tags={"Barangs"},
     *   summary="Update barang",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="kode_barang",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(type="string", example="BRG001")
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *
     *       @OA\Property(property="kategori_id", type="integer", example=1),
     *       @OA\Property(property="nama", type="string", example="Beras 10kg"),
     *       @OA\Property(property="harga_beli", type="number", format="float", example=60000),
     *       @OA\Property(property="harga_jual", type="number", format="float", example=75000),
     *       @OA\Property(property="stok", type="integer", example=20)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        try {
            $validated = $request->validated();

            $barang->update($validated);

            return $this->successResponse($barang, 'Barang berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengubah barang',
                500
            );
        }
    }

    /**
     * @OA\Delete(
     *   path="/barangs/{kode_barang}",
     *   operationId="deleteBarang",
     *   tags={"Barangs"},
     *   summary="Hapus barang",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="kode_barang",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(type="string", example="BRG001")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Deleted",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(DestroyBarangRequest $request, Barang $barang)
    {
        try {
            $request->validated();

            $barang->delete();

            return $this->successResponse(
                message: 'Barang berhasil dihapus'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghapus barang',
                500
            );
        }
    }
}
