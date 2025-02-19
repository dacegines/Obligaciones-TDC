<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .container {
            font-family: Arial, sans-serif;
                            max-width: 600px;
                            margin: auto;
                            padding: 20px;
                            background-color: #f8f9fa;
                            border-radius: 10px;
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #ced4da;
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            font-weight: bold;
        }
        .details-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .section-header {
            font-weight: bold;
            margin-top: 10px;
        }
        .info-section {
            padding: 10px 0;
        }
        .alert-message {
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            color: black;
            border: 1px solid black;
            background-color: {{ $colorFondo }};
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Fondo para conservación y mantenimiento</p>
        </div>
        <div class="details-card">
            <div class="info-section">
                <div class="container">
                    <div class="alert-message">
                        <i class="fas fa-check"></i> Faltan {{ $diasRestantes }} días para cumplir con esta obligación.
                    </div>
                    <hr>
                    <div class="section-header">📝 Obligación:</div>
                    <p>Reporte del costo de dos meses de los gastos y costos de mantenimiento y conservación de la vía, con firma de autorización del Director de Operación</p>
                    <div class="section-header">🗓 Periodicidad:</div>
                    <p>Bimestral</p>
                    <div class="section-header">👤 Responsable:</div>
                    <p>Gerente de Mantenimiento</p>
                    <div class="section-header">📅 Fechas límite de cumplimiento:</div>
                    <p>2024-02-28</p>
                    <div class="section-header">📄 Origen de la obligación:</div>
                    <p>Título concesión</p>
                    <div class="section-header">📜 Cláusula, condicionante, o artículo:</div>
                    <p style="text-align: justify;">Dentro de los 90 (noventa) días siguientes al Inicio de Operación de la Vía, la Concesionaria deberá constituir el fondo de conservación y mantenimiento en una cuenta por separado, la cual deberá mantener en todo momento con la cantidad equivalente correspondiente a los gastos y costos de Mantenimiento y Conservación que se requerirán para los 2 (dos) meses siguientes; para constituir dicho fondo, la Concesionaria deberá separar los recursos necesarios provenientes de la explotación de la Concesión y aportarlos bimestralmente al fondo de conservación y mantenimiento. Lo anterior, en el entendido, de que cualquier recurso restante en el fondo de conservación y mantenimiento, una vez aplicado a sus fines, será devuelto a la Concesionaria.</p>
                </div>
            </div>
        </div>
    </div>
    <div>
        <br>
        <p style="color:orange;">Antes de imprimir este mensaje en papel, piensa si es realmente necesario gastar esa hoja.</p>
        <p style="color:gray;">AVISO DE CONFIDENCIALIDAD Y PRIVACIDAD...</p>
        <p style="color:red;"><b>Este es un mensaje automático y no es necesario responder.</b></p>
    </div>
</body>
</html>
