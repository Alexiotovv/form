@extends('admin.dashboard') {{-- Usa tu plantilla Bootstrap base si tienes --}}

@section('content')

    <h4>Gestión de Tokens Personales</h4>

   @if(session('token_generado'))
        <div class="alert alert-success d-flex align-items-center justify-content-between">
            <div>
                <strong>Token generado:</strong><br>
                <code id="tokenGenerado">{{ session('token_generado') }}</code>
            </div>
            <button class="btn btn-sm btn-outline-primary ms-3" onclick="copiarToken()">
                📑
            </button>
        </div>
        Copiar este token, después no podrás recuperarlo
    @endif


    @if(session('mensaje'))
        <div class="alert alert-info">
            {{ session('mensaje') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tokens.store') }}" class="mb-4">
        @csrf
        <div class="input-group">
            <input type="text" name="token_name" class="form-control" placeholder="Nombre del token" required>
            <button class="btn btn-primary" type="submit">Generar Token</button>
        </div>
    </form>

    <h5>Tokens activos:</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($tokens as $token)
            <tr>
                <td>{{ $token->id }}</td>
                <td>{{ $token->name }}</td>
                <td>{{ $token->created_at }}</td>
                <td>
                    <form method="POST" action="{{ route('tokens.destroy', $token->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-light btn-sm">Revocar</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
@section('scripts')
    <script>
    function copiarToken() {
        const token = document.getElementById('tokenGenerado').textContent;
        navigator.clipboard.writeText(token).then(function () {
            alert('Token copiado al portapapeles');
        }).catch(function (err) {
            console.error('Error al copiar el token: ', err);
            alert('No se pudo copiar el token');
        });
    }
</script>
<script>
    function copiarTexto(id) {
        const texto = document.getElementById(id).innerText;
        navigator.clipboard.writeText(texto)
            .then(() => alert('Token copiado al portapapeles'))
            .catch(err => alert('Error al copiar token: ' + err));
    }
</script>
@endsection