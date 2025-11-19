@extends('admin.base')

@section('content')
    <div class="container mt-4">
        <h3>Bienvenido 👋</h3>

        <div class="row mt-4">
            <div class="col-md-6">
                <div id="chart-avance"></div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <h5>Resumen del mes</h5>
                    <p><strong>Almacenes que deben enviar:</strong> {{ $totalAlmacenes }}</p>
                    <p><strong>Almacenes que enviaron:</strong> {{ $enviaronEsteMes }}</p>
                    <p><strong>Avance:</strong> {{ $porcentaje }}%</p>
                </div>
            </div>
        </div>

        <div class="card mt-5 p-3 shadow-sm">
            <h5>Requerimientos por Almacén ({{ now()->translatedFormat('F Y') }})</h5>
            <div id="chart-requerimientos" style="height: 400px;"></div>
        </div>
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
