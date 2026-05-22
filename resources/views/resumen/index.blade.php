@extends('admin.base')

@section('css')
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/select2-bootstrap.css') }}" rel="stylesheet" />
<style>
    .resumen-wrap {
        font-size: 0.72rem;
    }

    .panel-filtros {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 0;
    }

    .panel-filtros .form-label {
        font-size: 0.64rem;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .panel-filtros .form-control,
    .panel-filtros .form-select {
        font-size: 0.66rem;
        min-height: 28px;
        padding: 2px 6px;
    }

    .panel-filtros .btn {
        font-size: 0.66rem;
        padding: 3px 8px;
    }

    .panel-filtros .select2-container .select2-selection--multiple {
        min-height: 28px !important;
        font-size: 0.66rem !important;
    }

    .panel-filtros .select2-container .select2-selection--single {
        height: 28px !important;
        font-size: 0.66rem !important;
    }

    .panel-filtros .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
    }

    .panel-filtros .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 0.62rem !important;
        padding: 1px 4px !important;
    }

    /* El dropdown de select2 se renderiza fuera del panel, por eso va en global */
    .select2-dropdown {
        font-size: 0.64rem !important;
    }

    .select2-container--default .select2-results__option {
        font-size: 0.64rem !important;
        line-height: 1.1 !important;
        padding: 4px 8px !important;
        min-height: 22px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        font-size: 0.64rem !important;
    }

    .select2-search--dropdown .select2-search__field {
        font-size: 0.64rem !important;
        padding: 3px 6px !important;
        min-height: 24px;
    }

    .select2-results__group {
        font-size: 0.62rem !important;
        padding: 4px 8px !important;
    }

    .top-layout {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
        align-items: flex-start;
    }

    .left-pane {
        width: 70%;
        min-width: 0;
    }

    .right-pane {
        width: 30%;
        min-width: 260px;
    }

    .grid-filtros {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .resumen-card {
        max-width: 100%;
        border: 2px solid #0b63b6;
        margin-bottom: 0;
    }

    .resumen-card table {
        margin-bottom: 0;
        font-size: 0.74rem;
    }

    .resumen-card thead th,
    .resumen-card tfoot th {
        background: #0b63b6;
        color: #fff;
        text-align: center;
        vertical-align: middle;
    }

    .resumen-card tbody tr {
        background: #f3f3f3;
    }

    .resumen-card td,
    .resumen-card th {
        padding: 3px 6px;
        vertical-align: middle;
    }

    .resumen-card tbody td:nth-child(2),
    .resumen-card tbody td:nth-child(3),
    .resumen-card tfoot th:nth-child(2),
    .resumen-card tfoot th:nth-child(3) {
        text-align: right;
    }

    .danger-text {
        color: #d00000;
        font-weight: 700;
    }

    .tabla-wrap {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: auto;
        max-height: 68vh;
    }

    .tabla-resumen {
        width: 100%;
        font-size: 0.82rem;
        margin-bottom: 0;
    }

    .tabla-resumen thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        background: #0b63b6;
        color: #fff;
    }

    .tabla-resumen thead th.col-verde {
        background: #00b050;
    }

    .tabla-resumen thead th.col-rosado {
        background: #f28c8c;
    }

    .tabla-resumen thead th.col-rojo {
        background: #ff0000;
    }

    .tabla-resumen thead th.col-azul {
        background: #0b63b6;
    }

    .tabla-resumen td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .tabla-resumen td.num {
        text-align: right;
        font-weight: 600;
    }

    .row-red {
        background: #e6f0ff;
        font-weight: 700;
    }

    .row-microred {
        background: #f4f8ff;
    }

    .nivel-alto {
        color: #198754;
        font-weight: 700;
    }

    .nivel-regular {
        color: #ffc000;
        font-weight: 700;
    }

    .nivel-bajo {
        color: #d00000;
        font-weight: 700;
    }

    .toggle-btn {
        border: none;
        background: none;
        color: #0d6efd;
        font-weight: 700;
        margin-right: 4px;
        cursor: pointer;
    }

    .indent-1 {
        padding-left: 20px !important;
    }

    .indent-2 {
        padding-left: 36px !important;
    }

    .loading-row {
        background: #fff8e1;
        color: #8a6d3b;
        font-style: italic;
    }

    @media (max-width: 992px) {
        .top-layout {
            flex-direction: column;
        }

        .left-pane,
        .right-pane {
            width: 100%;
            min-width: 0;
        }

        .grid-filtros {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('titulo_pagina')
Resumen
@endsection

@section('content')
<div class="container-fluid resumen-wrap">
    <div class="top-layout">
        <div class="left-pane">
            <div class="panel-filtros">
                <form method="GET" action="{{ route('resumen.index') }}" id="form-resumen">
                    <div class="grid-filtros">
                        <div>
                            <label class="form-label">Fin de mes</label>
                            <input type="date" name="fin_mes" class="form-control form-control-sm" value="{{ $fechaManual }}">
                        </div>

                        <div>
                            <label class="form-label">Red</label>
                            <select name="red[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['redes'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['red']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Microred</label>
                            <select name="microred[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['microredes'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['microred']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Tipo de establecimiento</label>
                            <select name="tipo_establecimiento[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['tipos_establecimiento'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['tipo_establecimiento']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Tipo de abastecimiento</label>
                            <select name="tipo_abastecimiento[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['tipos_abastecimiento'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['tipo_abastecimiento']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Tipo prod</label>
                            <select name="tipo_prod[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['tipos_prod'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['tipo_prod']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">PETI2023</label>
                            <select name="peti2023[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['peti2023'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['peti2023']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">TIPSUM</label>
                            <select name="tip_sum[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['tipsum'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['tip_sum']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Lista N 1</label>
                            <select name="lista_1[]" class="form-control form-control-sm select2" multiple>
                                @foreach($opcionesFiltros['lista_1'] as $valor)
                                    <option value="{{ $valor }}" {{ in_array($valor, $filtros['lista_1']) ? 'selected' : '' }}>{{ $valor }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                        <a href="{{ route('resumen.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="right-pane">
            <div class="resumen-card">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ITEM</th>
                            <th>CANTIDAD</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>SUBSTOCK</td><td id="sum-substock">0</td><td id="sum-substock-pct">0.0%</td></tr>
                        <tr><td>NORMOSTOCK</td><td id="sum-normostock">0</td><td id="sum-normostock-pct">0.0%</td></tr>
                        <tr><td>SOBRESTOCK</td><td id="sum-sobrestock">0</td><td id="sum-sobrestock-pct">0.0%</td></tr>
                        <tr><td>SIN ROTACION</td><td id="sum-sin-rotacion">0</td><td id="sum-sin-rotacion-pct">0.0%</td></tr>
                        <tr><td>POR VENCER</td><td id="sum-por-vencer">0</td><td id="sum-por-vencer-pct">0.0%</td></tr>
                        <tr><td class="danger-text">VENCIDOS</td><td id="sum-vencidos">0</td><td id="sum-vencidos-pct">0.0%</td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>TOTAL</th>
                            <th id="sum-total">0</th>
                            <th id="sum-total-pct">100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-2 d-flex justify-content-between">
        <div>
            <strong>Total de registros analizados:</strong> <span id="total-registros">{{ $totalRegistros }}</span>
        </div>
    </div>

    <div class="tabla-wrap">
        <table class="table table-bordered table-hover tabla-resumen" id="tabla-resumen">
            <thead>
                <tr>
                    <th>Red / Microred / Ipress</th>
                    <th class="col-verde">SIN CONSUMO</th>
                    <th class="col-verde">DESABASTECIDO</th>
                    <th class="col-verde">SUBSTOCK</th>
                    <th class="col-verde">NORMOSTOCK</th>
                    <th class="col-verde">SOBRESTOCK</th>
                    <th class="col-verde">SIN ROTACION</th>
                    <th class="col-rosado">POR VENCER</th>
                    <th class="col-rojo">VENCIDOS</th>
                    <th class="col-azul">TOTAL</th>
                    <th class="col-azul">% DISPONIBILIDAD</th>
                    <th class="col-azul">NIVEL DISPONIBILIDAD</th>
                </tr>
            </thead>
            <tbody id="tbody-resumen">
                @forelse($redes as $red)
                    <tr class="row-red" data-level="red" data-red="{{ $red }}" data-loaded="0" data-expanded="0">
                        <td>
                            <button type="button" class="toggle-btn btn-red">+</button>
                            {{ $red }}
                        </td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="nivel-bajo">-</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-3">No hay redes para los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
    $(function () {
        $('.select2').select2({
            width: '100%'
        });

        const urlRedData = "{{ route('resumen.red-data') }}";
        const urlMicroData = "{{ route('resumen.microred-data') }}";

        function serializeForm() {
            return $('#form-resumen').serialize();
        }

        function nivelClass(nivel) {
            if (nivel === 'ALTO') return 'nivel-alto';
            if (nivel === 'REGULAR') return 'nivel-regular';
            return 'nivel-bajo';
        }

        const resumenCargadoPorRed = {};

        function fmtPct(v) {
            return Number(v).toFixed(1) + '%';
        }

        function recalcularCuadroResumen() {
            let substock = 0;
            let normostock = 0;
            let sobrestock = 0;
            let sinRotacion = 0;
            let porVencer = 0;
            let vencidos = 0;

            Object.values(resumenCargadoPorRed).forEach(function (t) {
                substock += Number(t.substock || 0);
                normostock += Number(t.normostock || 0);
                sobrestock += Number(t.sobrestock || 0);
                sinRotacion += Number(t.sin_rotacion || 0);
                porVencer += Number(t.por_vencer || 0);
                vencidos += Number(t.vencidos || 0);
            });

            const total = substock + normostock + sobrestock;
            const base = total > 0 ? total : 1;

            $('#sum-substock').text(substock);
            $('#sum-substock-pct').text(fmtPct((substock / base) * 100));
            $('#sum-normostock').text(normostock);
            $('#sum-normostock-pct').text(fmtPct((normostock / base) * 100));
            $('#sum-sobrestock').text(sobrestock);
            $('#sum-sobrestock-pct').text(fmtPct((sobrestock / base) * 100));
            $('#sum-sin-rotacion').text(sinRotacion);
            $('#sum-sin-rotacion-pct').text(fmtPct((sinRotacion / base) * 100));
            $('#sum-por-vencer').text(porVencer);
            $('#sum-por-vencer-pct').text(fmtPct((porVencer / base) * 100));
            $('#sum-vencidos').text(vencidos);
            $('#sum-vencidos-pct').text(fmtPct((vencidos / base) * 100));
            $('#sum-total').text(total);
            $('#sum-total-pct').text('100%');
        }

        function renderTotalesEnFila($tr, t) {
            const celdas = $tr.find('td');
            celdas.eq(1).text(t.sin_consumo);
            celdas.eq(2).text(t.desabastecido);
            celdas.eq(3).text(t.substock);
            celdas.eq(4).text(t.normostock);
            celdas.eq(5).text(t.sobrestock);
            celdas.eq(6).text(t.sin_rotacion);
            celdas.eq(7).text(t.por_vencer);
            celdas.eq(8).text(t.vencidos);
            celdas.eq(9).text(t.total);
            celdas.eq(10).text(Number(t.disponibilidad).toFixed(1) + '%');
            celdas.eq(11).removeClass('nivel-alto nivel-regular nivel-bajo').addClass(nivelClass(t.nivel)).text(t.nivel);
        }

        function buildMicroRow(red, micro) {
            const t = micro.totales;
            return `
                <tr class="row-microred child-of-red" data-level="micro" data-red="${red}" data-micro="${micro.microred}" data-loaded="0" data-expanded="0" style="display:none;">
                    <td class="indent-1"><button type="button" class="toggle-btn btn-micro">+</button>${micro.microred}</td>
                    <td class="num">${t.sin_consumo}</td>
                    <td class="num">${t.desabastecido}</td>
                    <td class="num">${t.substock}</td>
                    <td class="num">${t.normostock}</td>
                    <td class="num">${t.sobrestock}</td>
                    <td class="num">${t.sin_rotacion}</td>
                    <td class="num">${t.por_vencer}</td>
                    <td class="num">${t.vencidos}</td>
                    <td class="num">${t.total}</td>
                    <td class="num">${Number(t.disponibilidad).toFixed(1)}%</td>
                    <td class="${nivelClass(t.nivel)}">${t.nivel}</td>
                </tr>
            `;
        }

        function buildIpressRow(red, micro, ip) {
            const t = ip.totales;
            return `
                <tr class="child-of-micro" data-level="ipress" data-red="${red}" data-micro="${micro}" style="display:none;">
                    <td class="indent-2">${ip.codigo} - ${ip.nombre}</td>
                    <td class="num">${t.sin_consumo}</td>
                    <td class="num">${t.desabastecido}</td>
                    <td class="num">${t.substock}</td>
                    <td class="num">${t.normostock}</td>
                    <td class="num">${t.sobrestock}</td>
                    <td class="num">${t.sin_rotacion}</td>
                    <td class="num">${t.por_vencer}</td>
                    <td class="num">${t.vencidos}</td>
                    <td class="num">${t.total}</td>
                    <td class="num">${Number(t.disponibilidad).toFixed(1)}%</td>
                    <td class="${nivelClass(t.nivel)}">${t.nivel}</td>
                </tr>
            `;
        }

        $(document).on('click', '.btn-red', function () {
            const $tr = $(this).closest('tr');
            const red = $tr.data('red');
            const expanded = Number($tr.attr('data-expanded')) === 1;
            const loaded = Number($tr.attr('data-loaded')) === 1;

            if (expanded) {
                $(`tr.child-of-red[data-red="${red}"]`).hide();
                $(`tr.child-of-micro[data-red="${red}"]`).hide();
                $tr.attr('data-expanded', '0');
                $(this).text('+');
                return;
            }

            if (loaded) {
                $(`tr.child-of-red[data-red="${red}"]`).show();
                $tr.attr('data-expanded', '1');
                $(this).text('-');
                return;
            }

            const loading = `<tr class="loading-row child-of-red" data-red="${red}"><td colspan="12" class="indent-1">Cargando microredes de ${red}...</td></tr>`;
            $tr.after(loading);

            $.getJSON(`${urlRedData}?${serializeForm()}&red=${encodeURIComponent(red)}`)
                .done(function (resp) {
                    renderTotalesEnFila($tr, resp.totales);
                    resumenCargadoPorRed[red] = resp.totales || {};
                    recalcularCuadroResumen();
                    $tr.attr('data-loaded', '1').attr('data-expanded', '1');
                    $tr.find('.btn-red').text('-');

                    const rows = (resp.microredes || []).map(m => buildMicroRow(red, m)).join('');
                    $(`tr.loading-row.child-of-red[data-red="${red}"]`).replaceWith(rows);
                })
                .fail(function () {
                    $(`tr.loading-row.child-of-red[data-red="${red}"]`).replaceWith(`<tr class="child-of-red" data-red="${red}"><td colspan="12" class="text-danger indent-1">Error al cargar ${red}. Intenta nuevamente.</td></tr>`);
                });
        });

        $(document).on('click', '.btn-micro', function () {
            const $tr = $(this).closest('tr');
            const red = $tr.data('red');
            const micro = $tr.data('micro');
            const expanded = Number($tr.attr('data-expanded')) === 1;
            const loaded = Number($tr.attr('data-loaded')) === 1;

            if (expanded) {
                $(`tr.child-of-micro[data-red="${red}"][data-micro="${micro}"]`).hide();
                $tr.attr('data-expanded', '0');
                $(this).text('+');
                return;
            }

            if (loaded) {
                $(`tr.child-of-micro[data-red="${red}"][data-micro="${micro}"]`).show();
                $tr.attr('data-expanded', '1');
                $(this).text('-');
                return;
            }

            const loading = `<tr class="loading-row child-of-micro" data-red="${red}" data-micro="${micro}"><td colspan="12" class="indent-2">Cargando IPRESS de ${micro}...</td></tr>`;
            $tr.after(loading);

            $.getJSON(`${urlMicroData}?${serializeForm()}&red=${encodeURIComponent(red)}&microred=${encodeURIComponent(micro)}`)
                .done(function (resp) {
                    $tr.attr('data-loaded', '1').attr('data-expanded', '1');
                    $tr.find('.btn-micro').text('-');

                    const rows = (resp.ipress || []).map(ip => buildIpressRow(red, micro, ip)).join('');
                    $(`tr.loading-row.child-of-micro[data-red="${red}"][data-micro="${micro}"]`).replaceWith(rows);
                    $(`tr.child-of-micro[data-red="${red}"][data-micro="${micro}"]`).show();
                })
                .fail(function () {
                    $(`tr.loading-row.child-of-micro[data-red="${red}"][data-micro="${micro}"]`).replaceWith(`<tr class="child-of-micro" data-red="${red}" data-micro="${micro}"><td colspan="12" class="text-danger indent-2">Error al cargar ${micro}.</td></tr>`);
                });
        });

    });
</script>
@endsection
