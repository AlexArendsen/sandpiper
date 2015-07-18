<?php
	require_once 'init.php';
	require_once 'user.utils.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		try {
			$dump = dumpUsers($i);
			echo json_encode(array(
				"success" => true,
				"payload" => $dump
			));
		} catch (mysqli_sql_exception $exc) { tossError($exc, "There was an internal error while fetching the user list."); }

	} else {echo error("Access Denied");}

?>