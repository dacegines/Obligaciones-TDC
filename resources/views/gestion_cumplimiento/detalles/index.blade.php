@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
    <hr>
    <div class="card">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Detalle de Obligaciones TDC</h4>
        </div>

        <div class="card-body">
            <div class="row justify-content-center">
                <form id="filtro-form" action="{{ route('filtrar-detalle') }}" method="POST"
                    class="form-inline d-flex align-items-center">
                    @csrf
                    <div class="form-group mr-2">
                        <label for="year-select" class="mr-2">Año:</label>
                        <select id="year-select" name="year" class="form-control form-control-sm">
                            @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                                <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm"
                        @if (Auth::user()->hasRole('invitado')) disabled @endif>Ver</button>

                </form>
            </div>
            <div class="container-fluit text-center">
                @if (Auth::user()->hasRole('invitado'))
                    <p class="text-center text-muted" style="font-size: 1.0rem;">
                        <b>Actualmente eres un usuario invitado y solo tienes acceso a esta información.</b>
                    </p>
                @endif

            </div>
            <div class="divider"></div>

            @if (isset($error))
                <div class="text-center mt-4">
                    <h5>{{ $error }}</h5>
                    <br>
                </div>
            @endif
            <div class="table-responsive">
                <table id="detallesTable" class="table table-striped table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th style="font-size: 12px;">#Obligación</th>
                            <th style="font-size: 12px;">Cláusula</th>
                            <th style="font-size: 12px;">Obligación</th>
                            <th style="font-size: 12px;">Periodicidad</th>
                            <th style="font-size: 12px;">Adjuntos</th>
                            <th style="font-size: 12px;">Fecha límite</th>
                            <th style="font-size: 12px;">Responsable</th>
                            <th style="font-size: 12px;">Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitos as $requisito)
                            <tr>
                                <td style="font-size: 11px;">{{ $requisito->numero_evidencia }}</td>
                                <td style="text-align: justify; font-size: 11px;">{{ $requisito->clausula }}</td>
                                <td style="text-align: justify; font-size: 11px;">{{ $requisito->requisito_evidencia }}</td>
                                <td style="font-size: 11px;">{{ $requisito->periodicidad }}</td>
                                <td class="adjuntos-link" data-fecha="{{ $requisito->fecha_limite_cumplimiento }}"
                                    style="font-size: 11px;">
                                    @if (Auth::user()->hasRole('invitado'))
                                        <span class="text-muted" style="font-size: 11px;">Sin acceso</span>
                                    @else
                                        @if ($requisito->cantidad_archivos > 0)
                                            <span
                                                style="cursor: pointer; color: blue; text-decoration: underline; font-size: 11px;">
                                                {{ $requisito->cantidad_archivos }}
                                                archivo{{ $requisito->cantidad_archivos > 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span style="font-size: 11px;">No hay adjuntos</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="font-size: 11px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
                                </td>
                                <td style="font-size: 11px;">{{ $requisito->responsable }}</td>
                                <td>
                                    <span style="font-size: 11px;"
                                        class="badge badge-{{ $requisito->estatus === 'Cumplido' ? 'success' : ($requisito->estatus === 'Vencido' ? 'danger' : ($requisito->estatus === 'Próximo' ? 'warning' : 'info')) }}">
                                        {{ $requisito->estatus }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar los archivos -->
    <div class="modal fade" id="modalArchivos" tabindex="-1" role="dialog" aria-labelledby="modalArchivosLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Archivos Adjuntos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="lista-archivos"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/gestion_obligaciones/detalles/detalles.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('.adjuntos-link').on('click', function() {
                const fechaLimite = $(this).data('fecha');

                axios.get(`{{ url('/obtener-archivos') }}/${fechaLimite}`)
                    .then(response => {
                        const archivos = response.data;
                        $('#lista-archivos').empty();
                        if (archivos.length > 0) {
                            archivos.forEach((archivo, index) => {

                                const nombreVisible = archivo.nombre_archivo.substring(archivo
                                    .nombre_archivo.indexOf('_') + 1);


                                $('#lista-archivos').append(`
                        <li>
                            <a href="{{ asset('storage') }}/${archivo.ruta_archivo}" target="_blank" style="color: blue; text-decoration: underline;">
                                ${nombreVisible}
                            </a>
                        </li>
                    `);
                            });
                        } else {
                            $('#lista-archivos').append('<li>No hay archivos adjuntos</li>');
                        }
                        $('#modalArchivos').modal('show');
                    })
                    .catch(error => {
                        console.error('Error al obtener los archivos:', error);
                        $('#lista-archivos').empty().append('<li>Error al cargar los archivos</li>');
                    });
            });

        });
    </script>
    <script>
        const mostrarBotonPDF =
            @json($mostrarBotonPDF ?? false);


        const descargarPdfUrl = "{{ route('descargar.pdf') }}";
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@stop
