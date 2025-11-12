<?php

namespace Modules\SharedRoles\Http\Controllers\Api;

use App\Http\Controllers\AppController;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;
use Illuminate\Http\Request;

class SharedPermissionController extends AppController
{
    private SharedPermissionRepository $sharedPermissionRepository;

    public function __construct(SharedPermissionRepository $sharedPermissionRepository)
    {
        $this->sharedPermissionRepository = $sharedPermissionRepository;
    }

    public function dataTable(Request $request)
    {
        // $this->allowedAction('getAdminSharedPermissions');

        $response = $this->sharedPermissionRepository->dataTable($request);

        return $response;
    }

    public function checkPermissionCode(Request $request)
    {
        // $this->allowedAction('getAdminSharedPermissions');

        $request->validate([
            "id" => "nullable",
            "code" => "required|string|max:100",
        ]);

        $response = $this->sharedPermissionRepository->checkPermissionCode($request);

        return $response;
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('deleteSharedPermissions');

        $response = $this->sharedPermissionRepository->destroy($id);

        return $response;
    }
}
