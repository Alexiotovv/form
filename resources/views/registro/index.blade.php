@extends('admin.base')
@section('content')
    <div class="mb-2">
        <h4 class="mb-0">Lista de Archivos ICIs</h4>
        <div class="text-center mt-2">
            <a href="{{ route('registro.create') }}" class="btn btn-primary btn-sm">Crear Registro</a>
        </div>
    </div>
    <form method="GET" action="{{ route('registro.index') }}" class="mb-3 d-flex">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por uno de estos campos: nombre, apellidos, correo, fecha de envío, profesión o establecimiento...">
            <button class="btn btn-outline-primary" type="submit">🔍Buscar</button>
        </div>
    </form>
    
    <div class="table-responsive">

        <table id="registros" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Profesión</th>
                    <th>Establecimiento</th>
                    @if(auth()->user()->is_admin)
                        <th>Usuario asignado</th>
                    @endif
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Códigos_PRE</th>
                    <th>Archivo</th>
                    @if(auth()->user()->is_admin)
                        <th>Acciones</th>
                    @endif
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
                        <td>{{ $reg->almacen->cod_ipress}} - {{$reg->almacen->nombre_ipress }}</td>
                        @if(auth()->user()->is_admin)
                            <td>{{ $reg->user->name ?? 'Sin usuario' }}</td>
                        @endif
                        <td>{{ $reg->fecha_envio }}</td>
                        <td>{{ $reg->hora_envio }}</td>
                        <td>
                            @php
                                $codigosPre = $reg->procesamientoHistorico
                                    ? $reg->procesamientoHistorico->formDet
                                        ->pluck('CODIGO_PRE')
                                        ->filter()
                                        ->unique()
                                        ->values()
                                    : collect();
                            @endphp

                            @if($reg->procesado && $codigosPre->isNotEmpty())
                                <div style="max-height: 90px; overflow-y: auto; font-size: 0.85rem;">
                                    @foreach($codigosPre as $codigo)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $codigo }}</span>
                                    @endforeach
                                </div>
                            @elseif($reg->procesado)
                                <span class="text-muted">Sin códigos</span>
                            @else
                                <span class="text-muted">No procesado</span>
                            @endif
                        </td>
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
                                @if(auth()->user()->is_admin)
                                    <form action="{{ route('registros.procesar', $reg->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light {{ $reg->procesado ? 'disabled' : '' }}" {{ $reg->procesado ? 'disabled' : '' }}>
                                            ⚙️ {{ $reg->procesado ? '✓ Procesado' : 'Procesar' }}
                                        </button>
                                    </form>
                                 @endif
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        
        Mostrando {{ $registros->firstItem() }} a {{ $registros->lastItem() }} 
        de {{ $registros->total() }} registros
        Página {{ $registros->currentPage() }} de {{ $registros->lastPage() }}   
        {{ $registros->links('components.pagination') }}

    </div>

@endsection
