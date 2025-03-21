@extends('adminlte::page')

@section('title', 'Resumen')

@section('content')
    <hr>
    <div class="card" id="card-resumen">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Dashboard de Obligaciones TDC</h4>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <form id="filter-form" action="{{ route('filtrar-requisitos') }}" method="POST"
                    class="form-inline d-flex align-items-center justify-content-center">
                    @csrf
                    <label for="year-select" class="mr-2">Año:</label>
                    <select id="year-select" name="year" class="form-control form-control-sm">
                        @if (Auth::user()->hasRole('invitado'))
                            <option value="2024" selected>2024</option>
                        @else
                            @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                                <option value="{{ $yearOption }}"
                                    {{ isset($year) && $year == $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endfor
                        @endif
                    </select>

                    <!-- Botón para "Ver" -->
                    <button type="submit" class="btn btn-success btn-sm ml-2"
                        @if (Auth::user()->hasRole('invitado')) disabled @endif>
                        Ver
                    </button>

                    <!-- Botón para "Descargar PDF" -->
                    <button type="button" onclick="descargarPDF()" class="btn btn-danger btn-sm ml-2"
                        title="Exportar en PDF" data-toggle="tooltip" @if (!$mostrarBotonPDF) disabled @endif>
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <!-- Campos ocultos para las imágenes de los gráficos -->
                    <input type="hidden" name="chartImageAvanceObligaciones" id="chartImageAvanceObligaciones">
                    <input type="hidden" name="chartImageAvanceTotal" id="chartImageAvanceTotal">
                    <input type="hidden" name="chartImageEstatusGeneral" id="chartImageEstatusGeneral">
                </form>
            </div>


            <div class="container-fluit text-center">
                @if (Auth::user()->hasRole('invitado'))
                    <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y
                            solo tienes acceso a esta información.</b></p>
                @endif
            </div>

            <hr class="divider">
            @if (isset($error))
                <div class="text-center mt-4">
                    <h5>{{ $error }}</h5>
                    <br>
                </div>
            @endif

            {{-- <div class="row text-center justify-content-center">
                @foreach ([['icon' => 'fa-tasks', 'color' => 'primary', 'title' => 'Obligaciones', 'id' => 'total_obligaciones', 'value' => e($totalObligaciones), 'modal' => 'detailsModal'], ['icon' => 'fa-comments', 'color' => 'info', 'title' => 'Activas', 'id' => 'activas', 'value' => e($activas), 'modal' => 'detailsModalA'], ['icon' => 'fa-check', 'color' => 'success', 'title' => 'Completas', 'id' => 'completas', 'value' => e($completas), 'modal' => 'detailsModalC'], ['icon' => 'fa-times-circle', 'color' => 'danger', 'title' => 'Vencidas', 'id' => 'vencidas', 'value' => e($vencidas), 'modal' => 'detailsModalV'], ['icon' => 'fa-clock', 'color' => 'warning', 'title' => 'Por vencer', 'id' => 'por_vencer', 'value' => e($porVencer), 'modal' => 'detailsModalP']] as $card)
                    <div class="col-md-2">
                        <div class="card metric-card">
                            <div class="card-total-detail card-header bg-light text-white text-center">
                                <i
                                    class="icon-card-resumen fas {{ e($card['icon']) }} fa-3x text-{{ e($card['color']) }}"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ e($card['title']) }}:</h5>
                                <p id="{{ e($card['id']) }}" class="card-text display-4 font-weight-bold">
                                    {{ e($card['value']) }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal"
                                    data-target="#{{ e($card['modal']) }}"><b>Ver Detalles</b></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div> --}}
            <div class="row text-center justify-content-center">
                <!-- Tarjeta de Obligaciones -->
                <div class="col-lg col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ e($totalObligaciones) }}</h3>
                            <p>Obligaciones</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#detailsModal">
                            Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta de Activas -->
                <div class="col-lg col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ e($activas) }}</h3>
                            <p>Activas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#detailsModalA">
                            Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta de Completas -->
                <div class="col-lg col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ e($completas) }}</h3>
                            <p>Completas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#detailsModalC">
                            Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta de Vencidas -->
                <div class="col-lg col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ e($vencidas) }}</h3>
                            <p>Vencidas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#detailsModalV">
                            Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta de Por Vencer -->
                <div class="col-lg col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ e($porVencer) }}</h3>
                            <p>Por Vencer</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#detailsModalP">
                            Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header-title card-header bg-success text-white text-center">
                            <h5 class="card-title-description">Avance de Obligaciones</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="barChartObligaciones"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header-title card-header bg-success text-white text-center">
                            <h5 class="card-title-description">Avance Total</h5>
                        </div>
                        <div class="card-body text-center">
                            <canvas id="avanceTotalChart" style="width: 100%; height: 228px;"></canvas>
                        </div>
                        <div class="card-body" id="tablasPeriodicidad"></div>
                    </div>
                </div>
            </div>
            <div class="row" id="chartContainer">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header-title card-header bg-success text-white text-center">
                            <h5 class="card-title-description">Estatus General</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="vencidasPorVencerCompletasChart" style="width: 100%; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @foreach ([['id' => 'detailsModal', 'title' => 'Obligaciones', 'requisitos' => $requisitos], ['id' => 'detailsModalA', 'title' => 'Obligaciones - Activas', 'requisitos' => $requisitosActivos], ['id' => 'detailsModalC', 'title' => 'Obligaciones - Completas', 'requisitos' => $requisitosCompletos], ['id' => 'detailsModalV', 'title' => 'Obligaciones - Vencidas', 'requisitos' => $requisitosVencidos], ['id' => 'detailsModalP', 'title' => 'Obligaciones - Por Vencer', 'requisitos' => $requisitosPorVencer]] as $modal)
        <div class="modal fade" id="{{ e($modal['id']) }}" tabindex="-1" role="dialog"
            aria-labelledby="{{ e($modal['id']) }}Label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="{{ e($modal['id']) }}Label">{{ e($modal['title']) }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#Registros</th>
                                        <th>Obligación</th>
                                        <th>Responsable</th>
                                        <th>Periodicidad</th>
                                        <th>Fecha Límite</th>
                                    </tr>
                                </thead>
                                <tbody id="modalContent">
                                    @if (Auth::user()->hasRole('invitado'))
                                        <!-- Mostrar solo 3 registros si es invitado -->
                                        @foreach ($modal['requisitos']->take(3) as $index => $requisito)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ e($requisito->evidencia) }}</td>
                                                <td>{{ e($requisito->responsable) }}</td>
                                                <td>{{ e($requisito->periodicidad) }}</td>
                                                <td
                                                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d-m-Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="5" class="text-center text-muted" style="font-size: 1.0rem;">
                                                Actualmente eres un usuario invitado y no tienes acceso a toda la
                                                información.
                                            </td>
                                        </tr>
                                    @else
                                        <!-- Mostrar todos los registros si no es invitado -->
                                        @foreach ($modal['requisitos'] as $index => $requisito)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ e($requisito->evidencia) }}</td>
                                                <td>{{ e($requisito->responsable) }}</td>
                                                <td>{{ e($requisito->periodicidad) }}</td>
                                                <td
                                                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d-m-Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/resumen/styles.css') }}">
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>




    <script>
        function generarPDF() {

            const {
                jsPDF
            } = window.jspdf;


            const elemento = document.getElementById('card-resumen');


            html2canvas(elemento).then(canvas => {
                const imgData = canvas.toDataURL('image/png');


                const pdf = new jsPDF();
                const imgWidth = 190;
                const pageHeight = pdf.internal.pageSize.height;
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;


                pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;


                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }


                pdf.save('reporte.pdf');
            });
        }
    </script>



    <script>
        window.chartData = {
            fechas: @json($fechasAgrupadas),
            vencidas: @json($vencidasAgrupadas),
            porVencer: @json($porVencerAgrupadas),
            completas: @json($completasAgrupadas),
            nombres: @json($nombres),
            avancesTotales: @json($avancesTotales)
        };



        window.chartData.avancesTotales = window.chartData.avancesTotales.map(avance => {

            avance = typeof avance === 'number' ? avance : 0;
            avance = parseFloat(avance.toFixed(2));
            return (avance >= 99.95 && avance <= 100.05) ? 100 : avance;
        });


        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('barChartObligaciones').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($nombres),
                    datasets: [{
                        label: 'Total Avance',
                        data: window.chartData.avancesTotales,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                display: true
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                },
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: true
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            right: 40
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            formatter: function(value) {
                                return Math.round(value) + '%';
                            },
                            color: 'black',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });



            new Chart(document.getElementById('avanceTotalChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Avance', 'Restante'],
                    datasets: [{
                        data: [{{ $porcentajeAvance }}, 100 - {{ $porcentajeAvance }}],
                        backgroundColor: ['#36a2eb', '#d3d3d3'],
                        hoverBackgroundColor: ['#36a2eb', '#d3d3d3']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + '%';
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            formatter: function(value, context) {
                                return context.dataIndex === 0 ? value + '%' : '';
                            },
                            color: '#000000',
                            font: {
                                size: '30',
                                weight: 'bold'
                            },
                            anchor: 'center',
                            align: 'center'
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });




            new Chart(document.getElementById('vencidasPorVencerCompletasChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($fechasAgrupadas),
                    datasets: [{
                            label: 'Vencidas',
                            data: @json($vencidasAgrupadas),
                            borderColor: '#ff6384',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: true
                        },
                        {
                            label: 'Por Vencer',
                            data: @json($porVencerAgrupadas),
                            borderColor: '#ffcd56',
                            backgroundColor: 'rgba(255, 205, 86, 0.2)',
                            fill: true
                        },
                        {
                            label: 'Completas',
                            data: @json($completasAgrupadas),
                            borderColor: '#4bc0c0',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true
                        }
                    ]
                },
                options: {
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: true
                            }
                        },
                        y: {

                            grace: '10%',
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 14
                                }
                            },
                            grid: {
                                display: true
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            bodyFont: {
                                size: 14
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value, context) {
                                return value;
                            },
                            color: '#000',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuad'
                    }
                },
                plugins: [ChartDataLabels]
            });

            document.getElementById('tablasPeriodicidad').innerHTML = `
<div class="row d-flex justify-content-center">
    @if ($mostrarBimestral)
    <div class="col-md-4">
        <table class="table table-bordered text-center" style="font-size: 0.8rem;">
            <thead class="thead-light">
                <tr><th>Periodo</th><th>Avance (%)</th></tr>
            </thead>
            <tbody>
                <tr><td>{{ e($bimestral->periodicidad ?? 'Bimestral') }}</td><td>{{ e($bimestral->avance ?? 0) }}%</td></tr>
            </tbody>
        </table>
    </div>
    @endif

    @if ($mostrarSemestral)
    <div class="col-md-4">
        <table class="table table-bordered text-center" style="font-size: 0.8rem;">
            <thead class="thead-light">
                <tr><th>Periodo</th><th>Avance (%)</th></tr>
            </thead>
            <tbody>
                <tr><td>{{ e($semestral->periodicidad ?? 'Semestral') }}</td><td>{{ e($semestral->avance ?? 0) }}%</td></tr>
            </tbody>
        </table>
    </div>
    @endif

    @if ($mostrarAnual)
    <div class="col-md-4">
        <table class="table table-bordered text-center" style="font-size: 0.8rem;">
            <thead class="thead-light">
                <tr><th>Periodo</th><th>Avance (%)</th></tr>
            </thead>
            <tbody>
                <tr><td>{{ e($anual->periodicidad ?? 'Anual') }}</td><td>{{ e($anual->avance ?? 0) }}%</td></tr>
            </tbody>
        </table>
    </div>
    @endif
</div>`;
        });
    </script>

    <script>
        function getChartAsBase64(chartId) {
            const canvas = document.getElementById(chartId);
            if (!canvas) {
                console.error(`Canvas con ID '${chartId}' no encontrado.`);
                return null;
            }
            return canvas.toDataURL('image/png');
        }

        function descargarPDF() {

            const chartImageAvanceObligaciones = getChartAsBase64('barChartObligaciones');
            const chartImageAvanceTotal = getChartAsBase64('avanceTotalChart');
            const chartImageEstatusGeneral = getChartAsBase64(
                'vencidasPorVencerCompletasChart');


            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('descargar-pdf') }}";
            form.target = "_blank";


            const csrfTokenInput = document.createElement('input');
            csrfTokenInput.type = 'hidden';
            csrfTokenInput.name = '_token';
            csrfTokenInput.value = "{{ csrf_token() }}";
            form.appendChild(csrfTokenInput);

            const yearInput = document.createElement('input');
            yearInput.type = 'hidden';
            yearInput.name = 'year';
            yearInput.value = "{{ $year }}";
            form.appendChild(yearInput);

            const userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'user_id';
            userIdInput.value = "{{ $user_id }}";
            form.appendChild(userIdInput);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = "{{ $status }}";
            form.appendChild(statusInput);


            if (chartImageAvanceObligaciones) {
                const chartImageAvanceObligacionesInput = document.createElement('input');
                chartImageAvanceObligacionesInput.type = 'hidden';
                chartImageAvanceObligacionesInput.name = 'chartImageAvanceObligaciones';
                chartImageAvanceObligacionesInput.value = chartImageAvanceObligaciones;
                form.appendChild(chartImageAvanceObligacionesInput);
            }

            if (chartImageAvanceTotal) {
                const chartImageAvanceTotalInput = document.createElement('input');
                chartImageAvanceTotalInput.type = 'hidden';
                chartImageAvanceTotalInput.name = 'chartImageAvanceTotal';
                chartImageAvanceTotalInput.value = chartImageAvanceTotal;
                form.appendChild(chartImageAvanceTotalInput);
            }

            if (chartImageEstatusGeneral) {
                const chartImageEstatusGeneralInput = document.createElement('input');
                chartImageEstatusGeneralInput.type = 'hidden';
                chartImageEstatusGeneralInput.name = 'chartImageEstatusGeneral';
                chartImageEstatusGeneralInput.value = chartImageEstatusGeneral;
                form.appendChild(chartImageEstatusGeneralInput);
            } else {
                console.error("La imagen de la gráfica Estatus General no se pudo capturar.");
            }


            document.body.appendChild(form);
            form.submit();

            document.body.removeChild(form);
        }
    </script>
    <script>
        $(document).ready(function() {

            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
