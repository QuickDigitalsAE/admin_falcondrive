<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function superAdminAuditMeta(object $record, $authUser): array
    {
        $createdByName = null;
        $updatedByName = null;
        $deletedByName = null;

        if (property_exists($record, 'created_by') || method_exists($record, 'createdByUser')) {
            $createdByName = method_exists($record, 'createdByUser')
                ? optional($record->createdByUser)->name
                : null;
        }

        if (property_exists($record, 'updated_by') || method_exists($record, 'updatedByUser')) {
            $updatedByName = method_exists($record, 'updatedByUser')
                ? optional($record->updatedByUser)->name
                : null;
        }

        if (property_exists($record, 'deleted_by') || method_exists($record, 'deletedByUser')) {
            $deletedByName = method_exists($record, 'deletedByUser')
                ? optional($record->deletedByUser)->name
                : null;
        }

        return [
            'show_super_admin_audit' => !is_null($createdByName) || !is_null($updatedByName) || !is_null($deletedByName),
            'created_by_name' => $createdByName,
            'updated_by_name' => $updatedByName,
            'deleted_by_name' => $deletedByName,
        ];
    }
}
