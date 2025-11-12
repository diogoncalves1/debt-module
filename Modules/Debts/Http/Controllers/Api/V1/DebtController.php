<?php

namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Debts\Repositories\DebtRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Debts\Http\Requests\DebtRequest;
use Modules\Debts\Http\Transformers\DebtCollection;
use Modules\Debts\Http\Transformers\DebtResource;

class DebtController extends Controller
{
    private $debtRepository;

    public function __construct(DebtRepository $debtRepository)
    {
        $this->debtRepository = $debtRepository;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->debtRepository->list($request);

            return new DebtCollection($data["data"])->additional([
                'draw' => $data["draw"],
                'recordsTotal' => $data["recordsTotal"],
                'recordsFiltered' => $data["recordsFiltered"],
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            $debt = $this->debtRepository->showToUser($request, $id);

            return new DebtResource($debt);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(),]);
        }
    }

    public function store(DebtRequest $request)
    {
        try {
            $debt = $this->debtRepository->store($request);

            return new DebtResource($debt)->additional([
                'success' => true,
                "message" => __('alerts.debtStored')
            ]);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function update(Request $request, string $id)
    {
        $response = $this->debtRepository->update($request, $id);

        return $response;
    }

    public function destroy(Request $request, string $id)
    {
        $response = $this->debtRepository->destroy($request, $id);

        return $response;
    }
}
