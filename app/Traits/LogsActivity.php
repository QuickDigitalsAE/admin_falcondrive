<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\UserActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            if (self::shouldLog($model)) {
                UserActivityLog::create([
                    'user_id'    => Auth::id(),
                    'model_type' => get_class($model),
                    'table_name' => $model->getTable(),
                    'action'     => 'created',
                    'changes'    => $model->getAttributes(),
                ]);
            }
        });

        static::updating(function ($model) {
            if (!self::shouldLog($model)) return;

            $changed   = $model->getDirty();
            $original  = $model->getOriginal();
            $tableName = $model->getTable();

            $changes = ['id' => $original['id'] ?? null];

            if ($tableName === 'users') {
                $changes['name']  = $original['name'] ?? null;
                $changes['email'] = $original['email'] ?? null;
            }

            foreach ($changed as $key => $newValue) {
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue,
                ];
            }

            if (!empty($changes)) {
                UserActivityLog::create([
                    'user_id'    => Auth::id(),
                    'model_type' => get_class($model),
                    'table_name' => $tableName,
                    'action'     => 'updated',
                    'changes'    => $changes,
                ]);
            }
        });

        static::deleted(function ($model) {
            if (self::shouldLog($model)) {
                UserActivityLog::create([
                    'user_id'    => Auth::id(),
                    'model_type' => get_class($model),
                    'table_name' => $model->getTable(),
                    'action'     => 'deleted',
                    'changes'    => $model->getOriginal(),
                ]);
            }
        });

        static::restored(function ($model) {
            if (self::shouldLog($model)) {
                UserActivityLog::create([
                    'user_id'    => Auth::id(),
                    'model_type' => get_class($model),
                    'table_name' => $model->getTable(),
                    'action'     => 'restored',
                    'changes'    => $model->getOriginal(),
                ]);
            }
        });
    }

    protected static function shouldLog($model): bool
    {
        // Don't log if user is not authenticated
        if (!Auth::check()) return false;

        $routeName = optional(Route::current())->getName();
        $routeUri  = optional(Route::current())->uri();

        // Skip user registration
        if ($model->getTable() === 'users' &&
            ($routeName === 'api.register' || str_contains($routeUri, 'register'))) {
            return false;
        }

        return true;
    }
}
