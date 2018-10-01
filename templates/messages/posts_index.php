<div>
</div>
<?php
	$this->nest("lists/posts.php", [
		"user" => $user,
		"posts" => $posts
	]);
?>
