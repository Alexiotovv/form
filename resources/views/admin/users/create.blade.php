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
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <button type="button" id="togglePassword" class="btn btn-outline-secondary btn-sm" title="Mostrar/Ocultar contraseña">👁️</button>
                            </div>
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
                            <div class="d-flex gap-2">
                                <button type="button" id="generatePassword" class="btn btn-outline-primary btn-sm">🔐 Generar</button>
                                <button type="button" id="copyPassword" class="btn btn-outline-secondary btn-sm" title="Copiar contraseña">📋</button>
                            </div>
                        </div>
                        <br>
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

            function shuffleString(str) {
                var arr = str.split('');
                for (var i = arr.length - 1; i > 0; i--) {
                    var j = Math.floor(Math.random() * (i + 1));
                    var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp;
                }
                return arr.join('');
            }

            function generateMediumPassword(length = 10) {
                var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZÑ';
                var lower = 'abcdefghijkmnpqrstuvwxyzñ';
                var numbers = '0123456789';
                var special = '!@#$%&*?¡¿-_+[]{}()=<>.,;:\\"';

                // Ensure at least one of each category
                var pwd = '';
                pwd += upper.charAt(Math.floor(Math.random() * upper.length));
                pwd += lower.charAt(Math.floor(Math.random() * lower.length));
                pwd += numbers.charAt(Math.floor(Math.random() * numbers.length));
                pwd += special.charAt(Math.floor(Math.random() * special.length));

                var all = upper + lower + numbers + special;
                for (var i = pwd.length; i < length; i++) {
                    pwd += all.charAt(Math.floor(Math.random() * all.length));
                }

                return shuffleString(pwd);
            }

            $('#generatePassword').on('click', function() {
                var pwd = generateMediumPassword(10);
                $('#password').val(pwd).trigger('input');
                $('#password-confirm').val(pwd).trigger('input');
            });

            $('#togglePassword').on('click', function() {
                var $btn = $(this);
                var $pwd = $('#password');
                var $pwdc = $('#password-confirm');
                var type = $pwd.attr('type') === 'password' ? 'text' : 'password';
                $pwd.attr('type', type);
                $pwdc.attr('type', type);
                $btn.html(type === 'text' ? '🙈' : '👁️');
            });

            $('#copyPassword').on('click', function() {
                var $btn = $(this);
                var pwd = $('#password').val();
                if (!pwd) {
                    // generate if empty
                    pwd = generateMediumPassword(10);
                    $('#password').val(pwd);
                    $('#password-confirm').val(pwd);
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(pwd).then(function() {
                        var original = $btn.html();
                        $btn.html('✅');
                        setTimeout(function() { $btn.html(original); }, 1000);
                    });
                } else {
                    // fallback
                    var $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(pwd).select();
                    document.execCommand('copy');
                    $temp.remove();
                    var original = $btn.html();
                    $btn.html('✅');
                    setTimeout(function() { $btn.html(original); }, 1000);
                }
            });

        });
    </script>
@endsection