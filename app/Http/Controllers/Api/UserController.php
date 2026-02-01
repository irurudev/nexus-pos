<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Users", description="Operasi CRUD untuk user/admin")
 *
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="username", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="role", type="string", example="kasir"),
 *   @OA\Property(property="is_active", type="boolean")
 * )
 *
 * @OA\Schema(
 *   schema="UserCreate",
 *   type="object",
 *   required={"name","username","email","password","role"},
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="username", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="password", type="string", format="password"),
 *   @OA\Property(property="role", type="string", example="kasir"),
 *   @OA\Property(property="is_active", type="boolean")
 * )
 *
 * @OA\Schema(
 *   schema="UserUpdate",
 *   type="object",
 *   required={"name","username","email","role"},
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="username", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="password", type="string", format="password", nullable=true),
 *   @OA\Property(property="role", type="string", example="kasir"),
 *   @OA\Property(property="is_active", type="boolean")
 * )
 */
class UserController extends BaseController
{
    /**
     * @OA\Get(
     *   path="/users",
     *   tags={"Users"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Pagination"))
     * )
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', User::class);

            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = User::query();

            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
            }

            $users = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($users, 'Data pengguna berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil pengguna', 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/users/{user}",
     *   tags={"Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(User $user)
    {
        try {
            $this->authorize('view', $user);
            return $this->successResponse($user, 'Data pengguna berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan', 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/users",
     *   tags={"Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserCreate")),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return $this->successResponse($user, 'Pengguna berhasil dibuat', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat pengguna', 500);
        }
    }

    /**
     * @OA\Put(
     *   path="/users/{user}",
     *   tags={"Users"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserUpdate")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            return $this->successResponse($user, 'Pengguna berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat memperbarui pengguna', 500);
        }
    }

    // Destroy/deletion of users is intentionally not implemented. Users cannot be deleted; they can only be activated/deactivated via the update endpoint.

}
