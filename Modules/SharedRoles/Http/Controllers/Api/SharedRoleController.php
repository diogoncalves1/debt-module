<?php

namespace Modules\SharedRoles\Http\Controllers\Api;

use App\Http\Controllers\AppController;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Illuminate\Http\Request;

class SharedRoleController extends AppController
{
    protected SharedRoleRepository $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function dataTable(Request $request)
    {
        // $this->allowedAction('getSharedRoles');

        $response = $this->sharedRoleRepository->dataTable($request);

        return $response;
    }

    public function checkRoleCode(Request $request)
    {
        // $this->allowedAction('getSharedRoles');

        $request->validate([
            "id" => "nullable",
            "code" => "required|string|max:255",
        ]);

        $response = $this->sharedRoleRepository->checkCode($request);

        return $response;
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('destroySharedRoles');

        $response = $this->sharedRoleRepository->destroy($id);

        return $response;
    }
}
