<?php

namespace Modules\Permission\Http\Controllers;

use Modules\Permission\Http\Requests\PermissionRequest;
use Modules\Permission\Repositories\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AppController;

class PermissionsController extends AppController
{
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function index()
    {
        // $this->allowedAction('viewPermissions');
        Session::flash('page', 'permissions');

        return view('permission::permissions.index');
    }

    public function create()
    {
        // $this->allowedAction('addPermission');
        Session::flash('page', 'permissions');

        return view('permission::permissions.form');
    }

    public function store(PermissionRequest $request)
    {
        // $this->allowedAction('addPermission');

        $this->permissionRepository->store($request);

        return redirect()->route('admin.permissions.index');
    }

    public function edit(string $id)
    {
        // $this->allowedAction('editPermission');
        Session::flash('page', 'permissions');

        $permission = $this->permissionRepository->show($id);

        return view('permission::permissions.form', ['permission' => $permission]);
    }

    public function update(PermissionRequest $request, string $id)
    {
        // $this->allowedAction('editPermission');

        $this->permissionRepository->update($request, $id);

        return redirect()->route('admin.permissions.index');
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('destroyPermission');

        if ($this->permissionRepository->destroy($id) == 1)
            return response()->json(["success" => true, 'message' => "Permissão apagada com sucesso!"]);
        else
            return response()->json(["error" => true, 'message' => "Erro ao apagar permissão"]);
    }
    public function dataTable(Request $request)
    {
        $data = $this->permissionRepository->dataTable($request);

        return response()->json($data);
    }
}
