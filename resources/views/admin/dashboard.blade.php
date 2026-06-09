@extends('admin.base')

@section('content')
    <div class="container mt-4">
        <h3>Bienvenido 👋</h3>
        @if(auth()->user()->is_admin)
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="annomes" class="form-label">Filtro ANNOMES</label>
                            <select name="annomes" id="annomes" class="form-select">
                                @forelse($mesesDisponibles as $mes)
                                    <option value="{{ $mes }}" @selected($annomes === $mes)>{{ $mes }}</option>
                                @empty
                                    <option value="{{ $annomes }}" selected>{{ $annomes }}</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted">Periodo seleccionado: <strong>{{ $annomes }}</strong></div>
                            <div class="small text-muted">Relación usada: almacenes.cod_ipress = form_det.CODIGO_PRE</div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div id="chart-avance"></div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3 shadow-sm">
                        <h5>Resumen del periodo</h5>
                        <p><strong>Almacenes que deben enviar:</strong> {{ $totalAlmacenes }}</p>
                        <p><strong>Almacenes que enviaron:</strong> {{ $enviaronEsteMes }}</p>
                        <p><strong>Avance:</strong> {{ $porcentaje }}%</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4 g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Almacenes pendientes de enviar</h5>
                            <p class="text-muted mb-3">No tienen registros en form_det para {{ $annomes }}.</p>

                            @if($almacenesPendientes->isEmpty())
                                <div class="alert alert-success mb-0">No hay almacenes pendientes para este periodo.</div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach($almacenesPendientes as $almacen)
                                        @php $detalleId = 'pendiente-' . $almacen->cod_ipress; @endphp
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $almacen->cod_ipress }} - {{ $almacen->nombre_ipress }}</div>
                                                    <div class="small text-muted">Pendiente de enviar</div>
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detalleId }}" aria-expanded="false" aria-controls="{{ $detalleId }}">
                                                    Ver detalles
                                                </button>
                                            </div>

                                            <div class="collapse mt-3" id="{{ $detalleId }}">
                                                <div class="border rounded p-3 bg-light">
                                                    <div><strong>Código:</strong> {{ $almacen->cod_ipress }}</div>
                                                    <div><strong>Nombre:</strong> {{ $almacen->nombre_ipress }}</div>
                                                    <div><strong>Estado:</strong> Sin envío registrado en {{ $annomes }}.</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Almacenes que ya enviaron</h5>
                            <p class="text-muted mb-3">Tienen registros en form_det para {{ $annomes }}.</p>

                            @if($almacenesEnviaron->isEmpty())
                                <div class="alert alert-warning mb-0">No hay almacenes con envíos registrados en este periodo.</div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach($almacenesEnviaron as $almacen)
                                        @php
                                            $detalleId = 'enviado-' . $almacen->cod_ipress;
                                            $detallesAlmacen = $detalleEnviosPorAlmacen[$almacen->cod_ipress] ?? collect();
                                        @endphp
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $almacen->cod_ipress }} - {{ $almacen->nombre_ipress }}</div>
                                                    <div class="small text-muted">{{ $detallesAlmacen->count() }} registros en form_det</div>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detalleId }}" aria-expanded="false" aria-controls="{{ $detalleId }}">
                                                    Ver detalles
                                                </button>
                                            </div>

                                            <div class="collapse mt-3" id="{{ $detalleId }}">
                                                @if($detallesAlmacen->isEmpty())
                                                    <div class="alert alert-light border mb-0">No se encontraron filas de detalle para este almacén.</div>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Código medicamento</th>
                                                                    <th class="text-end">SIS</th>
                                                                    <th class="text-end">Saldo</th>
                                                                    <th class="text-end">Stock final</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($detallesAlmacen as $detalle)
                                                                    <tr>
                                                                        <td>{{ $detalle->CODIGO_MED }}</td>
                                                                        <td class="text-end">{{ $detalle->SIS ?? '-' }}</td>
                                                                        <td class="text-end">{{ $detalle->SALDO ?? '-' }}</td>
                                                                        <td class="text-end">{{ $detalle->STOCK_FIN ?? '-' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-5 p-3 shadow-sm">
                <h5>Requerimientos por Almacén ({{ now()->translatedFormat('F Y') }})</h5>
                <div id="chart-requerimientos" style="height: 400px;"></div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- === GRÁFICO SEMICIRCULAR === --}}
    <script>
        var optionsAvance = {
            series: [{{ $porcentaje }}],
            chart: {
                height: 300,
                type: 'radialBar',
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#e7e7e7",
                        strokeWidth: '97%',
                        margin: 5,
                    },
                    dataLabels: {
                        name: { show: true, text: 'Avance', color: '#888', fontSize: '16px' },
                        value: {
                            show: true,
                            fontSize: '30px',
                            formatter: val => val + "%"
                        }
                    }
                }
            },
            fill: { colors: ['#00E396'] },
            labels: ['Avance']
        };

        new ApexCharts(document.querySelector("#chart-avance"), optionsAvance).render();
    </script>

    {{-- === GRÁFICO DE REQUERIMIENTOS === --}}
    <script>
        var optionsRequerimientos = {
            series: [{
                name: "Requerimientos",
                data: @json($data)
            }],
            chart: {
                type: 'bar',
                height: 400,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    distributed: true
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($labels),
                title: { text: 'Total Requerimiento (req_final)' }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " unidades";
                    }
                }
            },
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
            title: {
                text: "Establecimientos con requerimientos del mes",
                align: "center"
            }
        };

        new ApexCharts(document.querySelector("#chart-requerimientos"), optionsRequerimientos).render();
    </script>
@endsection
