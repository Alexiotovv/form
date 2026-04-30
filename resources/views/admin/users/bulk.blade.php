<div class="container">
    <div class="form-box">
        <div class="text-center mb-3">
            <h4 class="fw-bold">Creación masiva de usuarios</h4>
            <p class="mb-0">Genera usuarios a partir de los almacenes seleccionados. El usuario será: parte del `nombre_ipress` (desde el décimo carácter), minúsculas, espacios -> guion bajo, seguido de @sismed.com</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <form action="{{ route('admin.users.bulk.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña para todos los usuarios</label>
                <input type="text" class="form-control" id="password" name="password" required>
                <div class="form-text">La misma contraseña será aplicada a todos los usuarios creados.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar almacenes</label>
                <div class="mb-2">
                    <input type="checkbox" id="select_all_almacenes"> <label for="select_all_almacenes">Seleccionar todos</label>
                </div>

                <div class="border rounded p-2" style="max-height:300px; overflow:auto;">
                    @foreach($almacenes as $almacen)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="almacen_ids[]" value="{{ $almacen->id }}" id="alm_{{ $almacen->id }}">
                            <label class="form-check-label" for="alm_{{ $almacen->id }}">{{ $almacen->cod_ipress }} - {{ $almacen->nombre_ipress }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Crear usuarios masivos</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Volver al listado</a>
            </div>
        </form>
    </div>
</div>

@section('scripts')
    @parent
    <script>
        document.getElementById('select_all_almacenes')?.addEventListener('change', function(e){
            const checked = e.target.checked;
            document.querySelectorAll('input[name="almacen_ids[]"]').forEach(ch => ch.checked = checked);
        });
    </script>
@endsection
