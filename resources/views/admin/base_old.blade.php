<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-white">
    <div class="container mt-5">
        <div class="text-center">
            <img src="../../images/logo_diremid_trans.png" alt="" width="220px">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5>Formulario de Registro</h5>
            
            <div class="text-end">
                <p class="mb-1">👤 Bienvenido, {{ Auth::user()->name }}</p>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-light btn-sm">Cerrar sesión</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                    {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
            @auth  
            @if(auth()->user()->is_admin)
                {{-- <a href="{{ route('clave.edit') }}" class="btn btn-light btn-sm">
                    🔑 Configurar clave de acceso al formulario
                </a> --}}
                <a href="{{ route('plazo.edit') }}" class="btn btn-light btn-sm">
                    🕒 Configurar plazo de envío
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                    👥 Usuarios
                </a>
                <a href="{{ route('establecimientos.index') }}" class="btn btn-light btn-sm">
                    🏥 Establecimientos
                </a>
                <a href="{{ route('tokens.index') }}" class="btn btn-light btn-sm">
                    🗝️ Tokens de acceso
                </a>
                <a href="{{ route('django-config.index') }}" class="btn btn-light btn-sm">
                    🗝️ Django Config
                </a>
                <a href="{{ route('archivos.index') }}" class="btn btn-light btn-sm">
                    📑 Descargar Archivos
                </a>
                <a href="{{ route('registro.create') }}" class="btn btn-light btn-sm">
                    📑 Registro ICI
                </a>
                <a href="{{ route('historicos.index') }}" class="btn btn-light btn-sm">
                    📑 Procesamiento Históricos
                </a>
                <a href="{{ route('disas.index') }}" class="btn btn-light btn-sm">
                    📑 Disas
                </a>
                
            @else
                <a href="{{ route('registro.create') }}" class="btn btn-light btn-sm">
                    📑 Registro ICI
                </a>
            @endif
        @endauth
        @yield('content')
    </div>
    
    
    @yield('scripts')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#registros').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });
    </script>
     
</body>
</html>
