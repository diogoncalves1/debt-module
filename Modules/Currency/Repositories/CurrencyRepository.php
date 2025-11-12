<?php

namespace Modules\Currency\Repositories;

use Modules\Currency\Enums\Language;
use Modules\Currency\Entities\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\Http;

class CurrencyRepository implements RepositoryInterface
{
    public function all()
    {
        return Currency::all();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $input = $request->only(['code', 'symbol']);

                $languages = Language::cases();

                foreach ($languages as $language) {
                    $info[$language->name] = $request->get($language->name);
                }

                $input["info"] = json_encode($info);

                $apiToken = "fbd30e414a2fcb5b26108b54";
                $response = Http::get("https://v6.exchangerate-api.com/v6/$apiToken/latest/USD");

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['conversion_rates'][$request->get('code')]))
                        $input['rate'] = $data['conversion_rates'][$request->get('code')];
                    else
                        $input['rate'] = 0.5;
                }

                $currency = Currency::create($input);

                Log::info('Currency ' . $currency->id . ' successfully created');
                return response()->json(['success' => true, 'message' => 'Moeda adicionada com sucesso']);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => true, 'message' => 'Erro ao tentar adicionar uma nova moeda'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $currency = $this->show($id);

                $input = $request->only(['code', 'symbol']);

                $languages = Language::cases();

                foreach ($languages as $language) {
                    $info[$language->name] = $request->get($language->name);
                }

                $input["info"] = json_encode($info);

                $currency->update($input);

                Log::info('Currency ' . $currency->id . ' successfully updated');
                return response()->json(['success' => true, 'message' => 'Moeda atualizada com sucesso']);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => true, 'message' => 'Erro ao tentar atualizar moeda'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $currency = $this->show($id);

                $currency->delete();

                Log::info('Currency ' . $currency->id . ' successfully deleted');
                return response()->json(['error' => true, 'message' => 'Moeda apagada com sucesso']);
            });
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => true, 'message' => 'Erro ao tentar apagar moeda'], 500);
        }
    }

    public function show(string $id)
    {
        return Currency::find($id);
    }

    public function dataTable(Request $request)
    {
        $query = Currency::query();
        $userLang = /* $_COOKIE['lang'] ?? */ 'en';

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where("name", 'like', "{$search}%")
                    ->orWhere("code", 'like', "{$search}%")
                    ->orWhere("symbol", 'like', "{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $currencies = $query->offset($request->start)
            ->limit($request->length)
            ->select("code", "id", "symbol", "rate", "info->{$userLang}->name as name")
            ->get();

        foreach ($currencies as &$currency) {
            $currency->actions = "<div class='btn-group'>
                            <a type='button' href='" . route('admin.currencies.edit', $currency->id) . "' class='btn mr-1 btn-default'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button type='button' onclick='modalDelete(`" .  route('api.currencies.destroy', $currency->id) . "`)' class='btn btn-default'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>";
        }

        $data = [
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $currencies
        ];

        return $data;
    }

    public function checkCode(Request $request)
    {
        $query = Currency::code($request->get('code'));

        if ($request->get("id"))
            $query->where('id', '!=', $request->get('id'));

        $exists =  $query->exists();

        return response()->json(['exists' => $exists]);
    }
}
