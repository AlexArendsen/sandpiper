<?php

	require_once 'init.php';

	if(isset($_GET['i'])) {
		$arg['fileId'] = $_GET['i'];
	}
	echo $twig->render("edit.html",$arg);

?>