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
                    <button class="btn btn-danger btn-sm">Cerrar sesión</button>
                </form>
            </div>
        </div>

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
                            <a href="{{ asset('storage/' . $reg->archivo) }}" class="btn btn-sm btn-success" download>Descargar ZIP</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
