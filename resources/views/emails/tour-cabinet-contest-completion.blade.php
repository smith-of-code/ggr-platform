<p>Здравствуйте{{ $userName ? ', ' . e($userName) : '' }}!</p>
<p style="white-space: pre-wrap;">{{ $body }}</p>
<p><a href="{{ $cabinetUrl }}">Открыть личный кабинет туров</a></p>
<p style="color:#6b7280;font-size:12px;margin-top:24px;">С уважением, команда {{ $mailFromName }}</p>
