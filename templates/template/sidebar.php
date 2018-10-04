<ul style="padding: 0; padding-left: 24px">
	<li><a href="/" class="white">Strona główna</a></li><br>
	<li><a href="/profile" class="white">Profil</a></li>
</ul>

<?php if (($user->class_id ?? 1) != 1): ?>
	<hr>
	<span style="font-size: 125%">Klasa <?=$user->class_name?>:</span>
	<ul style="padding: 0; padding-left: 24px">
		<li><a href="/class/<?=$user->class_id?>" class="white">Profil klasy</a></li><br>
		<li><a href="/posts/class/<?=$user->class_id?>" class="white">Posty</a></li><br>
		<li><a href="/posts/add" class="white">Dodaj post</a></li>
	</ul>
<?php endif ?>

<?php if ($user->admin ?? False): ?>
	<hr>
	<span style="font-size: 125%">Narzędzia administratorskie:</span>
	<ul style="padding: 0; padding-left: 24px">
		<li><a href="/admin" class="white">Panel admina</a></li><br>
		<li><a href="/admin/logs" class="white">Logi</a></li>
	</ul>
<?php endif ?>