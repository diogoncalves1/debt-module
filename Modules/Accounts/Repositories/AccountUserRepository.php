<?php

namespace Modules\Accounts\Repositories;

use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Exceptions\AlreadyRelationException;
use Modules\Accounts\Exceptions\CreatorInviteException;
use Modules\Accounts\Exceptions\CreatorRevokeException;
use Modules\Accounts\Exceptions\InviteAlreadySentException;
use Modules\Accounts\Exceptions\InviteNotFoundException;
use Modules\Accounts\Exceptions\InvitesLimitExceededException;
use Modules\Accounts\Exceptions\InviteUserNotAllowedException;
use Modules\Accounts\Exceptions\RelationNotExistsException;
use Modules\Accounts\Exceptions\SelfInviteException;
use Modules\Accounts\Exceptions\SingleAccountCreatorViolationException;
use Modules\Accounts\Exceptions\UnauthorizedDestroyInviteException;
use Modules\Accounts\Exceptions\UnauthorizedRevokeUserException;
use Modules\Accounts\Exceptions\UnauthorizedUpdateUserRoleException;
use Modules\Accounts\Entities\AccountUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class AccountUserRepository
{
    private $accountRepository;
    private $friendshipRepository;
    private $sharedRoleRepository;

    public function __construct(AccountRepository $accountRepository /*, FriendshipRepository $FriendshipRepository */, SharedRoleRepository $sharedRoleRepository)
    {
        $this->accountRepository = $accountRepository;
        // $this->friendshipRepository = $friendshipRepository;
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function inviteUser(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->accountRepository->show($id);

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($account, 2);

                $sharedRoleInvite = $this->sharedRoleRepository->show($request->get('shared_role_id'));

                if ($sharedRoleInvite->code == "creator") throw new CreatorInviteException();

                // if (!$this->friendshipRepository->areFriends($request->get("user_id"))) throw new \Exception();

                if ($this->isSelf($request->get("user_id"), 2)) throw new SelfInviteException();
                if ($this->exceededDeclines($request->get("user_id"), $id, 3, 30)) throw new InvitesLimitExceededException();
                if ($this->hasPendingRequest($request->get("user_id"), $id)) throw new InviteAlreadySentException();
                if ($this->hasRelation($request->get("user_id"), $id)) throw new AlreadyRelationException();
                if (!$sharedRole || !$sharedRole->hasPermission('inviteUser')) throw new InviteUserNotAllowedException();

                $input = $request->only(['user_id', 'shared_role_id']);
                $input['account_id'] = $id;
                $input['invited_at'] = Carbon::now();

                $userRelation = AccountUser::create($input);

                return response()->json(['success' => true, 'message' => __('alerts.accountInviteSended'), 'relation' => $userRelation]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorInviteUserAccount')], 500);
        }
    }
    public function acceptInvite(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {

                $user = $request->user();

                if ($this->hasRelation(3, $id)) throw new AlreadyRelationException();
                if (!$this->hasPendingRequest(3, $id)) throw new InviteNotFoundException();

                $input = ["status" => "accepted", "accepted_at" => Carbon::now()];

                $this->update(3, $id, "pending", $input);
                $userRelation = AccountUser::query()->user(3)->account($id)->status("accepted")->first();

                return response()->json(["success" => true, "message" =>  __('alerts.accountInviteAccepted'), 'relation' => $userRelation]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorAcceptInviteAccount')], 500);
        }
    }
    public function destroyInvite(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->accountRepository->show($id);

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($account, 2);

                if (!$this->hasPendingRequest($request->get("user_id"), $id)) throw new InviteNotFoundException();
                if (!$sharedRole || !$sharedRole->hasPermission('destroyInvite')) throw new UnauthorizedDestroyInviteException();

                $this->destroy($request->get("user_id"), $id, "pending");

                return response()->json(["success" => true, "message" => __('alerts.inviteDestroyed')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorDestroyInvite')], 500);
        }
    }
    public function revokeInvite(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->accountRepository->show($id);

                $user = $request->user();

                if (!$this->hasPendingRequest(1, $account->id)) throw new InviteNotFoundException();

                $input = ["status" => "revoked"];

                $this->update(1, $id, "pending", $input);
                $userRelation = AccountUser::query()->user(1)->account($id)->status("revoked")->first();

                return response()->json(['success' => true, 'message' => __('alerts.accountInviteRevoked'), 'relation' => $userRelation]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorRevokeInviteAccount')], 500);
        }
    }
    public function revokeUser(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {

                $account = $this->accountRepository->show($id);

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($account, 2);

                $sharedRoleInvite = $this->accountRepository->userSharedRole($account, $request->get('user_id'));

                if (!$this->hasRelation($request->get("user_id"), $id)) throw new RelationNotExistsException();
                if ($sharedRoleInvite->code == "creator") throw new CreatorRevokeException();
                if (!$sharedRole || !$sharedRole->hasPermission('revokeUser')) throw new UnauthorizedRevokeUserException();

                $this->destroy($request->get("user_id"), $id, "accepted");

                return response()->json(['success' => true, 'message' => __('alerts.accountUserRevoked')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorRevokeUserAccount')], 500);
        }
    }
    public function updateUserRole(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->accountRepository->show($id);

                $user = $request->user();

                $sharedRole = $this->accountRepository->userSharedRole($account, 2);

                $sharedRoleUserUpdate = $this->accountRepository->userSharedRole($account, $request->get('user_id'));
                $newSharedRoleToUpdate = $this->sharedRoleRepository->show($request->get("shared_role_id"));

                if (!$this->hasRelation($request->get("user_id"), $id)) throw new RelationNotExistsException();
                if ($newSharedRoleToUpdate->code == "creator") throw new SingleAccountCreatorViolationException();
                if ($sharedRole->code == $sharedRoleUserUpdate->code) throw new UnauthorizedUpdateUserRoleException();
                if (!$sharedRole || !$sharedRole->hasPermission('updateUserRole') || $sharedRoleUserUpdate->code == "creator") throw new UnauthorizedUpdateUserRoleException();

                $input = $request->only("shared_role_id");

                $this->update($request->get("user_id"), $id, "accepted", $input);

                return response()->json(['success' => true, 'message' => __('alerts.userSharedRoleUpdated')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorUpdateUserRole')], 500);
        }
    }
    public function leave(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $account = $this->accountRepository->show($id);

                $user = $request->user();

                if (!$this->hasRelation(1, $id)) throw new RelationNotExistsException();

                $this->destroy(1, $id, "accepted");

                return response()->json(["success" => true, "message" => __('alerts.leaveAccount')]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            if ($e->getCode())
                return response()->json(['error' => true, 'message' => $e->getMessage()], $e->getCode());
            return response()->json(['error' => true, 'message' =>  __('alerts.errorUpdateUserRole')], 500);
        }
    }


    // Private Methods
    private function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    private function exceededDeclines(string $receiverId, string $accountId, int $maxDeclines, int $days)
    {
        $limitDate = Helpers::getOldDate($days);

        return AccountUser::query()->user($receiverId)->account($accountId)->status('revoked')->where('invited_at', '>=', $limitDate)->count() >= $maxDeclines;
    }
    private function hasPendingRequest(string $receiverId, string $accountId)
    {
        return AccountUser::query()->user($receiverId)->account($accountId)->status('pending')->exists();
    }
    private function hasRelation(string $userId, string $accountId)
    {
        return AccountUser::query()->user($userId)->account($accountId)->status('accepted')->exists();
    }
    private function destroy(string $userId, string $accountId, string $status)
    {
        return AccountUser::query()->user($userId)->account($accountId)->status($status)->delete();
    }
    private function update(string $userId, string $accountId, string $status, array $input)
    {
        return AccountUser::query()->user($userId)->account($accountId)->status($status)->update($input);
    }
}
