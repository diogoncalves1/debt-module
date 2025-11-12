<?php

namespace Modules\Permission\Repositories;

use Modules\Permission\Entities\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Repositories\RepositoryInterface;

class RoleRepository implements RepositoryInterface
{
    public function all()
    {
        return Role::where('name', '!=', 'superAdmin')->get();
    }

    public function store(Request $request)
    {
        try {
            $input = $request->all();

            DB::transaction(function () use ($input) {
                $role = Role::create($input);

                Log::info('Role ' . $role->id . ' added.');
                Session::flash('success', 'Perfil adicionado com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar adicionar perfil.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $input = $request->all();
            $role = $this->show($id);

            DB::transaction(function () use ($input, $role) {
                $role->update($input);

                Log::info('Role ' . $role->id . ' updated');
                Session::flash('success', 'Perfil atualizado com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar perfil.');
        }
    }

    public function destroy(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $role = $this->show($id);
                if ($role->users->count() > 0) {
                    return response()->json(["error" => true, 'message' => "Existem Utilizadores com esse perfil, verifique de alterar o perfil desses utilizadores."], 403);
                }

                $role->delete();

                Log::info('Role ' . $role->id . ' deleted');
                return response()->json(["error" => true, 'message' => "Perfil apagado com sucesso!"]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(["error" => true, 'message' => "Erro ao tentar apagar esse perfil."], 500);
        }
    }

    public function show(string $id)
    {
        return Role::find($id);
    }

    public function dataTable(Request $request)
    {
        $query = Role::query();
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where("name", 'like', "{$search}%")
                    ->orWhere("code", 'like', "{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $roles = $query->offset($request->start)
            ->limit($request->length)
            ->select("name", 'id', 'code',)
            ->get();

        foreach ($roles as &$role) {
            $role->actions = "<div class='btn-group'>
                            <a type='button' href='" . route('admin.roles.manage', $role->id) . "' class='btn mr-1 btn-default'>
                                <i class='fas fa-cogs'></i>
                            </a>
                            <a type='button' href='" . route('admin.roles.edit', $role->id) . "' class='btn mr-1 btn-default'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button type='button' onclick='modalDelete({$role->id})' class='btn btn-default'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>";
        }

        $data = [
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $roles
        ];

        return $data;
    }

    public function getRolePermissionsIds(string $id)
    {
        try {
            $role = $this->show($id);

            $rolePermissionsIds = $role->permissions->pluck('id')->toArray();

            return $rolePermissionsIds;
        } catch (\Exception $e) {
            Log::error($e);

            return [];
        }
    }

    public function managePermissions(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $role = $this->show($id);

                $role->permissions()->sync($request->input('permissions', []));

                Log::info('Role Permissions Updated');
                Session::flash('success', 'Permissões de papel atualizadas com sucesso');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar as permissões do perfil');
        }
    }
}