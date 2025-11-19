@extends('admin.base')
@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/toastr.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet"/>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h4>Requerimientos de Productos</h4>
        </div>
    </div>

    <!-- Filtros + Resúmenes en una sola fila -->
    <div class="row mb-3">
        <!-- Columna izquierda: filtros -->
        <div class="col-md-7">
            <div class="row mb-2">
                <div class="col-md-12 d-flex align-items-center">
                    <label for="red" class="me-2 mb-0 fw-bold">RED:</label>
                    <input id="red" type="text" class="form-control form-control-sm"  readonly style="background-color: aliceblue; flex: 1;">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 d-flex align-items-center">
                    <label for="microred" class="me-2 mb-0 fw-bold">MICRORED:</label>
                    <input id="microred" type="text" class="form-control form-control-sm" readonly style="background-color: aliceblue; flex: 1;">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex align-items-center">
                    <label for="ipress_select" class="me-2 mb-0 fw-bold">IPRESS:</label>
                    <select id="ipress_select" class="form-control select2" style="width: 100%;" 
                        >
                        {{-- @if(!$isAdmin) disabled @endif --}}
                        {{-- @if($cod_ipress)
                            <option value="{{ $cod_ipress }}" selected>
                                {{ $cod_ipress }} - {{ $nombre_ipress }} 
                            </option> --}}
                        {{-- @else --}}
                            <option value="">Seleccione una IPRESS...</option>
                        {{-- @endif --}}
                    </select>
                </div>
            </div>
            
            <br>
            
            <div class="col-md-7 d-flex justify-content-end">
                <button id="btn_generar_fer" class="btn btn-success">Generar FER</button>
            </div>
            <!-- Dentro del div de filtros, después del btn_generar_fer -->
            <div class="col-md-5 d-flex align-items-end justify-content-end">
                <button id="btn-ver-no-confirmados" class="btn btn-outline-secondary" title="Ver requerimientos no confirmados">
                    👁️ Productos
                </button>
            </div>

        </div>
        
        <!-- Columna derecha: resúmenes compactos -->
        <div class="col-md-5">
            <div class="row g-1"> <!-- g-1 reduce el espacio entre cards -->
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-danger p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">DESABASTECIDO</small>
                            <span class="card-text d-block" id="resumen_desabastecido" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-warning p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">SUBSTOCK</small>
                            <span class="card-text d-block" id="resumen_substock" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-success p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">NORMOSTOCK</small>
                            <span class="card-text d-block" id="resumen_normostock" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-info p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">SOBRESTOCK</small>
                            <span class="card-text d-block" id="resumen_sobrestock" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-secondary p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">POR VENCER</small>
                            <span class="card-text d-block" id="resumen_por_vencer" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                    <div class="card text-white bg-dark p-2 shadow-sm" style="min-height: auto;">
                        <div class="card-body p-1 text-center">
                            <small class="card-title d-block fw-bold text-uppercase" style="font-size: 0.7rem;">VENCIDO</small>
                            <span class="card-text d-block" id="resumen_vencido" style="font-size: 1.1rem; font-weight: bold;">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Tabla -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!-- Dentro del card de la tabla, antes del card-body -->
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5>Productos Disponibles</h5>
                    <div class="d-flex align-items-center">
                        <button id="btn-no-encontre" class="btn btn-outline-secondary btn-sm me-4" style="display: none;">
                            👩‍⚕️ No encontré el producto que busco...
                        </button>
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="busqueda-productos" class="form-control" placeholder="Buscar producto...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requerimientos_table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>COD_SISMED</th>
                                    <th>DESCRIPCIÓN DEL PRODUCTO</th>
                                    <th>STOCK_FINAL</th>
                                    <th>FECHA_VENC</th>
                                    <th>CPM</th>
                                    <th>MSD</th>
                                    <th>SITUACIÓN DE STOCK</th>
                                    <th>SITUACIÓN FECHA VENC.</th>
                                    <th>REQ. SUGERIDO</th>
                                    <th>REQ. FINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con JS o directamente desde PHP -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- Modal para requerimientos no confirmados -->
    <div class="modal fade" id="modalNoConfirmados" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Requerimientos No Confirmados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <button id="btn-confirmar-todos" class="btn btn-light btn-sm mb-3">✅ Confirmar Todos</button>
                    <div class="table-responsive">
                        <table id="tabla-no-confirmados" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>IPRESS</th>
                                    <th>COD_SISMED</th>
                                    <th>PRODUCTO</th>
                                    <th>STOCK</th>
                                    <th>REQ. FINAL</th>
                                    <th>FECHA(created_at)</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar cantidad -->
    <div class="modal fade" id="modalEditarCantidad" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cantidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Cantidad requerida:</label>
                        <input type="number" id="edit-req-final" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-edicion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

 

@endsection
<!-- Scripts -->
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    

    // Inicializar Select2 con AJAX
    $('#ipress_select').select2({
        placeholder: "Escriba código o nombre de IPRESS...",
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: "{{ route('matriz.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    tipo: 'cod_ipress',
                    _token: "{{ csrf_token() }}"
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

    let ipressSelected = '';

    $('#ipress_select').on('select2:select', function(e) {
        ipressSelected = e.params.data.id;
        $("#red").val(e.params.data.red);
        $("#microred").val( e.params.data.microred);
        cargarDatos(ipressSelected);
    });

    $('#ipress_select').on('select2:clear', function() {
        ipressSelected = '';
        $('#requerimientos_table tbody').empty();
        resetearResumenes();
    });

    function cargarDatos(codIpress) {
        $.ajax({
            url: "{{ route('requerimientos.data') }}",
            method: 'GET',
            data: { cod_ipress: codIpress },
            beforeSend: function() {
                $('#requerimientos_table tbody').html('<tr><td colspan="10" class="text-center">Cargando...</td></tr>');
            },
            success: function(response) {
                let tbody = '';

                response.data.forEach(item => {
                    // Normalizar la situación de stock para comparación robusta
                    const situacion = (item.situacion_stock || '').trim().toUpperCase();
                    const esBloqueado = 
                        situacion === 'SIN ROTACIÓN' || 
                        situacion === 'SIN ROTACION' || 
                        situacion === 'SOBRESTOCK';

                    let celdaReqFinal;
                    if (esBloqueado) {
                        // Opción 1: Mostrar texto (recomendado)
                        celdaReqFinal = '<span class="text-muted fst-italic small">🔒 No editable</span>';
                        
                        // Opción 2 (alternativa): input deshabilitado
                        // celdaReqFinal = `<input type="number" class="form-control" value="${item.req_final || ''}" disabled title="No se permite requerimiento en ${item.situacion_stock}">`;
                    } else {
                        celdaReqFinal = `
                            <input type="number" class="form-control req_final" 
                                data-cod-sismed="${item.cod_sismed}" 
                                data-cod-ipress="${codIpress}" 
                                data-req-sugerido="${item.req_sugerido || 0}" 
                                value="${item.req_final || ''}" min="0">
                        `;
                    }

                    tbody += `
                        <tr>
                            <td>${item.cod_sismed}</td>
                            <td>${item.descripcion_producto}</td>
                            <td>${item.stock_final}</td>
                            <td>${item.fecha_venc || ''}</td>
                            <td>${item.cpm}</td>
                            <td>${item.msd || ''}</td>
                            <td>${item.situacion_stock}</td>
                            <td>${item.situacion_fecha_venc || ''}</td>
                            <td>${item.req_sugerido || ''}</td>
                            <td>${celdaReqFinal}</td>
                        </tr>
                    `;
                });
                $('#requerimientos_table tbody').html(tbody || '<tr><td colspan="10" class="text-center">Sin datos</td></tr>');
                actualizarResumenes(response.resumenes);
            },
            error: function() {
                $('#requerimientos_table tbody').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar datos</td></tr>');
            }
        });
    }

    function actualizarResumenes(resumenes) {
        $('#resumen_desabastecido').text(resumenes.desabastecido || 0);
        $('#resumen_substock').text(resumenes.substock || 0);
        $('#resumen_normostock').text(resumenes.normostock || 0);
        $('#resumen_sobrestock').text(resumenes.sobrestock || 0);
        $('#resumen_por_vencer').text(resumenes.por_vencer || 0);
        $('#resumen_vencido').text(resumenes.vencido || 0);
    }

    function resetearResumenes() {
        $('#resumen_desabastecido').text(0);
        $('#resumen_substock').text(0);
        $('#resumen_normostock').text(0);
        $('#resumen_sobrestock').text(0);
        $('#resumen_por_vencer').text(0);
        $('#resumen_vencido').text(0);
    }

    $(document).on('blur', '.req_final', function() {
        let $input = $(this);
        let valor = $input.val();
        let reqSugerido = parseFloat($input.data('req-sugerido')) || 0;
        let codSismed = $input.data('cod-sismed');
        let codIpress = $input.data('cod-ipress');

        // Validar que sea un número válido
        if (valor === '' || isNaN(valor)) {
            $input.val(''); // opcional: limpiar
            return;
        }

        let reqFinal = parseInt(valor);

        // Validar límite: no más de 3 veces el sugerido
        if (reqSugerido > 0 && reqFinal > reqSugerido * 3) {
            alert(`⚠️ Monto excesivo: el requerimiento sugerido es ${reqSugerido}. 
    El valor máximo permitido es ${Math.floor(reqSugerido * 3)}.`);
            $input.val(''); // Opcional: limpiar el campo o dejar el valor anterior
            return;
        }

        // Si pasa la validación, proceder a guardar
        $.ajax({
            url: "{{ route('requerimientos.guardar') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                cod_sismed: codSismed,
                cod_ipress: codIpress,
                req_final: reqFinal
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Requerimiento guardado.');
                    const fila = $input.closest('tr');
                    fila.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    toastr.error(response.error || 'Error al guardar.');
                }
            },
            error: function(xhr, status, error) {
                let mensaje = 'Error desconocido.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) {
                        mensaje = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        let errores = xhr.responseJSON.errors;
                        mensaje = Object.values(errores).flat().join(' ');
                    }
                }
                toastr.error(mensaje);
                console.error('Error AJAX:', xhr.responseText || error);
            }
        });
    });

    

    $('#btn_generar_fer').click(function() {
        if (!ipressSelected) {
            alert('Por favor seleccione una IPRESS.');
            return;
        }
        alert('Función "Generar FER" aún no implementada.');
    });

    // === NUEVO: Gestión de requerimientos no confirmados ===
    $('#btn-ver-no-confirmados').click(function() {
        cargarRequerimientosNoConfirmados();
        $('#modalNoConfirmados').modal('show');
    });

    function cargarRequerimientosNoConfirmados() {
        $.get("{{ route('requerimientos.no-confirmados') }}", function(data) {
            let tbody = '';
            data.forEach(item => {
                tbody += `
                    <tr data-id="${item.id}">
                        <td>${item.ipress}</td>
                        <td>${item.cod_sismed}</td>
                        <td>${item.descripcion_producto}</td>
                        <td>${item.stock_final}</td>
                        <td>${item.req_final}</td>
                        <td>${item.created_at}</td>
                        <td>
                            <button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${item.id}">🗑️</button>
                            <button class="btn btn-outline-warning btn-sm btn-editar me-1" data-id="${item.id}" data-req="${item.req_final}">✏️</button>
                        </td>
                    </tr>
                `;
            });
            $('#tabla-no-confirmados tbody').html(tbody || '<tr><td colspan="7" class="text-center">No hay requerimientos pendientes</td></tr>');
        });
    }

    // Eliminar un requerimiento
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        if (confirm('¿Eliminar este requerimiento?')) {
            $.ajax({
                url: `/requerimientos/eliminar/${id}`,
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Eliminado.');
                        cargarRequerimientosNoConfirmados();
                        if (ipressSelected) {
                            cargarDatos(ipressSelected);
                        }
                    } else {
                        toastr.error('Error al eliminar.');
                    }
                }
            });
        }
    });

    // Confirmar todos
    $('#btn-confirmar-todos').click(function() {
        if (confirm('¿Confirmar todos los requerimientos no confirmados?')) {
            $.post("{{ route('requerimientos.confirmar') }}", {
                _token: "{{ csrf_token() }}"
            }, function(response) {
                if (response.success) {
                    toastr.success('¡Requerimientos confirmados!');
                    $('#modalNoConfirmados').modal('hide');
                } else {
                    toastr.error('Error al confirmar.');
                }
            });
        }
    });


    // === Editar cantidad ===
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        const req = $(this).data('req');
        $('#edit-id').val(id);
        $('#edit-req-final').val(req);
        $('#modalEditarCantidad').modal('show');
    });

    $('#btn-guardar-edicion').click(function() {
        const id = $('#edit-id').val();
        const reqFinal = $('#edit-req-final').val();

        if (reqFinal === '' || isNaN(reqFinal) || reqFinal < 0) {
            alert('Ingrese una cantidad válida.');
            return;
        }

        $.ajax({
            url: "{{ route('requerimientos.editar') }}",
            method: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                req_final: parseInt(reqFinal)
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Cantidad actualizada.');
                    $('#modalEditarCantidad').modal('hide');
                    cargarRequerimientosNoConfirmados(); // Recargar la tabla
                } else {
                    toastr.error('Error al actualizar.');
                }
            },
            error: function() {
                toastr.error('Error de conexión.');
            }
        });
    });


    // === Búsqueda en la tabla de productos ===
    $('#busqueda-productos').on('keyup', function() {
        let valor = $(this).val().toLowerCase().trim();
        let filas = $('#requerimientos_table tbody tr');
        let filasVisibles = 0;

        filas.each(function() {
            let fila = $(this);
            let textoFila = fila.text().toLowerCase();

            if (valor === '' || textoFila.includes(valor)) {
                fila.show();
                filasVisibles++;
            } else {
                fila.hide();
            }
        });

        // Mostrar u ocultar el botón según si hay resultados
        if (valor !== '' && filasVisibles === 0) {
            $('#btn-no-encontre').show();
        } else {
            $('#btn-no-encontre').hide();
        }
    });
});

$('#btn-no-encontre').on('click', function() {
    alert('Por favor, contacte al administrador o registre una solicitud.');
    // O abra un modal, envíe un correo, etc.
});



</script>

<script src="{{ asset('js/select2-focus.js') }}"></script>
@endsection

