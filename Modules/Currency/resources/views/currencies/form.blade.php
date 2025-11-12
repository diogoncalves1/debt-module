@extends('layouts.admin')

@section('title', 'Admin | ' . (isset($currency) ? 'Editar' : 'Adicionar') . ' Moeda ')

@section('breadcrumb')
<li class="breadcrumb-item"><a class="text-white" href="{{ route('admin.currencies.index') }}">Moedas</a></li>
<li class="breadcrumb-item active">{{ (isset($currency) ? 'Editar' : 'Adicionar') }}</li>
@endsection

@section('content')
<section class="content">
    <form id="form"
        action="{{ isset($currency) ? route('api.currencies.update', $currency->id) : route('api.currencies.store') }}">
        @csrf

        @if(isset($currency))
        <input type="hidden" name="currency_id" value="{{ $currency->id ?? null }}">
        @endif
        <div class="row">
            <div class="col-12">

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Geral</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="inputName">Código <span class="text-danger">*</span></label>
                                <input type="text" name="code" value="{{ $currency->code ?? '' }}" minlength="3" maxlength="3"
                                    placeholder="USD" data-extra="checkCurrencyCode" class="form-control" required>
                                <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                                <span class="success valid-feedback">Campo preenchido</span>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputName">Símbolo <span class="text-danger">*</span></label>
                                <input type="text" name="symbol" value="{{ $currency->symbol ?? '' }}"
                                    placeholder="$" class="validate form-control" required>
                                <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                                <span class="success valid-feedback">Campo preenchido</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            @foreach ($languages as $key => $language)
                            <li class="nav-item">
                                <a href="#{{ $language->name }}" data-toggle="tab"
                                    @class(['nav-link', 'active'=> $key == 0]) >
                                    {{ $language->value; }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">
                            @foreach ($languages as $key => $language)
                            <div @class(['tab-pane', 'active'=> $key == 0])
                                id="{{ $language->name }}">
                                <div class="form-group">
                                    <label for="inputDisplayName">Nome em {{ $language->value }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="{{ $language->name }}[name]" placeholder="Nome em {{ $language->value }}..."
                                        value="{{ isset($currency) ? (json_decode($currency->info)->{$language->name})->name ?? '' : '' }}"
                                        class="form-control" required>
                                    <span class="error invalid-feedback">Preencha este
                                        campo</span>
                                    <span class="success valid-feedback">Campo preenchido</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=" row">
            <div class="col-12">
                <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" id="btnSubmit"
                    class="btn btn-success float-right">{{ isset($currency) ? 'Editar' : 'Adicionar' }} Moeda</button>
            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
<script src="/assets/admin/js/currencies/form.js"></script>
<script src="/assets/js/allForm.js"></script>
@endsection