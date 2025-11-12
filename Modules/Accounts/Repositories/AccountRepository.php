<?php

namespace Modules\Accounts\Repositories;

use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Exceptions\UnauthorizedDeletedAccountException;
use Modules\Accounts\Exceptions\UnauthorizedUpdateAccountException;
use Modules\Accounts\Exceptions\UnauthorizedViewAccount;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\AccountUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use App\Repositories\RepositoryApiInterface;

class AccountRepository implements RepositoryApiInterface
{
    private $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function all()
    {
        return Account::all();
    }

    public function allUser()
    {
        $userId = Auth::id();

        return Account::whereHas("accounts_user", function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = $request->user();
                $input = $request->only('name', 'type', 'currency_id', 'active');

                $account = Account::create($input);

                $sharedRole = SharedRole::where("code", "creator")->first();

                $accountUserInput = [
                    "user_id" => 2,
                    "account_id" => $account->id,
                    "shared_role_id" => $sharedRole->id,
                    "status" => "accepted",
                    "accepted_at" => Carbon::now()
                ];

                AccountUser::create($accountUserInput);

                Log::info('Account ' . $account->id . ' successfully added.');
                return response()->json(['success' => true, 'message' => __('alerts.accountAdded'), "data" => $account]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorAddAccount')], 500);
        }
    }

    public function showToUser(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $account = $this->show($id);

            $sharedRole = $this->userSharedRole($account, $user->id);
            $sharedRole->permissions;

            if (!$sharedRole || !$sharedRole->hasPermission("viewAccountDetails")) throw new UnauthorizedViewAccount();

            $userLang = $user->preferences->getLang() ?? "en";

            foreach ($account->users as &$userAccount) {
                $userAccount->sharedRole = $this->userSharedRole($account, $userAccount->id);
                $userAccount->sharedRoleName = json_decode($userAccount->sharedRole->name)->$userLang;
            }

            $account->totalTransactions = $account->transactions()->status("completed")->count();
            $account->totalUsers =  $account->users()->count();
            $account->icon = Helpers::getAccountIcon($account->type);
            $account->type = __("frontend." . $account->type);
            $account->currencyInfo = json_decode($account->currency->info)->{$userLang};
            $account->createdAtFormated = Carbon::parse($account->created_at)->format("Y-m-d");
            $account->creatorAccount = $account->creator();

            $data = [
                "account" => $account,
                "userRole" => $sharedRole
            ];

            return response()->json(["success" => true, "data" => $data]);
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorGetAccount')], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->show($id);

                $user = $request->user();

                $sharedRole = $this->userSharedRole($account, 1);

                if (!$sharedRole || !$sharedRole->hasPermission("updateAccount"))
                    throw new UnauthorizedUpdateAccountException();

                $input = $request->only('name', 'type', 'currency_id', 'active');

                $account->update($input);

                Log::info('Account ' . $account->id . ' successfully updated.');
                return response()->json(['success' => true, 'message' => __('alerts.accountUpdated'), "data" => $account]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorUpdateAccount')], 500);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->show($id);

                $user = $request->user();

                $sharedRole = $this->userSharedRole($account, 2);

                if (!$sharedRole ||  !$sharedRole->hasPermission("deleteAccount"))
                    throw new UnauthorizedDeletedAccountException();

                $account->delete();

                Log::info('Account ' . $account . ' successfully destroyed');
                return response()->json(["success" => true, "message" => __('alerts.accountDeleted')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(["error" => true, "message" => __('alerts.errorDeleteAccount')], 500);
        }
    }

    public function show(string $id)
    {
        $account = Account::findOrFail($id);

        return $account;
    }

    public function dataTable(Request $request)
    {

        $user = $request->user();

        App::setLocale($user->preferences->lang ?? 'en');

        $query =  Account::query();

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


        $accounts = $query->offset($request->start)
            ->limit($request->length)
            ->whereHas("users", function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->distinct()
            ->get();

        $total = $query->count();
        foreach ($accounts as &$account) {
            $account->icon = Helpers::getAccountIcon($account->type);
            $account->typeTranslated = __("frontend." . $account->type);
            $account->user = $account->users->map(function ($user) {
                return $user->name;
            });
            $account->currencySymbol = $account->currency->symbol;

            $account->statusTranslated = $account->active ? __('portal.active') : __('portal.inactive');

            $sharedRole = $this->userSharedRole($account, $user->id);

            $account->balaceFormatted = Helpers::formatMoneyWithSymbol($account->balance);

            $btnGroup = '<div class="d-flex justify-content-center gap-1">';
            if ($sharedRole->hasPermission("viewAccountDetails"))
                $btnGroup .= '<a href="accounts/' . $account->id . '" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>';
            if ($sharedRole->hasPermission("editAccount"))
                $btnGroup .= '<a href="accounts/' .  $account->id . '/edit" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>';
            if ($sharedRole->hasPermission("deleteAccount"))
                $btnGroup .= "<button type='button' onclick='modalDelete({$account->id})' 
                                data-table-delete-row 
                                 class='btn btn-light btn-icon btn-sm rounded-circle'>
                                <i class='ti ti-trash fs-lg'></i>
                            </button>";
            $btnGroup .= "</div>";
            $account->input = '<input data-id="' . $account->id . '" class="form-check-input form-check-input-light fs-14 file-item-check mt-0" type="checkbox">';
            $account->actions = $btnGroup;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $accounts
        ]);
    }

    // Private Methods

    public function userSharedRole($account, $userId)
    {
        $user = $account->users()
            ->where('user_id', $userId)
            ->where('status', 'accepted')
            ->join('shared_roles', 'accounts_user.shared_role_id', '=', 'shared_roles.id')
            ->first();

        if ($user)
            return $this->sharedRoleRepository->show($user?->pivot->shared_role_id);
        return null;
    }
}
