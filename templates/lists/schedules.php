<div style="display: flex; flex-wrap: wrap">
	<div style="flex-grow: 1; width: 33%; line-height: 140%">
		<b>Lista nauczycieli:</b><hr>
		<?php foreach ($teachers as $id => $name): ?>
			•  <a href="/schedule/teacher/<?=$id?>"><?=$name?></a><br>
		<?php endforeach ?>
	</div>
	<div style="flex-grow: 1; width: 33%; line-height: 140%">
		<b>Lista klas:</b><hr>
		<?php foreach ($classes as $id => $name): ?>
			•  <a href="/schedule/class/<?=$id?>"><?=$name?></a><br>
		<?php endforeach ?>
	</div>
	<div style="flex-grow: 1; width: 33%; line-height: 140%">
		<b>Lista sal lekcyjnych:</b><hr>
		<?php foreach ($classrooms as $id => $name): ?>
			•  <a href="/schedule/classroom/<?=$id?>"><?=$name?></a><br>
		<?php endforeach ?>
	</div>
</div>
