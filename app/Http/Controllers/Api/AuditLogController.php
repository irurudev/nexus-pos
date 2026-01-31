<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    public function index(Request $request)
    {
        try {
            // Only admin can view audit logs
            $this->authorize('viewAny', AuditLog::class);

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
