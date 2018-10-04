<a href="?after=<?=$after+10?>">Następne 10 postów [<?=$after+11?>-<?=$after+20?>]</a>
<?php
	$this->nest("lists/posts.php", [
		"user" => $user,
		"posts" => $posts
	]);
?>
