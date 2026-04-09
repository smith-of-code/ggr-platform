<p>Здравствуйте{{ $userName ? ', ' . e($userName) : '' }}!</p>
<p>По событию «{{ e($eventTitle) }}» документ «{{ e($documentTypeLabel) }}» был аннулирован модератором. Загрузите файл заново в личном кабинете.</p>
<p><strong>Комментарий модератора:</strong></p>
<p style="white-space: pre-wrap;">{{ e($adminComment) }}</p>
<p><a href="{{ $profileUrl }}">Открыть профиль</a></p>
