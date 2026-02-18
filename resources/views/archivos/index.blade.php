@extends('admin.base')

@section('content')
    <h4>Descargar Archivos ZIP</h4>

    <form method="GET" action="{{ route('archivos.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="anio" class="form-label">Año</label>
            <select name="anio" id="anio" class="form-select" required>
                <option value="">Seleccione...</option>
                @foreach($anios as $anio)
                    <option value="{{ $anio }}" {{ request('anio') == $anio ? 'selected' : '' }}>
                        {{ $anio }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="mes" class="form-label">Mes</label>
            <select name="mes" id="mes" class="form-select" required>
                <option value="">Seleccione...</option>
                @foreach($meses as $mes)
                    <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($mes)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-outline-primary">🔍 Buscar</button>
        </div>
        <div class="col-md-4">
            @if(request('anio') && request('mes') && $archivos->count())
                <a href="{{ route('archivos.descargarZip', ['anio' => request('anio'), 'mes' => request('mes')]) }}"
                class="btn btn-light btn-sm mb-3">
                   📥 Descargar todo en ZIP
                </a>
            @endif
        </div>


    </form>

    @if($archivos->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre Archivo</th>
                    <th>Fecha Envío</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($archivos as $archivo)
                    <tr>
                        <td>{{ $archivo->archivo }}</td>
                        <td>{{ $archivo->fecha_envio }}</td>
                        <td>
                            <a href="{{ route('archivos.descargar', $archivo->id) }}" class="btn btn-success btn-sm">
                                Descargar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif(request()->has('anio'))
        <div class="alert alert-warning">No se encontraron archivos para el año y mes seleccionados.</div>
    @endif
@endsection
