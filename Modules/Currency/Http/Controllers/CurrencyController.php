<?php

namespace Modules\Currency\Http\Controllers;

use Modules\Currency\Enums\Language;
use Modules\Currency\Repositories\CurrencyRepository;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AppController;

class CurrencyController extends AppController
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function index()
    {
        // $this->allowedAction('getCurrencies');

        Session::flash('page', 'currencies');

        return view('currency::currencies.index');
    }

    public function create()
    {
        // $this->allowedAction('getCurrencies');

        Session::flash('page', 'currencies');

        $languages = Language::cases();

        return view('currency::currencies.form', compact('languages'));
    }

    // public function show(string $id)
    // {
    //     // $this->allowedAction('getCurrencies');
    // }

    public function edit(string $id)
    {
        // $this->allowedAction('getCurrencies');

        Session::flash('page', 'currencies');

        $currency = $this->currencyRepository->show($id);
        $languages = Language::cases();

        return view('currency::currencies.form', compact('currency', 'languages'));
    }
}
