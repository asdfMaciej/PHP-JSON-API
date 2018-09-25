<ul style="padding: 0; padding-left: 24px">
<li><a href="/" class="white">Strona główna</a></li><br>
<li><a href="/profile" class="white">Profil</a></li>
</ul>
<?php if ($user->admin ?? False): ?>
	Narzędzia administratorskie:
	<ul style="padding: 0; padding-left: 24px">
	<li><a href="/admin" class="white">Panel admina</a></li><br>
	<li><a href="/admin/logs" class="white">Logi</a></li>
	</ul>
<?php endif ?>