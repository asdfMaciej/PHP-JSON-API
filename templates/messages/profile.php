
<div style="width: 100%; text-align: center; background-color: #212121; color: #F0F0F0; min-height: 10vw; padding: 8px">
	<span style="font-size: 5vw;">
		<?=$user->first_name?> <?=$user->last_name?><br>
	</span>
	<span style="font-size: 3vw;">
		Klasa: <?=$user->class_name?>
		
	</span>
</div>

<div style="display: flex; flex-wrap: wrap; font-size: 125%">
	<div style="flex-grow: 1; flex-basis: 33%; min-height: 5vw; text-align: center">
		<?php if ($user->active): ?>
			✔ Konto aktywne
		<?php else: ?>
			✖ Konto nieaktywne. Skontaktuj się z administratorem.
		<?php endif ?>
	</div>

	<div style="flex-grow: 1; flex-basis: 33%; min-height: 5vw; text-align: center">
		<?php if ($user->admin): ?>
			✔ Administrator
		<?php else: ?>
			✖ Zwykły użytkownik
		<?php endif ?>
	</div>

	<div style="flex-grow: 1; flex-basis: 33%; min-height: 5vw; text-align: center">
		<?php if ($user->teacher): ?>
			✔ Nauczyciel
		<?php else: ?>
			✖ Uczeń
		<?php endif ?>
	</div>
</div>

<div style="font-size: 120%; text-align: center">
<b>Email:</b> <?=$user->email?><br>
<b>Nick:</b> <?=$user->nick?><br>
<b>Założenie konta:</b> <span class="epoch"><?=$user->register_timestamp?></span>
</div>