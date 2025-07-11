@extends('admin.base')
@section('content')
<div class="container">
    <a href="{{route('admin.dashboard')}}" class="">← Volver</a>
    <h3>Configurar rango de días</h3>
    @if(session('success')) <div class="alert alert-success">{{session('success')}}</div>@endif
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
        <button class="btn btn-primary mt-3">Guardar</button>
    </form>
</div>
@endsection
