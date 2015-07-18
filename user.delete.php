<?php
	require_once 'init.php';
	require_once 'utils.php';

	/**
	 * Deletes a user's database record. Cascades delete to user's file records
	 * as well
	 * 
	 * @param  mysqli $mysliLink: MySQLi link to database
	 * @param  string $userPublicId: public_id of user to be deleted
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database issue
	 * 		is encountered.
	 */
	function deleteUserRecord($mysqliLink, $userPublicId) {
		if($s=$mysqliLink->prepare("
			DELETE FROM
				USERS
			WHERE
				PUBLIC_ID = ?"
		)) {
			$s->bind_param('s',$userPublicId);
			if(!$s->execute()){
				throw new mysqli_sql_exception("Unexpected error while executing user deletion script");
			}
			$s->close();
		} else {throw new mysql_sql_exception("Unexpected error while preparing user deletion script");}
	}

	/**
	 * Moves a user's uploads directory to the trash.
	 *
	 * @param  string $uploadsDirectory: Upload directory. Include trailing
	 * 		slash.
	 * @param  string $userPublicId: public_id of user whose uploads directory
	 * 		should be deleted
	 * @return void
	 *
	 * @throws RuntimeException: Thrown if user's uploads directory cannot be
	 *		moved to trash
	 */
	function evacuateUserUploadsDirectory($uploadsDirectory, $userPublicId) {
		if(!rename("./".$uploadsDirectory.$userPublicId."/","./".$uploadsDirectory.".trash/".$userPublicId)) {
			throw new RuntimeException("Failed to move user upload directory to trash");
		}
	}

	if($arg['loggedIn'] && $arg['isAdmin']) {

		// Move storage directory to trash
		$userRecord = getUserRecord($i,$_GET['i']);
		evacuateUserUploadsDirectory("uploads/",$userRecord['public_id']);
		
		// Delete user record (file records will delete via cascade)
		deleteUserRecord($i, $userRecord['public_id']);

		echo success("User deleted successfully");

		
	} else {echo error("Access Denied");}
?>