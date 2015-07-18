<?php
	require_once 'init.php';
	require_once 'user.utils.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		try {
			// Move storage directory to trash
			$userRecord = getUserRecord($i,$_GET['i']);
			try {
				evacuateUserUploadsDirectory("uploads/",$userRecord['public_id']);
			} catch (RuntimeException $exc) { tossError($exc, "Could not remoe user's uploads directory"); }
			
			// Delete user record (file records will delete via cascade)
			deleteUserRecord($i, $userRecord['public_id']);

			echo success("User deleted successfully");
		} catch (mysql_sql_exception $exc) { tossError($exc, "There was an internal error while deleting the user"); }

		
	} else {echo error("Access Denied");}
?>