<?php

namespace Modules\SharedRoles\Repositories;

use Modules\SharedRoles\Entities\SharedPermission;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SharedPermissionRepository implements RepositoryInterface
{

    public function all()
    {
        return SharedPermission::all();
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $input = $request->only(['code', 'category', 'name']);

                $sharedPermission = SharedPermission::create($input);

                Log::info('Shared permission ' . $sharedPermission->id . ' successfully created.');
                Session::flash('success', __('alerts.sharedPermissionAdded'));
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __('alerts.errorAddSharedPermission'));
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedPermission = $this->show($id);

                $input = $request->only(['code', 'category', 'name']);

                $sharedPermission->update($input);

                Log::info('Shared permission ' . $sharedPermission->id . ' successfully updated.');
                Session::flash('success', __('alerts.sharedPermissionUpdated'));
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __('alerts.errorUpdateSharedPermission'));
        }
    }

    public function destroy(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $sharedPermission = $this->show($id);

                $sharedPermission->delete();

                Log::info('Shared permission ' . $sharedPermission->id . ' successfully deleted.');
                return response()->json(['success' => true, 'message' => __('alerts.sharedPermissionDeleted')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => true, 'message' =>  __('alerts.errorDeleteSharedPermission')]);
        }
    }

    public function show(string $id)
    {
        return SharedPermission::find($id);
    }

    public function dataTable(Request $request)
    {
        $query = SharedPermission::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where("name", 'like', "{$search}%")
                    ->orWhere("code", 'like', "{$search}%")
                    ->orWhere("category", 'like', "{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $sharedPermissions = $query->offset($request->start)
            ->limit($request->length)
            ->select("name", "category", 'code', 'id')
            ->get();

        foreach ($sharedPermissions as  &$sharedPermission) {
            $btnGroup = "<div class='btn-group'>";

            // if (auth()->user()->hasPermission('editSharedPermissions')) 
            $btnGroup .=  "<a href='" . route('admin.shared-permissions.edit', $sharedPermission->id) . "'  class='btn btn-default'>
                                <i class='fas fa-edit'></i>
                            </a>";
            // if (auth()->user()->hasPermission('deleteSharedPermissions')) 
            $btnGroup .= "<button type='button' onclick='modalDelete(`" . route('api.shared-permissions.destroy', $sharedPermission->id) . "`)' class='btn btn-default'>
                                <i class='fas fa-trash'></i>
                            </button>";

            $btnGroup .= "</div>";

            $sharedPermission->actions = $btnGroup;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $sharedPermissions
        ]);
    }

    public function checkPermissionCode(Request $request)
    {
        $query = SharedPermission::where('code', $request->get('code'));

        if ($request->get("id"))
            $query->where('id', '!=', $request->get('id'));

        $exists =  $query->exists();

        return response()->json(['success' => true, 'exists' => $exists]);
    }
}
