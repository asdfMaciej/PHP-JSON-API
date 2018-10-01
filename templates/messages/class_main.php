
<div style="width: 100%; text-align: center; background-color: #212121; color: #F0F0F0; min-height: 10vw; padding: 8px">
	<span style="font-size: 5vw;">
		Klasa <?=$cl_users[0]["class_name"]?>
	</span>
</div>
<?php 
	$teachers = [];
	$students = [];
	foreach ($cl_users as $us) {
		if ($us["teacher"]) {
			$teachers[] = $us;
		} else {
			$students[] = $us;
		}
	}
?>

<h3>Nauczyciele:</h3>
<ol>
	<?php foreach ($teachers as $teach): ?>
		<li> <?=$teach["first_name"]?> <?=$teach["last_name"]?></li>
	<?php endforeach ?>
</ol>

<h3>Uczniowie:</h3>
<ol>
	<?php foreach ($students as $stud): ?>
		<li> <?=$stud["first_name"]?> <?=$stud["last_name"]?></li>
	<?php endforeach ?>
</ol>