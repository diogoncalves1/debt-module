<?php

namespace Modules\Permission\Http\Controllers;

use Modules\Permission\Http\Requests\RoleRequest;
use Modules\Permission\Http\Requests\UpdateRolePermissionsRequest;
use Modules\Permission\Repositories\PermissionRepository;
use Modules\Permission\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AppController;

class RoleController extends AppController
{
    private $roleRepository;
    private $permissionRepository;

    public function __construct(RoleRepository $repository, PermissionRepository $permissionRepository)
    {
        $this->roleRepository = $repository;
        $this->permissionRepository = $permissionRepository;
    }

    public function index()
    {
        // $this->allowedAction('viewRoles');
        Session::flash('page', 'roles');

        $roles = $this->roleRepository->all();

        return view('permission::roles.index', ['roles' => $roles]);
    }

    public function create()
    {
        // $this->allowedAction('addRole');
        Session::flash('page', 'roles');

        return view('permission::roles.form');
    }

    public function store(RoleRequest $request)
    {
        // $this->allowedAction('addRole');

        $this->roleRepository->store($request);

        return redirect()->route('admin.roles.index');
    }

    public function edit(string $id)
    {
        // $this->allowedAction('editRole');
        Session::flash('page', 'roles');

        $role = $this->roleRepository->show($id);

        return view('permission::roles.form', ["role" => $role]);
    }

    public function update(RoleRequest $request, string $id)
    {
        // $this->allowedAction('editRole');

        $this->roleRepository->update($request, $id);

        return redirect()->route('admin.roles.index');
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('destroyRole');

        return $this->roleRepository->destroy($id);
    }

    public function showManageForm(string $id)
    {
        // $this->allowedAction('manageRolePermissions');

        Session::flash('page', 'roles');

        $rolePermissionsIds = $this->roleRepository->getRolePermissionsIds($id);

        $permissionsGrouped = $this->permissionRepository->getPermissionsGrouped();

        $data = [
            "rolePermissionsIds" => $rolePermissionsIds,
            "permissionsGrouped" => $permissionsGrouped,
            "roleId" => $id
        ];

        return view('permission::roles.manage', $data);
    }

    public function manage(UpdateRolePermissionsRequest $request, string $id)
    {
        // $this->allowedAction('manageRolePermissions');

        $this->roleRepository->managePermissions($request, $id);

        return redirect()->route('admin.roles.index');
    }

    public function dataTable(Request $request)
    {
        // $this->allowedAction('viewRoles');

        $data = $this->roleRepository->dataTable($request);

        return response()->json($data);
    }
}
