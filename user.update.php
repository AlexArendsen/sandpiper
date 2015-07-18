<?php
	require_once 'init.php';
	require_once 'utils.php';

	/**
	 * Update a user's record
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to database
	 * @param  string $userPublicId: public_id of user to be updated
	 * @param  string $username: new username for the user
	 * @param  string $passhash: new password has for the user
	 * @param  int $isAdmin: boolean integer indicating whether the user an
	 * 		administrator or not
	 * 
	 * @return array: Associative array of the user record with the following
	 * 		keys:
	 * 			"username" => user's new username
	 * 			"isAdmin" => boolean integer indicating whether or not the user
	 * 				is now an administrator
	 * 			"id" => user's public_id
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database issue
	 * 		is encountered.
	 */
	function updateUserRecord($mysqliLink, $userPublicId, $username, $passhash, $isAdmin) {
		if($s=$mysqliLink->prepare("
			UPDATE
				USERS
			SET
				USERNAME = ?,
				PASSWORD = ?,
				ISADMIN = ?
			WHERE
				PUBLIC_ID = ?
		")) {
			$s->bind_param('ssis',$username,$passhash,$isAdmin,$userPublicId);
			if(!$s->execute()){
				throw new mysqli_sql_exception("Error while executing user update script");
			}
			$out = array(
				"username" => $username,
				"isAdmin" => $isAdmin,
				"id" => $userPublicId
			);
			$s->close();
			return $out;
		} else {
			throw new mysqli_sql_exception("Error while preparing user update script");
		}
	}



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