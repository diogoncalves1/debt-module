<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\AppController;
use Modules\Accounts\Http\Requests\TransactionRequest;
use Modules\Accounts\Http\Requests\TransactionUpdateRequest;
use Modules\Accounts\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class TransactionController extends AppController
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }


    public function index(Request $request)
    {
        $response = $this->transactionRepository->dataTable($request);

        return $response;
    }

    public function store(TransactionRequest $request)
    {
        $response = $this->transactionRepository->store($request);

        return $response;
    }

    public function update(TransactionUpdateRequest $request, string $id)
    {
        $response = $this->transactionRepository->update($request, $id);

        return $response;
    }

    public function destroy(Request $request, string $id)
    {
        $response = $this->transactionRepository->destroy($request, $id);

        return $response;
    }
}
