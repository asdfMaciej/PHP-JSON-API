
<div style="width: 100%; text-align: center; background-color: #212121; color: #F0F0F0; min-height: 10vw; padding: 8px">
	<span style="font-size: 5vw;">
		<?=$user->nick?>
	</span><br>
	<span style="font-size: 3vw;">
		<?=$user->first_name?> <?=$user->last_name?><br>
		<?=$user->email?>
	</span>
</div>

<div style="display: flex; flex-wrap: wrap; padding: 4px">
	<div style="flex-grow: 1; width: 33%; height: 5vw; text-align: center">
		<?php if ($user->active): ?>
			✔ Konto aktywne
		<?php else: ?>
			✖ Konto nieaktywne. Skontaktuj się z administratorem.
		<?php endif ?>
	</div>

	<div style="flex-grow: 1; width: 33%; height: 5vw; text-align: center">
		<?php if ($user->admin): ?>
			✔ Administrator
		<?php else: ?>
			✖ Zwykły użytkownik
		<?php endif ?>
	</div>

	<div style="flex-grow: 1; width: 33%; height: 5vw; text-align: center">
		Dupatest
	</div>
</div>