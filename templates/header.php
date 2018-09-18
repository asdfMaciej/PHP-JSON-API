<div id="header">
	<a href="/" style="color: #F0F0F0;"><b>Kaszkowiak</b></a>
	<div style="float: right; text-align: right; margin-right: 3%; font-size: 35%;">
		<?php if ($user): ?>
			<?=$user->first_name?> <?=$user->last_name?>
		<?php endif ?>
		<?php
			$this->nest("forms/login.php", [
				"user" => $user
			]);
		?>
	</div>
</div>

<div id="content">
