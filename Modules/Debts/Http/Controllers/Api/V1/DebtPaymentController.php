<?php

namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Debts\Repositories\DebtPaymentRepository;
use Illuminate\Http\Request;
use Modules\Debts\Http\Transformers\DebtCollection;

class DebtPaymentController extends Controller
{
    private $debtPaymentRepository;

    public function __construct(DebtPaymentRepository $repository)
    {
        $this->debtPaymentRepository = $repository;
    }

    public function index(Request $request)
    {
        try {
            $debtsPayments = $this->debtPaymentRepository->list($request);

            return new DebtCollection($debtsPayments);
        } catch (\Exception $e) {
        }
    }

    public function store(Request $request)
    {
        $response = $this->debtPaymentRepository->store($request);

        return $response;
    }

    public function update(Request $request, string $id)
    {
        $response = $this->debtPaymentRepository->update($request, $id);

        return $response;
    }

    public function destroy(Request $request, string $id)
    {
        $response = $this->debtPaymentRepository->destroy($request, $id);

        return $response;
    }
}
