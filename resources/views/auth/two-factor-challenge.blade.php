@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@section('auth_header', 'Autenticación de doble factor')

@section('auth_body')
    <div x-data="{ recovery: false }">
        {{-- Mensaje de autenticación con código de aplicación --}}
        <div class="mb-4 text-gray-600" x-show="! recovery">
            {{ __('Por favor, Ingrese el código de autenticación provisto por su aplicación de autenticación.') }}

        </div>

        {{-- Mensaje de autenticación con código de recuperación 
        <div class="mb-4 text-gray-600" x-cloak x-show="recovery">
            {{ __('Por favor, confirme el acceso a su cuenta ingresando uno de sus códigos de recuperación.') }}
        </div>
        --}}
        {{-- Errores de validación --}}
        <x-validation-errors class="mb-4" />

        {{-- Formulario --}}
        <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf

            {{-- Campo para el código de autenticación --}}
            <div class="input-group mb-3" x-show="! recovery">
                <input type="text" name="code" id="code" class="form-control" placeholder="Código de Autenticación" x-ref="code" autofocus autocomplete="one-time-code">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-key"></span>
                    </div>
                </div>
            </div>

            {{-- Campo para el código de recuperación 
            <div class="input-group mb-3" x-cloak x-show="recovery">
                <input type="text" name="recovery_code" id="recovery_code" class="form-control" placeholder="Código de Recuperación" x-ref="recovery_code" autocomplete="one-time-code">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-undo"></span>
                    </div>
                </div>
            </div>
            --}}
            <br>
            {{-- Botón de alternar entre código de autenticación y código de recuperación 
            <div class="text-center mb-3">
                <button type="button" class="btn btn-link" x-show="! recovery" x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                    {{ __('Usar un código de recuperación') }}
                </button>
                <button type="button" class="btn btn-link" x-cloak x-show="recovery" x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                    {{ __('Usar un código de autenticación') }}
                </button>
            </div>
            --}}
            {{-- Botón para enviar el formulario (color verde) --}}
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-success btn-block">
                        {{ __('Iniciar sesión') }}
                    </button>
                </div>
            </div>
        </form>
        @section('auth_footer')
    <p class="my-0">
        <a href="{{ $login_url }}">
            Volver al Inicio
        </a>
    </p>
@stop
    </div>
@stop
