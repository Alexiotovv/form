@extends('admin.base')
@section('content')
    <h3>Configurar rango de días</h3>
    
    <form method="POST" action="{{route('plazo.update')}}">
        @csrf
        <div class="row g-3">
            <div class="col-md-3">
                <label>Día inicio</label>
                <input type="number" name="dia_inicio" value="{{old('dia_inicio',$plazo->dia_inicio)}}" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Día fin</label>
                <input type="number" name="dia_fin" value="{{old('dia_fin',$plazo->dia_fin)}}" class="form-control">
            </div>
        </div>
        <button class="btn btn-outline-primary mt-3 btn-sm">Guardar</button>
    </form>
@endsection
