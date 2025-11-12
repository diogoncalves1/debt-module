<?php

namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Exceptions\InvalidTransactionDateException;
use Modules\Accounts\Exceptions\UnauthorizedCreateTransactionException;
use Modules\Accounts\Exceptions\UnauthorizedDeletedTransactionException;
use Modules\Accounts\Exceptions\UnauthorizedUpdateTransactionException;
use Modules\Accounts\Entities\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository implements RepositoryApiInterface
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function all()
    {
        return Transaction::all();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $input = $request->only(['account_id', 'type', 'amount', 'date', 'description', 'status', 'category_id']);

                $account = $this->accountRepository->show($request->get('account_id'));

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($account, $user->id);

                if ($request->get("date") > Carbon::now() && $request->get("status") == "completed") throw new \Exception("Impossível adicionar uma transação com a data maior que a atual sem estar agendada", 500);
                if (!$sharedRole || !$sharedRole->hasPermission('addTransactions')) throw new UnauthorizedCreateTransactionException();

                $input["user_id"] = $user->id;

                $transaction = Transaction::create($input);

                if ($transaction->status == "completed" && $transaction->account)
                    $this->adjustAccountBalance($transaction);

                Log::info('Transaction ' . $transaction->id . ' successfully created.');
                return response()->json(['success' => true, 'message' => __('alerts.transactionStored'), "data" => $transaction]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorAddTransaction')], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $input = $request->only(['amount', 'date', 'description', 'category_id']);

                $transaction = $this->show($id);

                $user = $request->user();
                $sharedRole = $this->accountRepository->userSharedRole($transaction->account, $user->id);

                if ($request->get("date") > Carbon::now() && $request->get("status") == "completed") throw new InvalidTransactionDateException();
                if (!$sharedRole->hasPermission("editTransactions")) throw new UnauthorizedUpdateTransactionException();

                if ($transaction->status == "completed") {
                    $difference = $transaction->amount - $request->get("amount");
                    $this->updateAccountBalance($transaction, $difference);
                }

                $transaction->update($input);

                Log::info("Transactions " . $transaction->id . " updated successfully");
                return response()->json(["success" => true, "data" => $transaction, "difference" => $difference, "message" => __("alerts.transactionUpdated")]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorUpdateTransaction')], 500);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $transaction = $this->show($id);

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($transaction->account, $user->id);

                if (!$sharedRole->hasPermission("deleteTransactions")) throw new UnauthorizedDeletedTransactionException();

                if ($transaction->status == "completed" && $transaction->account)
                    $this->reverseAccountBalance($transaction);

                $transaction->delete();

                Log::info("Transaction " . $transaction->id . " successfully destroyed.");
                return response()->json(["success" => true, "message" => __("alerts.transactionDestroyed")]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' => __('alerts.errorDestroyTransaction')], 500);
        }
    }

    public function show(string $id)
    {
        return Transaction::findOrFail($id);
    }

    public function dataTable(Request $request)
    {
        $user = $request->user();
        App::setLocale($user->preferences->lang ?? 'en');

        $query =  Transaction::join("accounts", "accounts.id", "=", "transactions.account_id")
            ->join("accounts_user", "accounts.id", '=', 'accounts_user.account_id')
            ->leftJoin("users", "users.id", '=', 'transactions.user_id')
            ->where("accounts_user.user_id", $user->id);

        if ($request->get("status"))
            $query->where("transactions.status", $request->get("status"));
        if ($request->get("type"))
            $query->where("transactions.type", $request->get("type"));
        if ($request->get("account_id"))
            $query->account($request->get("account_id"));
        if ($request->get("user"))
            $query->user($request->get("user"))
                ->where("accounts_user.user_id", $user->id);

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('transactions.date', 'like', "%{$search}%")
                    ->orWhere("accounts.name", 'like', "%{$search}%")
                    ->orWhere("users.name", 'like', "%{$search}%")
                    ->orWhere("transactions.amount", 'like', "%{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $transactions = $query->offset($request->start)
            ->limit($request->length)
            ->select("users.name as user", "accounts.id", "transactions.type", "transactions.*")
            ->orderBy("transactions.id", "desc")
            ->get();

        foreach ($transactions as &$transaction) {
            $sharedRole = $this->accountRepository->userSharedRole($transaction->account, $user->id);
            $transaction->statusClass = Helpers::getClassByStatus($transaction->status);
            $transaction->statusTranslate = "{$transaction->status}Transaction";
            $amountFormated = Helpers::formatMoneyWithSymbol($transaction->amount);
            $amountFormated["value"] = ($transaction->type == "expense") ? '-' . $amountFormated['value'] : '+' . $amountFormated['value'];
            $transaction->account->currency;

            $transaction->amountFormated = $amountFormated;

            $btnGroup = "<div class='btn-group'>";

            if ($transaction->status == 'pending' && $transaction->date <= date('Y-m-d'))
                if ($sharedRole->hasPermission("confirmScheduledTransaction"))
                    $btnGroup .= "<button type='button' onclick='confirmTransaction({$transaction->id})' data-toggle='tooltip' data-placement='top'
                                 title='Confirmar Transação' class='btn mr-1 btn-default'>
                                <i class='fas fa-check'></i>
                            </button>";
            if ($sharedRole->hasPermission("viewTransactionsDetails"))
                $btnGroup .= '<a href="' . "transactions/" . $transaction->id . '" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>';
            if ($sharedRole->hasPermission("editTransactions"))
                $btnGroup .= '<a href="' . "transactions/" . $transaction->id . '/edit" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>';
            if ($sharedRole->hasPermission("deleteTransactions"))
                $btnGroup .= "<button type='button' onclick='modalDelete({$transaction->id})'  data-table-delete-row 
                                 class='btn btn-light btn-icon btn-sm rounded-circle'>
                                <i class='ti ti-trash fs-lg'></i>
                            </button>";
            $btnGroup .= "</div>";
            $transaction->actions = $btnGroup;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            "app" => app()->getLocale(),
            'recordsFiltered' => $total,
            'data' => $transactions
        ]);
    }

    // Private methods
    private function adjustAccountBalance(Transaction $transaction): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type === "revenue" ? $transaction->amount : -$transaction->amount;

        $account->save();
    }
    private function updateAccountBalance(Transaction $transaction, float $difference): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type == "revenue" ? -$difference : $difference;

        $account->save();
    }
    private function reverseAccountBalance(Transaction $transaction): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type == "revenue" ? -$transaction->amount : $transaction->amount;

        $account->save();
    }
}
