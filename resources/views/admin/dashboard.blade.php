@extends('admin.base')
@section('content')
    <div class="container mt-5">
        <div class="text-center">
            <img src="../../images/logo_diremid_trans.png" alt="" width="220px">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Registros enviados</h2>
            
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
                {{ session('success') }}
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
                <a href="{{ route('clave.edit') }}" class="btn btn-light btn-sm">
                    🔑 Configurar clave de acceso al formulario
                </a>
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

            @endif
        @endauth
        

        <table id="registros" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Profesión</th>
                    <th>Establecimiento</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Archivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $reg)
                    <tr>
                        <td>{{ $reg->nombres }}</td>
                        <td>{{ $reg->apellidos }}</td>
                        <td>{{ $reg->correo }}</td>
                        <td>{{ $reg->telefono }}</td>
                        <td>{{ $reg->profesion->nombre_profesion }}</td>
                        <td>{{ $reg->establecimiento->nombre }}</td>
                        <td>{{ $reg->fecha_envio }}</td>
                        <td>{{ $reg->hora_envio }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ asset('storage/' . $reg->archivo) }}" class="btn btn-sm btn-outline-success" download>
                                    Descargar ZIP
                                </a>

                                @if(auth()->user()->is_admin)
                                    <form action="{{ route('registros.destroy', $reg->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-light"
                                                onclick="return confirm('¿Eliminar registro de {{ $reg->nombres }} {{ $reg->apellidos }}?')">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">

                                <form action="{{ route('registros.procesar', $reg->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light {{ $reg->procesado ? 'disabled' : '' }}" {{ $reg->procesado ? 'disabled' : '' }}>
                                        ⚙️ {{ $reg->procesado ? '✓ Procesado' : 'Procesar' }}
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
