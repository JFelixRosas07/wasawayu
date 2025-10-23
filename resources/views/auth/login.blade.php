@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Iniciar Sesión - Wasawayu')

@section('auth_header')
    <div class="text-center mb-3">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Wasawayu"
             class="logo-wasawayu mb-3">
        <h2>Sistema Wasawayu</h2>
        <p>Gestión de Rotación de Cultivos</p>
    </div>
@stop

@section('auth_body')
    {{-- Mensaje de error general --}}
    @if ($errors->has('error'))
        <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first('error') }}
        </div>
    @endif

    {{-- Mensajes de estado --}}
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="post">
        @csrf

        {{-- Email --}}
        <div class="input-group mb-3">
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control custom-input"
                   placeholder="Correo electrónico" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text bg-success">
                    <span class="fas fa-envelope text-white"></span>
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="input-group mb-3">
            <input type="password" name="password"
                   class="form-control custom-input"
                   placeholder="Contraseña" required>
            <div class="input-group-append">
                <div class="input-group-text bg-success">
                    <span class="fas fa-lock text-white"></span>
                </div>
            </div>
        </div>
        {{-- Captcha: solo a partir del 3er intento fallido --}}
        @php
            $limiter = app(\Illuminate\Cache\RateLimiter::class);
            $showCaptcha = $limiter->attempts(old('email') . '|' . request()->ip()) >= 2;
        @endphp

        @if ($showCaptcha)
            <div class="mb-3 text-center">
                <div class="d-flex justify-content-center mb-2">
                    {!! captcha_img('flat') !!}
                </div>
                <input type="text" name="captcha"
                       class="form-control custom-input"
                       placeholder="Escribe el texto de la imagen">
                @error('captcha')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>
        @endif

        {{-- Botón --}}
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-success btn-block shadow custom-btn">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <p class="text-center text-muted mt-3">
        <small>&copy; {{ date('Y') }} Wasawayu</small>
    </p>
@stop

@push('css')
<style>
    /* Fondo */
    body.login-page {
        background: url('{{ asset('images/fondo_andino.png') }}') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Nunito', sans-serif;
    }

    /* Ocultar logo AdminLTE original */
    .login-logo {
        display: none !important;
    }

    /* Card translúcido */
    .login-card-body, .card {
        background: rgba(255, 255, 255, 0.7) !important;
        border-radius: 15px;
        backdrop-filter: blur(8px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    }

    /* Logo */
    .logo-wasawayu {
        width: 220px;
        max-width: 80%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        object-fit: cover;
    }

    /* Textos */
    h2 {
        color: #2E4600;
        font-weight: 700;
        font-family: 'Quicksand', sans-serif;
    }

    p {
        color: #444;
        font-size: 1rem;
    }

    /* Inputs */
    .custom-input {
        border-radius: 10px 0 0 10px !important;
        border: 1px solid #ccc;
        transition: all 0.3s ease-in-out;
    }

    .custom-input:focus {
        border-color: #228B22;
        box-shadow: 0 0 8px rgba(34, 139, 34, 0.3);
    }

    /* Botón */
    .custom-btn {
        border-radius: 10px;
        transition: all 0.3s ease-in-out;
    }

    .custom-btn:hover {
        background: linear-gradient(90deg, #228B22, #2E8B57);
        border-color: #2E8B57;
        transform: scale(1.02);
    }

    /* Responsividad */
    @media (max-width: 768px) {
        .logo-wasawayu {
            width: 150px;
        }
        h2 {
            font-size: 1.5rem;
        }
        p {
            font-size: 0.9rem;
        }
    }
</style>
@endpush
