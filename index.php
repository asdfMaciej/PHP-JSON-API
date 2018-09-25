<?php
$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = $uri[0];

$pages = [
	"" => "pages/index.php",
	"/" => "pages/index.php",
	"404" => "pages/404.php",
	"register" => "pages/register.php",
	"admin" => [
		"index" => "pages/admin.php",
		"panel" => "pages/admin.php",
		"logs" => "pages/admin_logs.php"
	],
	"profile" => "pages/profile.php"
];

$iter_pages = $pages;
$prefix = "";
$depth = 0;
$path_levels = explode("/", $path);
array_shift($path_levels); // 1st item
while (True) {
	if (sizeof($path_levels) <= $depth) {
		if (array_key_exists("index", $iter_pages)) {
			require $iter_pages["index"];
			break;
		} else {
			require $pages["404"];
			break;
		}
	}

	$_p = $path_levels[$depth];
	if (array_key_exists($_p, $iter_pages)) {
		if (is_array($iter_pages[$_p])) {
			$iter_pages = $iter_pages[$_p];
			$prefix .= $_p . "/";
			$depth += 1;
		} else {
			require $iter_pages[$_p];
			break;
		}
	} elseif ($_p == "" || $_p == "/") {
		if (array_key_exists("index", $iter_pages)) {
			require $iter_pages["index"];
			break;
		} else {
			require $pages["404"];
			break;
		}
	} else {
		require $pages["404"];
		break;
	}
}
?>