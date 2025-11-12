<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\AppController;
use Modules\Accounts\Http\Requests\AccountRequest;
use Modules\Accounts\Repositories\AccountRepository;
use Illuminate\Http\Request;

class AccountController extends AppController
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function index(Request $request)
    {
        $response = $this->accountRepository->dataTable($request);

        return $response;
    }

    public function show(Request $request, string $id)
    {
        $response = $this->accountRepository->showToUser($request, $id);

        return $response;
    }

    public function store(AccountRequest $request)
    {
        $response = $this->accountRepository->store($request);

        return $response;
    }

    public function update(AccountRequest $request, string $id)
    {
        $response = $this->accountRepository->update($request, $id);

        return $response;
    }

    public function destroy(Request $request, string $id)
    {
        $response = $this->accountRepository->destroy($request, $id);

        return $response;
    }
}
