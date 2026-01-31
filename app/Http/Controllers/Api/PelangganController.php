<?php

namespace App\Http\Controllers\Api;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Pelanggans", description="Operasi CRUD untuk pelanggan")
 *
 * @OA\Schema(
 *   schema="Pelanggan",
 *   type="object",
 *
 *   @OA\Property(property="id_pelanggan", type="string", example="PGN123"),
 *   @OA\Property(property="nama", type="string", example="John Doe"),
 *   @OA\Property(property="domisili", type="string", example="Jakarta"),
 *   @OA\Property(property="jenis_kelamin", type="string", enum={"PRIA","WANITA"}, example="PRIA"),
 *   @OA\Property(property="poin", type="integer", example=0)
 * )
 *
 * @OA\Schema(
 *   schema="PelangganCreate",
 *   type="object",
 *   required={"nama","jenis_kelamin"},
 *
 *   @OA\Property(property="id_pelanggan", type="string", example="PGN123"),
 *   @OA\Property(property="nama", type="string", example="John Doe"),
 *   @OA\Property(property="domisili", type="string", example="Jakarta"),
 *   @OA\Property(property="jenis_kelamin", type="string", enum={"PRIA","WANITA"}, example="PRIA")
 * )
 */
class PelangganController extends BaseController
{
    /**
     * Get all pelanggan with pagination.
     *
     * @OA\Get(
     *   path="/pelanggans",
     *   tags={"Pelanggans"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
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

            $query = Pelanggan::query();

            if ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('id_pelanggan', 'like', "%{$search}%");
            }

            // Order by newest created records first
            $pelanggans = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($pelanggans, 'Data pelanggan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil data pelanggan',
                500
            );
        }
    }

    /**
     * Get single pelanggan detail.
     *
     * @OA\Get(
     *   path="/pelanggans/{id}",
     *   tags={"Pelanggans"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID pelanggan",
     *
     *     @OA\Schema(type="string")
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
    public function show(Pelanggan $pelanggan)
    {
        try {
            return $this->successResponse($pelanggan, 'Data pelanggan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan',
                500
            );
        }
    }

    /**
     * Create new pelanggan.
     *
     * @OA\Post(
     *   path="/pelanggans",
     *   tags={"Pelanggans"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/PelangganCreate")
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Created",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(\App\Http\Requests\Pelanggan\StorePelangganRequest $request)
    {
        try {
            $validated = $request->validated();

            // Generate id_pelanggan if not provided (delegate to model generator PGN###)
            if (empty($validated['id_pelanggan'])) {
                $validated['id_pelanggan'] = Pelanggan::generateId();
            }

            $pelanggan = Pelanggan::create($validated);

            return $this->successResponse($pelanggan, 'Pelanggan berhasil dibuat', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat membuat pelanggan',
                500
            );
        }
    }

    /**
     * Update pelanggan.
     *
     * @OA\Put(
     *   path="/pelanggans/{id}",
     *   tags={"Pelanggans"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID pelanggan",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="nama", type="string"),
     *       @OA\Property(property="domisili", type="string"),
     *       @OA\Property(property="jenis_kelamin", type="string", enum={"PRIA","WANITA"}),
     *       @OA\Property(property="poin", type="integer")
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
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(\App\Http\Requests\Pelanggan\UpdatePelangganRequest $request, Pelanggan $pelanggan)
    {
        try {
            \Illuminate\Support\Facades\Log::debug('PelangganController update called', ['id' => $pelanggan->id_pelanggan, 'user_id' => $request->user()?->id]);

            $validated = $request->validated();

            $pelanggan->update($validated);

            return $this->successResponse($pelanggan, 'Pelanggan berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat mengubah pelanggan',
                500
            );
        }
    }

    /**
     * Delete pelanggan.
     *
     * @OA\Delete(
     *   path="/pelanggans/{id}",
     *   tags={"Pelanggans"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID pelanggan",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Deleted",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *   ),
     *
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(\App\Http\Requests\Pelanggan\DestroyPelangganRequest $request, Pelanggan $pelanggan)
    {
        try {
            \Illuminate\Support\Facades\Log::debug('PelangganController destroy called', ['id' => $pelanggan->id_pelanggan, 'user_id' => $request->user()?->id]);

            $pelanggan->delete();

            return $this->successResponse(
                message: 'Pelanggan berhasil dihapus'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghapus pelanggan',
                500
            );
        }
    }
}
