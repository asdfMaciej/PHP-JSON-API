<?php if ($user): ?>
	<form action="/" method="post">
		<input type="hidden" name="action" value="logout">

		<label>Wyloguj się: <input type="submit"></label>
	</form>
<?php else: ?>
	<form action="/" method="post" style="margin: 0px; padding: 0px">
		<input type="hidden" name="action" value="login">

		<label> Login: <input name="login" type="text"></label><br>
		<label> Haslo: <input name="password" type="password"></label><br>
		<input type="submit" value="Zaloguj się">
	</form>
	<form action="/register" method="get">
		<input type="submit" value="Zarejestruj się">
	</form>
	
<?php endif ?>