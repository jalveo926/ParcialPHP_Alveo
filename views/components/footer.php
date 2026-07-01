<?php
$anio = date('Y');
?>

<footer>
	<p class="footer-copy">© <?= $anio ?> iTECH Contrataciones. All rights reserved.</p>
	<p class="footer-contacto">Contacto: <?= htmlspecialchars(Config::APP_EMAIL) ?> | <?= htmlspecialchars(Config::APP_PHONE) ?></p>
</footer>
