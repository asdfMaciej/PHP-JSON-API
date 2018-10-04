<?php
$b_submit = $form_action == "add" ? "Dodaj post" : "";
$b_submit = $form_action == "edit" ? "Edytuj post" : $b_submit;
?>
<form action="<?=$form_href?>" method="post" style="margin: 0px; padding: 0px">
	<input type="hidden" name="action" value="<?=$form_action?>">

	<label><input name="title" type="text" style="width: 100%" value="<?=$title ?? ""?>" placeholder="Napisz tytuł. 12-160 znaków"></label><br>
	<select name="class" style="width: 100%">
		<?php if (!$classes): ?> <option value="" selected disabled hidden>Wybierz klasę!</option> <?php endif ?>
		<?php foreach ($classes as $c): ?>
			<option value="<?=$c["id"]?>" <?= $class_id==$c["id"] ? "selected" : ""?> ><?=$c["name"]?></option>
		<?php endforeach?>
	</select>
	<label><textarea name="text" rows="16" style="width: 100%" placeholder="Wpisz tutaj tekst. 16-5500 znaków, max 100 linijek"><?=$text ?? ""?></textarea></label><br>
	<input type="submit" value="<?=$b_submit?>">
</form>
