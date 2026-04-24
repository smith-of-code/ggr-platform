<p>Здравствуйте{{ $userName ? ', ' . e($userName) : '' }}!</p>
<p>Документ «{{ e($documentTypeLabel) }}» в личном кабинете туров был отклонён модератором. Загрузите исправленный файл в разделе «Документы».</p>
<p><strong>Комментарий модератора:</strong></p>
<p style="white-space: pre-wrap;">{{ e($adminComment) }}</p>
<p><a href="{{ $cabinetUrl }}">Открыть личный кабинет туров</a></p>
<p style="color:#6b7280;font-size:12px;margin-top:24px;">С уважением, команда {{ $mailFromName }}</p>
