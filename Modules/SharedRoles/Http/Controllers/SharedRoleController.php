<?php

namespace Modules\SharedRoles\Http\Controllers;

use Modules\SharedRoles\Enums\Language;
use App\Http\Controllers\AppController;
use Modules\SharedRoles\Http\Requests\SharedRoleRequest;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Illuminate\Support\Facades\Session;

class SharedRoleController extends AppController
{
    protected SharedRoleRepository $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function index()
    {
        // $this->allowedAction('viewSharedRoles');

        Session::flash('page', "shared roles");

        return view("sharedroles::shared-roles.index");
    }

    public function create()
    {
        // $this->allowedAction('addSharedRoles');

        Session::flash('page', "shared roles");

        $languages = Language::cases();

        return view("sharedroles::shared-roles.form", compact('languages'));
    }

    public function store(SharedRoleRequest $request)
    {
        // $this->allowedAction('addSharedRoles');

        $this->sharedRoleRepository->store($request);

        return redirect()->route('admin.shared-roles.index');
    }

    public function edit(string $id)
    {
        // $this->allowedAction('editSharedRoles');

        Session::flash('page', "shared roles");

        $languages = Language::cases();
        $sharedRole = $this->sharedRoleRepository->show($id);

        return view("sharedroles::shared-roles.form", compact('languages', 'sharedRole'));
    }

    public function update(SharedRoleRequest $request, string $id)
    {
        // $this->allowedAction('updateSharedRoles');

        $this->sharedRoleRepository->update($request, $id);

        return redirect()->route('admin.shared-roles.index');
    }
}
