<?php
	require_once 'init.php';
	require_once 'user.utils.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		try {
			// Retreive user account details
			$userRecord = getUserRecord($i, $_POST['id']);

			// If user is changing password, verify integrity (must come in hashed)
			if(isset($_POST['password'])) {
				verifyPasswordIntegrity($_POST['password']);
				$userRecord['password'] = $_POST['password'];
			}

			// Apply other changed fields
			if(isset($_POST['username']) && $_POST['username']!=""){
				$userRecord['username'] = $_POST['username'];
			}
			if(isset($_POST['isAdmin'])) {
				$userRecord['isAdmin'] = filter_var($_POST['isAdmin'],FILTER_VALIDATE_BOOLEAN);
			}

			// Do the update
			$updatedUserRecord = updateUserRecord(
				$i,
				$userRecord['public_id'],
				$userRecord['username'],
				$userRecord['password'],
				$userRecord['isAdmin']
			);

			echo json_encode(array(
				"success" => true,
				"userData" => $updatedUserRecord
			));
		} catch (mysqli_sql_exception $exc) { tossError($exc,"There was an internal error while updating the user"); }


	} else {echo error("Access Denied");}

?>