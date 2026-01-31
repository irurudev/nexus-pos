<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    protected function createAudit(Model $model, string $action)
    {
        // avoid auditing audit logs themselves
        if ($model instanceof AuditLog) {
            return;
        }

        $user = Auth::user();

        $old = null;
        $new = null;

        if ($action === 'created') {
            $new = $model->getAttributes();
        } elseif ($action === 'deleted') {
            $old = $model->getOriginal();
        } elseif ($action === 'updated') {
            $old = $model->getOriginal();
            $new = $model->getAttributes();
        }

        AuditLog::create([
            'user_id' => $user?->id ?? null,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => (string) ($model->getKey() ?? ''),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'url' => Request::fullUrl(),
        ]);
    }

    public function created(Model $model)
    {
        $this->createAudit($model, 'created');
    }

    public function updated(Model $model)
    {
        $this->createAudit($model, 'updated');
    }

    public function deleted(Model $model)
    {
        $this->createAudit($model, 'deleted');
    }
}
