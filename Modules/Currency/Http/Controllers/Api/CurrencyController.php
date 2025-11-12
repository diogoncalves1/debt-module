<?php

namespace Modules\Currency\Http\Controllers\Api;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Currency\Console\Commands\FetchApiCurrenciesDaily;
use Modules\Currency\Repositories\CurrencyRepository;
use Modules\Currency\Http\Requests\CurrencyRequest;

class CurrencyController extends AppController
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function store(CurrencyRequest $request)
    {
        // $this->allowedAction('getCurrencies');

        $response = $this->currencyRepository->store($request);

        return $response;
    }

    public function update(CurrencyRequest $request, string $id)
    {
        // $this->allowedAction('getCurrencies');

        $response = $this->currencyRepository->update($request, $id);

        return $response;
    }

    public function destroy(string $id)
    {
        // $this->allowedAction('getCurrencies');

        $response = $this->currencyRepository->destroy($id);

        return $response;
    }

    public function dataTable(Request $request)
    {
        // $this->allowedAction('getCurrencies');

        $data = $this->currencyRepository->dataTable($request);

        return response()->json($data);
    }

    public function checkCode(Request $request)
    {
        // $this->allowedAction('checkCurrenciesCode');

        $request->validate([
            "id" => "nullable",
            "code" => "required|string|size:3",
        ]);

        $response = $this->currencyRepository->checkCode($request);

        return  $response;
    }

    public function updateRates()
    {
        // $this->allowedAction('updateRates');

        Artisan::call('currency:fetch-daily');

        return response()->json([
            'success' => true,
            'message' => 'Taxas atualizadas com sucesso',
        ]);
    }
}
