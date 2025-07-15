<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset('storage/fondos/fondo_form3.webp') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
        }
        .form-box {
            background-color: rgba(255, 255, 255, 0.95); /* blanco con opacidad */
            padding: 2rem;
            border-radius: 15px;
            max-width: 900px;
            margin: 40px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
    </style>
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script>
</head>
<body class="bg-light">
<div class="container">
    <div class="form-box">
        <div class="text-center">
            <img src="../../images/logo_diremid.png" width="210px" alt="">
        </div>

    <div class="text-center mb-4">
        <h1 class="fw-bold">Plataforma de Recepción</h1>
        <p class="lead">
            Bienvenido a la plataforma web para la recepción diaria del backup del aplicativo <strong>SISMED</strong>
        </p>
    </div>

    @if(!$dentroDelPlazo)
        <div class="alert alert-danger text-center">
            <strong>El plazo para registrar datos ha finalizado.</strong>
            <br>Solo se puede registrar del 01 al 05 de cada mes.
        </div>
    @else
        <div id="reloj-container" class="alert alert-info text-center">
            Puedes registrar hasta: <strong id="countdown"></strong>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('registro.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Aquí van todos los campos del formulario como ya los tienes -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Nombres</label>
                        <input type="text" name="nombres" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Profesión o Cargo</label>
                        <select name="profesion_id" class="form-select" required>
                            <option value="">Seleccione una profesión</option>
                            @foreach($profesiones as $profesion)
                                <option value="{{ $profesion->id }}">{{ $profesion->nombre_profesion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Establecimiento</label>
                        <select name="establecimiento_id" class="form-select" required>
                            <option value="">Seleccione un establecimiento</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
            </div>


            <div class="mb-3">
                <label>Archivo ZIP</label>
                <input type="file" name="archivo" class="form-control" accept=".zip" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="terminos" class="form-check-input" required>
                <label class="form-check-label">Estoy de acuerdo con los términos y condiciones</label>
            </div>
            <div class="h-captcha" data-sitekey="24321fda-1948-4430-bab6-2ea8cf4e9802"></div>

            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const countdownEl = document.getElementById('countdown');

                // valores dinámicos enviados por PHP/Blade
                const inicioDia = {{ $inicio }};
                const finDia    = {{ $fin }};

                const now   = new Date();
                const year  = now.getFullYear();
                const month = now.getMonth(); // 0-indexed

                // deadline = día "fin" a las 23:59:59
                const deadline = new Date(year, month, finDia, 23, 59, 59);

                const interval = setInterval(() => {
                    const distance = deadline - Date.now();

                    if (distance <= 0) {
                        clearInterval(interval);
                        countdownEl.textContent = "¡Tiempo finalizado!";
                        return;
                    }

                    const days    = Math.floor(distance / (1000*60*60*24));
                    const hours   = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
                    const minutes = Math.floor((distance % (1000*60*60)) / (1000*60));
                    const seconds = Math.floor((distance % (1000*60)) / 1000);

                    countdownEl.textContent =
                        `${days}d ${hours}h ${minutes}m ${seconds}s`;
                }, 1000);
            });
        </script>

    @endif

</div>
</div>


</body>
</html>
