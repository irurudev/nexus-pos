<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    /**
     * @OA\Get(
     *   path="/audit-logs",
     *   operationId="getAuditLogs",
     *   tags={"AuditLogs"},
     *   summary="Dapatkan audit logs (admin only)",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=20)),
     *   @OA\Parameter(name="user_id", in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Parameter(name="auditable_type", in="query", @OA\Schema(type="string", example="App\\Models\\Barang")),
     *
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Pagination"))
     * )
     */
    public function index(Request $request)
    {
        // Only admin can view audit logs - let AuthorizationException bubble up to return 403
        $this->authorize('viewAny', AuditLog::class);

        try {
            $perPage = $request->get('per_page', 20);
            $query = AuditLog::query()->with('user');

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->filled('auditable_type')) {
                $query->where('auditable_type', $request->get('auditable_type'));
            }

            $logs = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($logs, 'Audit logs retrieved');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving audit logs', 500);
        }
    }
}
