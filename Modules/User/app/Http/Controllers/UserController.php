<?php

namespace Modules\User\app\Http\Controllers;

use Modules\User\app\Http\Requests\UpdateUserRolesRequest;
use Modules\User\app\Http\Requests\UserRequest;
use Modules\User\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Permission\Repositories\RoleRepository;
use App\Http\Controllers\AppController;

class UserController extends AppController
{
    private $userRepository;
    private $roleRepository;

    public function __construct(UserRepository $repository, RoleRepository $roleRepository)
    {
        $this->userRepository = $repository;
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        // $this->allowedAction('viewUsers');
        Session::flash('page', 'users');

        return view('user::users.index');
    }

    public function create()
    {
        // $this->allowedAction('addUser');
        Session::flash('page', 'users');

        $roles = $this->roleRepository->all();

        return view('user::users.form', ["roles" => $roles]);
    }

    public function store(UserRequest $request)
    {
        // $this->allowedAction('addUser');

        $this->userRepository->store($request);

        return redirect()->route('admin.users.index');
    }

    public function edit(string $id)
    {
        // $this->allowedAction('editUser');
        Session::flash('page', 'users');

        $user = $this->userRepository->show($id);
        $roles = $this->roleRepository->all();
        $userRolesIds = $this->userRepository->getUserRolesIds($id);

        $data = [
            'user' => $user,
            "roles" => $roles,
            "userRolesIds" => $userRolesIds,
        ];

        return view('user::users.form', $data);
    }

    public function update(UserRequest $request, string $id)
    {
        // $this->allowedAction('editUser');

        $this->userRepository->update($request, $id);

        return redirect()->route('admin.users.index');
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('destroyUser');

        if ($this->userRepository->destroy($id) == 1)
            return response()->json(["success" => true, 'message' => "Utilizador apagada com sucesso!"]);
        else
            return response()->json(["error" => true, 'message' => "Erro ao apagar utilizador"]);
    }


    public function showManageForm(string $id)
    {
        // $this->allowedAction('manageRolePermissions');
        Session::flash('page', 'users');

        $userRolesIds = $this->userRepository->getUserRolesIds($id);

        $roles = $this->roleRepository->all();

        $data = [
            "userRolesIds" => $userRolesIds,
            "roles" => $roles,
            "userId" => $id
        ];

        return view('user::users.manage', $data);
    }

    public function manage(UpdateUserRolesRequest $request, string $id)
    {
        // $this->allowedAction('manageRolePermissions');

        $this->userRepository->manageRoles($request, $id);

        return redirect()->route('admin.users.index');
    }

    public function dataTable(Request $request)
    {
        // $this->allowedAction('viewRoles');

        $data = $this->userRepository->dataTable($request);

        return response()->json($data);
    }
}
