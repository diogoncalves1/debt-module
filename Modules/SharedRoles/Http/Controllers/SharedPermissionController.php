<?php

namespace Modules\SharedRoles\Http\Controllers;

use Modules\SharedRoles\Enums\Language;
use App\Http\Controllers\AppController;
use Modules\SharedRoles\Http\Requests\SharedPermissionRequest;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;
use Illuminate\Support\Facades\Session;

class SharedPermissionController extends AppController
{
    private SharedPermissionRepository $sharedPermissionRepository;

    public function __construct(SharedPermissionRepository $sharedPermissionRepository)
    {
        $this->sharedPermissionRepository = $sharedPermissionRepository;
    }

    public function index()
    {
        // $this->allowedAction('viewAdminSharedPermissions');

        Session::flash('page', 'shared permissions');

        return view("sharedroles::shared-permissions.index");
    }

    public function create()
    {
        // $this->allowedAction('addSharedPermissions');

        Session::flash('page', 'shared permissions');

        return view("sharedroles::shared-permissions.form");
    }

    public function store(SharedPermissionRequest $request)
    {
        // $this->allowedAction('addSharedPermissions');

        $this->sharedPermissionRepository->store($request);

        return redirect()->route('admin.shared-permissions.index');
    }

    public function edit(string $id)
    {
        // $this->allowedAction('editSharedPermissions');

        Session::flash('page', 'shared permissions');

        $sharedPermission = $this->sharedPermissionRepository->show($id);

        $languages = Language::cases();

        return view("sharedroles::shared-permissions.form", compact('languages', 'sharedPermission'));
    }

    public function update(SharedPermissionRequest $request, string $id)
    {
        // $this->allowedAction('editSharedPermissions');

        $this->sharedPermissionRepository->update($request, $id);

        return redirect()->route('admin.shared-permissions.index');
    }
}
