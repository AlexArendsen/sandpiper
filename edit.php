<?php

	require_once 'init.php';

	if($arg['loggedIn']) {
		if(isset($_GET['i'])) {
			$arg['fileId'] = $_GET['i'];
		}
		echo $twig->render("edit.html",$arg);
	} else {
		header('Location: index.php');
	}

?>