<?php

	require_once 'init.php';

	if($arg['loggedIn']) {
		echo $twig->render("dash.html",$arg);
	} else {
		echo $twig->render("front.html",$arg);
	}

?>