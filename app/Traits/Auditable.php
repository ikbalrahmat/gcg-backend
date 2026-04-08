<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    // Fungsi ini otomatis dipanggil Laravel pas ada perubahan di database
    public static function bootAuditable()
    {
        // Pas ada data BARU ditambah
        static::created(function ($model) {
            self::logAction('created', $model);
        });

        // Pas ada data DIUPDATE
        static::updated(function ($model) {
            self::logAction('updated', $model);
        });

        // Pas ada data DIHAPUS
        static::deleted(function ($model) {
            self::logAction('deleted', $model);
        });
    }

    protected static function logAction($action, $model)
    {
        // Jangan catat password yang di-hash ke log biar aman
        $hiddenFields = ['password', 'remember_token'];

        $oldValues = $action !== 'created' ? collect($model->getOriginal())->except($hiddenFields)->toArray() : null;
        $newValues = $action !== 'deleted' ? collect($model->getDirty())->except($hiddenFields)->toArray() : null;

        AuditLog::create([
            'user_id' => Auth::id(), // Tangkap ID user yang lagi login
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
