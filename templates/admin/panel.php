<form method="post" action="/admin/panel">
	<table style="width: 100%">
		<tr>
			<th>ID</th>
			<th>Nick</th>
			<th>Imię</th>
			<th>Nazwisko</th>
			<th>IP rejestracji</th>
			<th>Aktywny</th>
			<th>Admin</th>
			<th>Zaznacz użytkownika</th>
		</tr>
		<?php
		foreach ($users as $user):
			$aktywny = $user["active"] == 1 ? "checked" : "";
			$admin = $user["admin"] == 1 ? "checked" : "";
		?>
		<tr>
			<th><?=$user["id"]?></th>
			<th><?=$user["nick"]?></th>
			<th><?=$user["first_name"]?></th>
			<th><?=$user["last_name"]?></th>
			<th><?=$user["register_ip"]?></th>
			<th><input type="checkbox" onclick="return false;" <?=$aktywny?>></th>
			<th><input type="checkbox" onclick="return false;" <?=$admin?>></th>
			<th><input type="checkbox" name="user_id[]" value="<?=$user["id"]?>"/></th>
		</tr>
		<?php endforeach ?>
	</table>
	<div>
		<label>Imie: <input type="text" name="fname"></label><br>
		<label>Nazwisko: <input type="text" name="lname"></label><br>
		<button type="submit" name="action" value="activate">Aktywuj konta</button>
		<button type="submit" name="action" value="deactivate">Dezaktywuj konta</button>
		<button type="submit" name="action" value="delete">USUŃ KONTA</button>
		<button type="submit" name="action" value="rename">Zmien imie/nazwisko</button>
	</div>
</form>