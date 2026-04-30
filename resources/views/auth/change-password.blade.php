@extends('admin.base')

@section('content')
<div class="container">
    <div class="form-box">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Cambiar contraseña</h2>
            <p class="lead mb-0">Genera una nueva contraseña, cópiala y guárdala antes de actualizarla.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-warning">
            <strong>Importante:</strong> guarda la contraseña en un lugar seguro antes de enviarla. Luego no podrás verla nuevamente.
        </div>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Contraseña actual</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nueva contraseña</label>
                <div class="input-group">
                    <input type="text" name="password" id="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-primary" id="generatePassword">Generar</button>
                    <button type="button" class="btn btn-outline-secondary" id="copyPassword">Copiar</button>
                </div>
                <div class="form-text">Mínimo 8 caracteres. Usa el botón para generar una contraseña segura.</div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                <input type="text" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function generateSecurePassword(length = 12) {
        const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
        const array = new Uint32Array(length);
        window.crypto.getRandomValues(array);
        let password = '';

        for (let i = 0; i < length; i++) {
            password += characters[array[i] % characters.length];
        }

        return password;
    }

    document.getElementById('generatePassword')?.addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const confirmationInput = document.getElementById('password_confirmation');
        const password = generateSecurePassword();

        passwordInput.value = password;
        confirmationInput.value = password;
        passwordInput.focus();
        passwordInput.select();
    });

    document.getElementById('copyPassword')?.addEventListener('click', async function () {
        const passwordInput = document.getElementById('password');

        try {
            await navigator.clipboard.writeText(passwordInput.value || '');
            this.textContent = 'Copiado';
            setTimeout(() => this.textContent = 'Copiar', 1500);
        } catch (error) {
            alert('No se pudo copiar la contraseña.');
        }
    });
</script>
@endsection
