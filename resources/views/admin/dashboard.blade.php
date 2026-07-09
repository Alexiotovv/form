@extends('admin.base')

@section('content')
    <div class="container mt-4">
        <h3>Bienvenido👋</h3>
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

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">
                                <div>
                                    <h5 class="card-title mb-2">Estado de envíos</h5>
                                    <p class="text-muted mb-0">Resumen simple de envíos para {{ $annomes }}.</p>
                                </div>
                                <div class="d-flex flex-column flex-sm-row gap-3">
                                    <div class="p-3 bg-light rounded shadow-sm text-center" style="min-width:180px;">
                                        <div class="text-uppercase text-muted small">Pendientes</div>
                                        <div class="fs-3 fw-bold">{{ $almacenesPendientesCount }}</div>
                                        <div class="text-muted">Almacenes sin envío</div>
                                    </div>
                                    <div class="p-3 bg-light rounded shadow-sm text-center" style="min-width:180px;">
                                        <div class="text-uppercase text-muted small">Enviaron</div>
                                        <div class="fs-3 fw-bold">{{ $enviaronEsteMes }}</div>
                                        <div class="text-muted">Almacenes con envío registrado</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 p-3 shadow-sm">
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

    <script>
        // El dashboard simplificado no requiere carga dinámica de detalles.
    </script>
@endsection
