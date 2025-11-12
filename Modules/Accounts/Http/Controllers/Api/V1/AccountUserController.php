<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\AppController;
use Modules\Accounts\Http\Requests\InviteUserAccountRequest;
use Modules\Accounts\Http\Requests\RemoveUserAccountRequest;
use Illuminate\Http\Request;
use Modules\Accounts\Repositories\AccountUserRepository;

class AccountUserController extends AppController
{
    private AccountUserRepository $accountUserRepository;
    public function __construct(AccountUserRepository $accountUserRepository)
    {
        $this->accountUserRepository = $accountUserRepository;
    }

    public function inviteUser(InviteUserAccountRequest $request, string $id)
    {
        $response = $this->accountUserRepository->inviteUser($request, $id);

        return $response;
    }

    public function acceptInvite(Request $request, string $id)
    {
        $response = $this->accountUserRepository->acceptInvite($request, $id);

        return $response;
    }

    public function destroyInvite(RemoveUserAccountRequest $request, string $id)
    {
        $response = $this->accountUserRepository->destroyInvite($request, $id);

        return $response;
    }

    public function revokeInvite(Request $request, string $id)
    {
        $response = $this->accountUserRepository->revokeInvite($request, $id);

        return $response;
    }

    public function revokeUser(RemoveUserAccountRequest $request, string $id)
    {
        $response = $this->accountUserRepository->revokeUser($request, $id);

        return $response;
    }

    public function updateUserRole(InviteUserAccountRequest $request, string $id)
    {
        $response = $this->accountUserRepository->updateUserRole($request, $id);

        return $response;
    }

    public function leave(Request $request, string $id)
    {
        $response = $this->accountUserRepository->leave($request, $id);

        return $response;
    }
}
