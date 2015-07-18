<?php
	require_once 'init.php';

	if ($arg['loggedIn']) {
		echo $twig->render('users.html',$arg);
	} else {
		header("Location: index.php");
	}
?>