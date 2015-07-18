<?php
	require_once 'init.php';
	require_once 'utils.php';

	/**
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param string $userPublicId: public_id of user to be created
	 * @param string $username: username of user to be created
	 * @param string $passhash: bcrypt hashed password for the user to be
	 * 		created
	 * @param int $isAdmin: boolean integer indicitating whether or the
	 * 		user to be created is an administrator.
	 *
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database issue
	 * 		is encountered.
	 */
	function createUserRecord($mysqliLink, $userPublicId, $username, $passhash, $isAdmin) {
		if($s=$mysqliLink->prepare("
			INSERT INTO USERS (
				PUBLIC_ID,
				USERNAME,
				PASSWORD,
				ISADMIN
			) VALUES (?,?,?,?)
		")) {
			$s->bind_param('sssi',$userPublicId,$username,$passhash,$isAdmin);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while executing user insert statement");}
			$out = array(
				"id" => $userPublicId,
				"username" => $username,
				"isAdmin" => $isAdmin
			);
			$s->close();
			return $out;
		} else {throw new mysql_sql_exception("Error while preparing user insert statement");}
	}

	/**
	 * @param  string $uploadsDirectory: Parent directory for uploads. Include
	 * 		trailing slash
	 * @param  string $userPublicId: New user public ID
	 * @return void
	 *
	 * @throws RuntimeException: Thrown if there is an issue creating the
	 * 		user's directory
	 */
	function createUserUploadDirectory($uploadsDirectory, $userPublicId) {
		if(!mkdir("./".$uploadsDirectory.$userPublicId,0755)) {
			throw new RuntimeException("Failed to create upload directory for new user");
		}
	}



	if($arg['loggedIn'] && $arg['isAdmin']) {

		try {
			// Check that password has been hashed
			verifyPasswordIntegrity($_POST['password']);

			$userId = uniqid();
			
			// Create uploads directory for user
			createUserUploadDirectory("uploads/",$userId);

			// Create user record
			$isAdmin = filter_var($_POST['isAdmin'],FILTER_VALIDATE_BOOLEAN);
			$newUserRecord = createUserRecord($i, $userId, $_POST['username'], $_POST['password'], $isAdmin);


			echo json_encode(array(
				"success" => true,
				"userData" => $newUserRecord
			));
		} catch (mysqli_sql_exception $exc) { tossError($exc,"There was an internal issue while deleting your file."); }
		  catch (RuntimeException $exc) {tossError($exc, "There was an error creating your uploads directory."); }
		  catch (InvalidArgumentException $exc) { tossError($exc, $exc); }

	} else {echo error("Access Denied");}
?>