<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class KategoriController extends BaseController
{
    /**
     * @OA\Tag(
     *   name="Kategoris",
     *   description="Operasi CRUD untuk kategori"
     * )
     *
     * @OA\Schema(
     *   schema="Kategori",
     *   type="object",
     *
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="nama_kategori", type="string", example="Sembako"),
     *   @OA\Property(property="created_at", type="string", format="date-time"),
     *   @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     *
     * @OA\Schema(
     *   schema="KategoriCreate",
     *   type="object",
     *   required={"nama_kategori"},
     *
     *   @OA\Property(property="nama_kategori", type="string", example="Sembako")
     * )
     */
    /**
     * @OA\Get(
     *     path="/kategoris",
     *     operationId="getKategoris",
     *     tags={"Kategoris"},
     *     summary="Dapatkan semua kategori",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data kategori berhasil diambil",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kategori berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id_kategori", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Sembako"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $kategoris = Kategori::latest()->paginate($perPage);

            return $this->paginatedResponse($kategoris, 'Data kategori berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil data kategori',
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/kategoris/{id}",
     *     operationId="getKategoriDetail",
     *     tags={"Kategoris"},
     *     summary="Dapatkan detail kategori beserta barangnya",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Kategori",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data kategori berhasil diambil",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $kategori = Kategori::with('barangs')->find($id);

            if (! $kategori) {
                return $this->errorResponse(
                    'Kategori tidak ditemukan',
                    404
                );
            }

            return $this->successResponse($kategori, 'Data kategori berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan',
                500
            );
        }
    }

    /**
     * Create new kategori (admin only).
     */
    /**
     * @OA\Post(
     *     path="/kategoris",
     *     operationId="createKategori",
     *     tags={"Kategoris"},
     *     summary="Buat kategori baru",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nama_kategori"},
     *
     *             @OA\Property(property="nama_kategori", type="string", example="Sembako")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Kategori berhasil dibuat",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(\App\Http\Requests\Kategori\StoreKategoriRequest $request)
    {
        try {
            $validated = $request->validated();

            $kategori = Kategori::create($validated);

            return $this->successResponse($kategori, 'Kategori berhasil dibuat', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat membuat kategori',
                500
            );
        }
    }

    /**
     * Update kategori (admin only).
     */
    /**
     * @OA\Put(
     *     path="/kategoris/{id}",
     *     operationId="updateKategori",
     *     tags={"Kategoris"},
     *     summary="Update kategori",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nama_kategori"},
     *
     *             @OA\Property(property="nama_kategori", type="string", example="Sembako")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berhasil diperbarui",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(\App\Http\Requests\Kategori\UpdateKategoriRequest $request, $id)
    {
        try {
            $kategori = Kategori::find($id);

            if (! $kategori) {
                return $this->errorResponse(
                    'Kategori tidak ditemukan',
                    404
                );
            }

            $validated = $request->validated();

            $kategori->update($validated);

            return $this->successResponse($kategori, 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengubah kategori',
                500
            );
        }
    }

    /**
     * Delete kategori (admin only).
     */
    /**
     * @OA\Delete(
     *     path="/kategoris/{id}",
     *     operationId="deleteKategori",
     *     tags={"Kategoris"},
     *     summary="Hapus kategori",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berhasil dihapus",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(\App\Http\Requests\Kategori\DestroyKategoriRequest $request, $id)
    {
        try {
            $kategori = Kategori::find($id);

            if (! $kategori) {
                return $this->errorResponse(
                    'Kategori tidak ditemukan',
                    404
                );
            }

            $kategori->delete();

            return $this->successResponse(
                message: 'Kategori berhasil dihapus'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghapus kategori',
                500
            );
        }
    }
}
