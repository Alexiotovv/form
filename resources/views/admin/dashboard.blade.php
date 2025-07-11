@extends('admin.base')
@section('content')
    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Registros enviados</h2>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger">Cerrar sesión</button>
            </form>
        </div>
        <a href="{{ route('clave.edit') }}" class="btn btn-light btn-sm">
            🔑 Configurar clave de acceso al formulario
        </a>
        <a href="{{ route('plazo.edit') }}" class="btn btn-light btn-sm">
            🕒 Configurar plazo de envío
        </a>

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
