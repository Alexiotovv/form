<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso protegido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="text-center">Ingrese la clave de acceso</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('acceso.verificar') }}" class="col-md-6 mx-auto">
        @csrf
        <div class="mb-3">
            <input type="password" name="clave" class="form-control" placeholder="Clave" required>
        </div>
        <button class="btn btn-primary w-100">Ingresar</button>
    </form>
</div>
</body>
</html>
