<div style="width: 100%; padding: 0">
	<?php foreach ($posts as $p): ?>
	<a class="post" href="/posts/view/<?=$p["id"]?>">
		<div class="post">
			<?php
			$icon = $p["author_teacher"] ? "&#x265B" : "";
			$icon = $p["author_admin"] ? "&#x26A0" : $icon;
			?>
			<div class="post_icon">
				<?=$icon?>
			</div>
			<div class="post_rest">
				<div class="post_title"><?=$p["title"]?></div>
				<div class="post_date"><b><?=$p["author_nick"]?></b> - <?=$p["create_timestamp"]?> (<?=$p["class_name"]?>)<br></div>
				</div>
		</div>
	</a>
	<?php endforeach ?>
</div>