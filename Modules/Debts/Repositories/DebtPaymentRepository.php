<?php

namespace Modules\Debts\Repositories;

use Modules\Debts\Entities\DebtPayment;
use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DebtPaymentRepository implements RepositoryApiInterface
{

    private $debtRepository;

    public function __construct(DebtRepository $debtRepository)
    {
        $this->debtRepository = $debtRepository;
    }

    public function all() {}

    public function list(Request $request)
    {
        $user = $request->user();

        // App::setLocale($user->preferences->lang ?? 'en');

        $query =  DebtPayment::query();

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


        $debtPayments = $query->offset($request->start)
            ->limit($request->length)
            ->whereHas("users", function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->distinct()
            ->get();

        $total = $query->count();

        foreach ($debtPayments as &$payment) {
            // $account->icon = Helpers::getAccountIcon($account->type);
            $payment->typeTranslated = __("frontend." . $payment->type);
            $payment->user = $payment->users->map(function ($user) {
                return $user->name;
            });
            $payment->currencySymbol = $payment->currency->symbol;

            $payment->statusTranslated = $payment->active ? __('portal.active') : __('portal.inactive');

            $sharedRole = $this->debtRepository->userSharedRole($payment, $user->id);

            // $account->balaceFormatted = Helpers::formatMoneyWithSymbol($account->balance);

            $btnGroup = '<div class="d-flex justify-content-center gap-1">';
            if ($sharedRole->hasPermission("viewDebtPaymentDetails"))
                $btnGroup .= '<a href="debt-payments/' . $payment->id . '" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>';
            if ($sharedRole->hasPermission("editDebtPayment"))
                $btnGroup .= '<a href="debt-payments/' .  $payment->id . '/edit" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>';
            if ($sharedRole->hasPermission("deleteDebtPayment"))
                $btnGroup .= "<button type='button' onclick='modalDelete({$payment->id})' 
                                data-table-delete-row 
                                 class='btn btn-light btn-icon btn-sm rounded-circle'>
                                <i class='ti ti-trash fs-lg'></i>
                            </button>";
            $btnGroup .= "</div>";
            $payment->input = '<input data-id="' . $payment->id . '" class="form-check-input form-check-input-light fs-14 file-item-check mt-0" type="checkbox">';
            $payment->actions = $btnGroup;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $debtPayments
        ]);
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = $request->user();

                $input = $request->only([]);

                $debtPayment = DebtPayment::create($input);

                return response()->json(['success' => true, "message" => __('alerts.debtPaymentStored'), "data" => $debtPayment]);
            });
        } catch (\Exception $e) {
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                return response()->json(["success" => true, "message" => __('')]);
            });
        } catch (\Exception $e) {
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
        } catch (\Exception $e) {
        }
    }

    public function show(string $id) {}


    // private methods
    private function adjustDebtPaidAmount(DebtPayment $debtTransaction): void
    {
        $debt = $debtTransaction->debt;

        $debt->paid_amount += $debtTransaction->amount;

        $debt->save();
    }
    private function reverseDebtPaidAmount(DebtPayment $debtTransaction): void
    {
        $debt = $debtTransaction->debt;
        if ($debt) {
            $debt->paid_amount -= $debtTransaction->amount;

            $debt->save();
        }
    }
}
