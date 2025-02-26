<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Obligaciones TDC</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; font-size: 5px; }
        strong{font-size: 12px;}
        h2, h3 { text-align: center; font-size: 18px; margin-top: 0px; }
        .content { margin: 20px; font-size: 16px; }
        .chart { text-align: center; margin-top: 10px; }
        .chart img { width: 95%;  }

        /* Estilos para la tabla de estadísticas */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin: 20px 0;
        }

        .stats-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 0.9rem;
            vertical-align: top;
        }

        .stats-table td strong {
            display: block;
            margin-bottom: 4px;
        }

        /* Contenedor principal que agrupa la gráfica y la tabla */
        .container {
            display: flex;
            flex-direction: column; /* Cambia a columna para alinear elementos centrados verticalmente */
            align-items: center; /* Centra los elementos horizontalmente */
            margin-top: 20px;
        }

        .chartImageAvanceObligaciones {
            width: 70%;
            margin: 0px auto;
        }	

        /* Ajusta el tamaño de la gráfica de Avance Total */
        .chartImageAvanceTotal {
            width: 70%;
            margin: 0px auto;
        }

        .chartImageEstatusGeneral {
            width: 100%;
            margin: 0px auto;
        }

        /* Ajustes para la tabla de Periodicidad */
        .table-container {
            margin-top: 10px; /* Espacio entre la gráfica y la tabla */
            display: flex;
            justify-content: center; /* Centra la tabla */
            width: auto; /* Permite que la tabla ocupe solo el espacio necesario */
        }

        .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 0.9rem;
            text-align: center;
            vertical-align: top;
        }
        .header-logo {
        text-align: left; /* Alinea la imagen a la izquierda */
        margin-left: -15px; /* Ajusta la distancia desde el borde izquierdo */
        margin-bottom: 10px; /* Espacio entre el logo y el título */
        margin-top: -25px
    }

    .header-logo img {
        width: 150px; /* Ajusta el tamaño según tu necesidad */
    }

    h2 {
        text-align: center; /* Mantiene el título centrado */
        font-size: 18px;
        margin-top: 0px;
    }
    </style>
</head>
<body>
    <div class="header-logo">
        <img src="{{ public_path('img/logo_supervia.png') }}" alt="Logo">
    </div>
    <h2>Resumen de Obligaciones TDC del Año {{ $year }}</h2>
{{-- Validación para mostrar el puesto solo si no está en la lista especificada --}}
@php
    $puestosExcluidos = [
        'Director Jurídico',
        'Directora General',
        'Jefa de Cumplimiento',
        'Director de Finanzas',
        'Director de Operación',
        'Invitado'
    ];
@endphp

@if (!in_array($userPuesto, $puestosExcluidos))
    <p style="font-size: 12px; text-align: center;">
        <strong>{{ $userPuesto }}</strong>
    </p>
@endif

    <div class="content">
        <table class="stats-table">
            <tr>
                <td><strong>Total de Obligaciones: {{ $totalObligaciones }}</strong></td>
                <td><strong>Activas: {{ $activas }}</strong></td>
                <td><strong>Completas: {{ $completas }}</strong></td>
                <td><strong>Vencidas: {{ $vencidas }}</strong></td>
                <td><strong>Por Vencer: {{ $porVencer }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Gráfica de Avance de Obligaciones -->
    <div class="chart chartImageAvanceObligaciones">
        <h3>Avance de Obligaciones</h3>
        @if(!empty($chartImageAvanceObligaciones))
            <img src="{{ $chartImageAvanceObligaciones }}" alt="Gráfica de Avance de Obligaciones">
        @else
            <p>No se pudo cargar la gráfica de Avance de Obligaciones.</p>
        @endif
    </div>

    <div class="container">
        <!-- Gráfica de Avance Total -->
        <div class="chart  chartImageAvanceTotal">
            <h3>Avance Total</h3>
            @if(!empty($chartImageAvanceTotal))
                <img src="{{ $chartImageAvanceTotal }}" alt="Gráfica de Avance Total">
            @else
                <p>No se pudo cargar la gráfica de Avance Total.</p>
            @endif
        </div>
    
        <!-- Tablas de Periodicidad -->
        <div class="content table-container box">
            <table class="stats-table">
                <tr>
                    @if($mostrarBimestral)
                        <td>
                            <strong>{{ $bimestral->periodicidad ?? 'Bimestral' }}:
                             {{ number_format($bimestral->avance ?? 0, 2) }}%</strong>
                        </td>
                    @endif

                    @if($mostrarSemestral)
                        <td>
                            <strong>{{ $semestral->periodicidad ?? 'Semestral' }}:
                             {{ number_format($semestral->avance ?? 0, 2) }}%</strong>
                        </td>
                    @endif

                    @if($mostrarAnual)
                        <td>
                            <strong>{{ $anual->periodicidad ?? 'Anual' }}:
                             {{ number_format($anual->avance ?? 0, 2) }}%</strong>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    <!-- Gráfica de Estatus General -->
    <div class="chart chartImageEstatusGeneral">
        <h3>Estatus General</h3>
        @if(!empty($chartImageEstatusGeneral))
            <img src="{{ $chartImageEstatusGeneral }}" alt="Gráfica de Estatus General">
        @else
            <p>No se pudo cargar la gráfica de Estatus General.</p>
        @endif
    </div>

 
</body>
</html>
