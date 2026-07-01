<?php

function mostrarError(Throwable $e): void
{
    echo '<div class="error-box">';
    echo '<h3>Error detectado</h3>';
    echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Línea:</strong> ' . htmlspecialchars((string) $e->getLine()) . '</p>';
    echo '</div>';
}