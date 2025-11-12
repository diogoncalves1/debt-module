<?php

namespace Modules\SharedRoles\Repositories;

use Modules\SharedRoles\Entities\SharedRole;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SharedRoleRepository implements RepositoryInterface
{
    public function all()
    {
        return SharedRole::all();
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $input = $request->only('code');

                $input["name"] = json_encode($request->get('name'));

                $sharedRole = SharedRole::create($input);

                Log::info('Shared Role ' . $sharedRole->id . ' successfully created.');
                Session::flash('success', __('alerts.sharedRoleAdded'));
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __('alerts.errorAddSharedRole'));
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedRole = $this->show($id);

                $input = $request->only('code');

                $input["name"] = json_encode($request->get('name'));

                $sharedRole->update($input);

                Log::info('Shared Role ' . $sharedRole->id . ' successfully updated.');
                Session::flash('success', __('alerts.sharedRoleUpdated'));
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __('alerts.errorUpdateSharedRole'));
        }
    }

    public function destroy(string $id)
    {
        try {
            return  DB::transaction(function () use ($id) {
                $sharedRole = $this->show($id);

                if ($sharedRole)
                    $sharedRole->delete();

                Log::info('Shared Role ' . $sharedRole->id . ' successfully deleted.');
                return response()->json(['success' => true, 'message' => __('alerts.sharedRoleDeleted')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => true, 'message' =>  __('alerts.errorDeleteSharedRole')]);
        }
    }

    public function show(string $id)
    {
        return SharedRole::find($id);
    }

    public function dataTable(Request $request)
    {
        $query = SharedRole::query();

        $userLang = /* $_COOKIE["lang"] */ 'en';

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search, $userLang) {
                $q->where("name->{$userLang}", 'like', "%{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            if ($orderColumn == 'name')
                $query->orderBy($orderColumn . "->{$userLang}", $orderDir);
            else
                $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $sharedRoles = $query->offset($request->start)
            ->limit($request->length)
            ->select("name->{$userLang} as name", 'code', 'id')
            ->get();

        foreach ($sharedRoles as &$sharedRole) {

            $btnGroup = "<div class='btn-group'>";

            // if (auth()->user()->hasPermission('managePermissionsSharedRoles'))
            $btnGroup .= "<a href='" . route('admin.shared-roles.manage', $sharedRole->id) . "' class='btn btn-default'>
                                <i class='fas fa-cogs'></i>
                            </a>";
            // if (auth()->user()->hasPermission('editSharedRoles'))
            $btnGroup .= "<a href='" . route('admin.shared-roles.edit', $sharedRole->id) . "' class='btn btn-default'>
                                <i class='fas fa-edit'></i>
                            </a>";
            // if (auth()->user()->hasPermission('deleteSharedRoles'))
            $btnGroup .= "<button type='button' onclick='modalDelete(`" . route('api.shared-roles.destroy', $sharedRole->id) . "`)' class='btn btn-default'>
                                <i class='fas fa-trash'></i>
                            </button>";

            $btnGroup .= "</div>";

            $sharedRole->actions = $btnGroup;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $sharedRoles
        ]);
    }

    public function updateRolePermissions(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedRole = $this->show($id);

                $sharedRole->permissions()->sync($request->input('permissions', []));

                Log::info('Shared role permissions ' . $sharedRole->id . ' successfully updated.');
                Session::flash('success', "Permissões de partilha do papel de partilha atualizadas com sucesso!");
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __("Error ao tentar atualizar o permissões de partilha do papel de partilha."));
        }
    }

    public function checkCode(Request $request)
    {
        $query = SharedRole::where('code', $request->get('code'));

        if ($request->get("id"))
            $query->where('id', '!=', $request->get('id'));

        $exists =  $query->exists();

        return response()->json(['success' => true, 'exists' => $exists]);
    }
}
