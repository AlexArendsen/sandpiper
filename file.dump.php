<?php

	require_once 'init.php';
	require_once 'file.utils.php';


	if($arg['loggedIn']) {
		try {
			$dump = dumpFiles($i,$_SESSION['user']);
			echo json_encode(array(
				"success" => true,
				"payload" => $dump
			));
		} catch (mysqli_sql_exception $exc) { tossError($exc, "There was an internal error while retreiving your files."); }
	} else {
		echo error("Access Denied");
	}

?>