@extends('admin.base')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Estilo Select2 para que se parezca a Bootstrap 5 */
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        display: flex;
        align-items: center;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.5 !important;
        color: #212529 !important;
        margin-top: -2px;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem + 2px) !important;
        right: 8px;
    }

    .select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-container {
        width: 100% !important;
    }

    /* Placeholder */
    .select2-selection__placeholder {
        color: #6c757d !important;
    }
    
</style>
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <br>
            <div class="card">
                <div class="card-header"> Crear Nuevo Usuario</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Correo</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password-confirm">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="password-confirm" name="password_confirmation" required>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" {{ old('is_admin') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_admin">
                                    Usuario Admin
                                </label>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="almacen_id" class="form-label">Establecimiento</label>
                            <select name="almacen_id" id="almacen_id" class="form-select select2" required>
                                <!-- vacío, Select2 llenará con AJAX -->
                            </select>
                        </div>

                        <br>
                        <button type="submit" class="btn btn-outline-primary btn-sm">💾 Guardar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#almacen_id').select2({
                theme: 'bootstrap4',
                placeholder: "Escriba código o nombre de IPRESS...",
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('matriz.searchAlmacen') }}", // 👈 ruta nueva
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

        });
    </script>
@endsection