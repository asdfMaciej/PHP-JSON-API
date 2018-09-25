<div style="width: 100%; text-align: center">
	<a href="/admin/logs?after=<?=$after+70?>">Następne logi</a>
</div>
<table style="width: 100%">
	<tr>
		<th>ID</th>
		<th>Spełnia permisje</th>
		<th>Czas</th>
		<th>IP</th>
		<th>Call</th>
	</tr>
	<?php foreach ($logs as $l) : ?>
	<tr>
		<th><?=$l["id"]?></th>
		<th><?=$l["success"]?></th>
		<th><?=$l["timestamp"]?></th>
		<th><?=$l["ip"]?></th>
		<th><?=$l["api_call"]?></th>
	</tr>
	<?php endforeach ?>
</table>