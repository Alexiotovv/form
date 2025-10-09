<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato Estándar de Requerimiento - FER</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 { margin: 0; font-size: 16px; }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td, .info-table th {
            border: 1px solid #000;
            padding: 5px;
            font-size: 14px;
        }
        .info-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-table th {
            background-color: #e0e0e0;
            text-align: left;
            font-weight: bold;
            padding: 5px;
            border: 1px solid #000;
        }
        .products-table td {
            padding: 5px;
            border: 1px solid #000;
            font-size: 12px;
        }
        .right-box {
            float: right;
            width: 200px;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            margin-top: 10px;
        }
        .right-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .right-box td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>GERENCIA REGIONAL DE SALUD LORETO</h3>
        <h3>DIRECCIÓN EJECUTIVA DE MEDICAMENTOS, INSUMOS Y DROGAS</h3>
        <h3>FORMATO ESTÁNDAR DE REQUERIMIENTO - FER</h3>
    </div>

    <table class="info-table">
        <tr>
            <th>RED</th>
            <td>{{ $almacen ? $almacen->red : 'N/A' }}</td>
        </tr>
        <tr>
            <th>MICRORED</th>
            <td>{{ $almacen ? $almacen->microred : 'N/A' }}</td>
        </tr>
        <tr>
            <th>IPRESS</th>
            <td><strong>{{ $almacen ? $almacen->nombre_ipress : 'N/A' }}</strong></td>
        </tr>
    </table>

    <div class="right-box">
        <table>
            <tr>
                <td><strong>FECHA</strong></td>
                <td>{{ $fechaFormateada }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <table class="products-table">
        <thead>
            <tr>
                <th>COD_SISMED</th>
                <th>DESCRIPCION DEL PRODUCTO</th>
                <th>CPMA</th>
                <th>STOCK FINAL</th>
                <th>REQ. FINAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $p)
                <tr>
                    <td>{{ $p->cod_sismed }}</td>
                    <td>{{ $p->producto ? $p->producto->descripcion_producto : $p->descripcion_producto }}</td>
                    <td>{{ $p->cpm }}</td>
                    <td>{{ $p->stock_final }}</td>
                    <td>{{ $p->req_final ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento es generado automáticamente por el sistema.</p>
    </div>

</body>
</html>