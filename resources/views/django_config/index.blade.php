@extends('admin.base')

@section('content')
    <h4>Configuración Django API</h4>

    <form action="{{ route('django-config.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="url" class="form-label">URL API</label>
            <input type="text" class="form-control form-control-sm" name="url" id="url" 
                   value="{{ old('url', $config->url ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="token" class="form-label">Token</label>
            <input type="text" class="form-control form-control-sm" name="token" id="token" 
                   value="{{ old('token', $config->token ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="password_zip" class="form-label">Contraseña ZIP</label>
            <input type="text" class="form-control form-control-sm" name="password_zip" id="password_zip" 
                   value="{{ old('password_zip', $config->password_zip ?? '') }}" required>
        </div>

        <button type="submit" class="btn btn-outline-primary btn-sm">💾 Guardar</button>
    </form>
@endsection
