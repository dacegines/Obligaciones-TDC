<!DOCTYPE html>
<html>
<head>
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
            background-color: {{ $color }};
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            font-weight: bold;
            color: black;
        }
        .details-card {
            padding: 20px;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 10px;
        }
        .confidentiality {
            color: gray;
            text-align: justify;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <p style="color: red; text-align: center;">¡Faltan {{ $dias_restantes }} días para cumplir esta obligación!</p>

        <div class="header">
            {{ $requisito->numero_evidencia }} - {{ $requisito->nombre }} - {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d/m/Y') }}


        </div>

        <div class="details-card">
            <div class="container">
                <hr>
                <p><strong>📝 Obligación:</strong></p>
                <p style="text-align: justify;">{{ $requisito->evidencia }}</p>

                <p><strong>👤 Responsable:</strong></p>
                <p>{{ $requisito->responsable }}</p>

                <p><strong>🗓 Fecha Límite:</strong></p>
                <p>{{ $requisito->fecha_limite_cumplimiento }}</p>

                <p><strong>📄 Origen:</strong></p>
                <p>{{ $requisito->origen_obligacion }}</p>

                <p><strong>📜 Cláusula:</strong></p>
                <p style="text-align: justify;">{{ $requisito->clausula_condicionante_articulo }}</p>
                <hr>
            </div>
        </div>

        <br>

        <p class="confidentiality">
            AVISO DE CONFIDENCIALIDAD Y PRIVACIDAD. Este correo electrónico y cualquier archivo adjunto al mismo puede contener datos y/o información confidencial, sometida a secreto profesional o cuya divulgación está prohibida en virtud de la legislación vigente. La información transmitida mediante el presente correo es para la(s) persona(s) cuya dirección aparece como destinatario y es estrictamente confidencial. Esta información no debe ser divulgada a ninguna persona sin autorización. Si ha recibido este correo por error o no es usted el destinatario al cual se pretende hacer llegar esta comunicación, por favor notifique al remitente de inmediato o a una persona responsable de hacerla llegar a su destinatario y elimine por completo este mensaje de su sistema.
        </p>

        <p class="confidentiality">
            Cualquier uso, distribución, divulgación, reproducción o retención de este mensaje o cualquier parte del mismo, o cualquier acción u omisión basada en el contenido de este correo electrónico está prohibida y puede ser ilegal.
        </p>

        <p class="confidentiality">
            La transmisión por vía electrónica no permite garantizar la confidencialidad de los mensajes que se transmiten, ni su integridad o correcta recepción, por lo que Operadora Vía Rápida Poetas, S.A.P.I. de C.V., y/o las empresas pertenecientes a dicho grupo empresarial no asumen responsabilidad alguna por estas circunstancias.
        </p>

        <br>

        {{-- <center>
            <a href="{{ env('APP_URL') }}" style="display: inline-block; padding: 10px 20px; color: white; background-color: #007bff; text-decoration: none; border-radius: 5px;">
                Ir al sistema
            </a>
        </center> --}}
    </div>
</body>
</html>
