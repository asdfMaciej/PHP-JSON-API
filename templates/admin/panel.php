<form method="post" action="/admin/panel">
	<table style="width: 100%">
		<tr>
			<th>ID</th>
			<th>Klasa</th>
			<th>Nick</th>
			<th>Imię</th>
			<th>Nazwisko</th>
			<th>IP rejestracji</th>
			<th>Aktywny</th>
			<th>Admin</th>
			<th>Nauczyciel</th>
			<th>Zaznacz użytkownika</th>
		</tr>
		<?php
		foreach ($users as $user):
			$aktywny = $user["active"] == 1 ? "checked" : "";
			$admin = $user["admin"] == 1 ? "checked" : "";
			$nauczyciel = $user["teacher"] == 1 ? "checked" : "";
		?>
		<tr>
			<th><?=$user["id"]?></th>
			<th><?=$user["class_name"]?></th>
			<th><?=$user["nick"]?></th>
			<th><?=$user["first_name"]?></th>
			<th><?=$user["last_name"]?></th>
			<th><?=$user["register_ip"]?></th>
			<th><input type="checkbox" onclick="return false;" <?=$aktywny?>></th>
			<th><input type="checkbox" onclick="return false;" <?=$admin?>></th>
			<th><input type="checkbox" onclick="return false;" <?=$nauczyciel?>></th>
			<th><input type="checkbox" name="user_id[]" value="<?=$user["id"]?>"/></th>
		</tr>
		<?php endforeach ?>
	</table>
	<div>
		<label>Imie: <input type="text" name="fname"></label><br>
		<label>Nazwisko: <input type="text" name="lname"></label><br>
		<label>Klasa: </label>
		<select name="class">
			<?php foreach ($classes as $c): ?>
				<option value="<?=$c["id"]?>"><?=$c["name"]?></option>
			<?php endforeach ?>
		</select><br>
		<button type="submit" name="action" value="activate">Aktywuj konta</button>
		<button type="submit" name="action" value="deactivate">Dezaktywuj konta</button>
		<button type="submit" name="action" value="teacher">Nadaj uprawnienia nauczyciela</button>
		<button type="submit" name="action" value="deteacher">Zabierz uprawnienia nauczyciela</button>
		<button type="submit" name="action" value="delete">USUŃ KONTA</button>
		<button type="submit" name="action" value="rename">Zmien imie/nazwisko</button>
		<button type="submit" name="action" value="class">Zmień klasę</button>
	</div>
</form>