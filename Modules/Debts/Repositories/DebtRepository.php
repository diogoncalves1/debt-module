<?php

namespace Modules\Debts\Repositories;

use Modules\Debts\Entities\Debt;
use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class DebtRepository implements RepositoryApiInterface
{
    private $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function all()
    {
        return Debt::all();
    }

    public function list(Request $request)
    {
        $user = $request->user();

        App::setLocale($user->preferences->lang ?? 'en');

        $query =  Debt::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere("type", 'like', "%{$search}%")
                    ->orWhere("balance", 'like', "%{$search}%");
            });
        }

        if ($request->get('status')) {
            $active = $request->get('status') == 'active' ? 1 : 0;
            $query->active($active);
        }
        if ($request->get('type')) {
            $query->type($request->get('type'));
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }


        $debts = $query->offset($request->start)
            ->limit($request->length)
            ->whereHas("users", function ($query) use ($user) {
                $query->where('user_id', 2/*$user->id */);
                $query->where('status', 'accepted');
            })
            ->distinct()
            ->get();

        $total = $query->count();
        foreach ($debts as &$debt) {
            // $debt->icon = Helpers::getAccountIcon($debt->type);
            $debt->typeTranslated = __("frontend." . $debt->type);
            $debt->user = $debt->users->map(function ($user) {
                return $user->name;
            });
            $debt->currencySymbol = $debt->currency->symbol;

            $debt->statusTranslated = $debt->active ? __('portal.active') : __('portal.inactive');

            $sharedRole = $this->userSharedRole($debt, 2);

            // $debt->balaceFormatted = Helpers::formatMoneyWithSymbol($debt->balance);

            $btnGroup = '<div class="d-flex justify-content-center gap-1">';
            if ($sharedRole->hasPermission("viewAccountDetails"))
                $btnGroup .= '<a href="accounts/' . $debt->id . '" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>';
            if ($sharedRole->hasPermission("editAccount"))
                $btnGroup .= '<a href="accounts/' .  $debt->id . '/edit" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>';
            if ($sharedRole->hasPermission("deleteAccount"))
                $btnGroup .= "<button type='button' onclick='modalDelete({$debt->id})' 
                                data-table-delete-row 
                                 class='btn btn-light btn-icon btn-sm rounded-circle'>
                                <i class='ti ti-trash fs-lg'></i>
                            </button>";
            $btnGroup .= "</div>";
            $debt->input = '<input data-id="' . $debt->id . '" class="form-check-input form-check-input-light fs-14 file-item-check mt-0" type="checkbox">';
            $debt->actions = $btnGroup;
        }

        return [
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $debts
        ];
    }

    public function showToUser(Request $request, string $id)
    {
        try {
            $debt = $this->show($id);

            $user = $request->user();

            $sharedRole = $this->userSharedRole($debt, 2 /*$user->id*/);

            if (!$sharedRole->hasPermission('getDebt')) throw new \Exception();

            return $debt;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $input = $request->only(['name', 'total_amount', 'currency_id', 'start_date', 'due_date']);

                $user = $request->user();

                $debt = Debt::create($input);

                Log::info("Debt " . $debt->id . " created successfully");
                return $debt;
            });
        } catch (\Exception $e) {
            Log::error($e);
            return null;
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $input = $request->only(['name', 'total_amount', 'paid_amount', 'start_date', 'due_date']);

                $user = $request->user();

                // TODO: Exceptions

                $debt = $this->show($id);

                return response()->json(["success" => true, "message" => __('alerts.debtUpdated'), "data" => $debt]);
            });
        } catch (\Exception $e) {
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $user = $request->user();

                $debt = $this->show($id);

                // TODO: Exceptions

                $debt->destroy();

                Log::info('Debt ' . $debt->id . " destroyed successfully");
                return response()->json(["success" => true, "message" => __('alerts.debtDestroyed')]);
            });
        } catch (\Exception $e) {
        }
    }

    public function show(string $id)
    {
        return Debt::findOrFail($id);
    }


    // Private Methods

    public function userSharedRole($debt, $userId)
    {
        $user = $debt->users()
            ->where('user_id', $userId)
            ->where('status', 'accepted')
            ->join('shared_roles', 'debts_user.shared_role_id', '=', 'shared_roles.id')
            ->first();

        if ($user)
            return $this->sharedRoleRepository->show($user?->pivot->shared_role_id);
        return null;
    }
}
