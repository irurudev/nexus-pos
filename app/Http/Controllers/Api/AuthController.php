<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class AuthController extends BaseController
{
    /**
     * @OA\Tag(
     *   name="Auth",
     *   description="Endpoint autentikasi"
     * )
     */

    /**
     * @OA\Post(
     *     path="/login",
     *     operationId="login",
     *     tags={"Auth"},
     *     summary="Login dengan email dan password",
     *     description="Endpoint untuk login dan mendapatkan Bearer token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Admin POS"),
     *                     @OA\Property(property="email", type="string", example="admin@example.com"),
     *                     @OA\Property(property="role", type="string", enum={"admin","kasir"}, example="admin")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|xxxxxxxxxxxxxxxxxxxxxxxx")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Email atau password salah"),
     *     @OA\Response(response=403, description="Akun belum aktif atau dinonaktifkan"),
     *     @OA\Response(response=500, description="Kesalahan server")
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            Log::debug('Login attempt', ['email' => $request->email]);

            // Find user by email first
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                Log::debug('User not found', ['email' => $request->email]);

                return $this->errorResponse(
                    'Email atau password salah',
                    401
                );
            }

            // If user found but inactive, reject with clear error
            if (! $user->is_active) {
                Log::debug('User found but inactive', ['email' => $request->email]);

                return $this->errorResponse(
                    'Akun belum aktif atau dinonaktifkan',
                    403
                );
            }

            $passwordMatch = Hash::check($request->password, $user->password);
            Log::debug('Password check', ['match' => $passwordMatch]);

            if (! $passwordMatch) {
                return $this->errorResponse(
                    'Email atau password salah',
                    401
                );
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
            ], 'Login berhasil', 200);
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return $this->errorResponse(
                'Terjadi kesalahan saat login: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     operationId="logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Logout berhasil"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout()
    {
        try {
            // Ensure we have an authenticated user and safely delete the current token
            $user = Auth::user();
            if (! $user) {
                return $this->errorResponse('Unauthorized', 401);
            }

            /** @var \App\Models\User $user */
            /** @var \Laravel\Sanctum\PersonalAccessToken|null $currentToken */
            $currentToken = $user->currentAccessToken();
            $currentToken?->delete();

            return $this->successResponse(
                message: 'Logout berhasil'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat logout',
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/me",
     *     operationId="getCurrentUser",
     *     tags={"Auth"},
     *     summary="Dapatkan data user yang sedang login",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data user berhasil diambil",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me()
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return $this->errorResponse('Unauthorized', 401);
            }

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ], 'Data user berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan',
                500
            );
        }
    }
}
