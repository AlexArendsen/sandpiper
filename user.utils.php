<?php

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
	 * @param  mysqli $mysqliLink: MySQLi link to database
	 * @return array: Array of associative arrays for every user with the 
	 * 		following keys:
	 * 			"id" => User public ID
	 * 			"username" => User username
	 * 			"isAdmin" => Boolean integer indicating whether user is an
	 * 				administrator or not
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database issue
	 * 		is encountered. 
	 */
	function dumpUsers($mysqliLink) {
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				USERNAME,
				ISADMIN
			FROM
				USERS
		")){
			$s->bind_result($pid,$username,$isAdmin);
			if($s->execute()){
				$out = array();
				while($s->fetch()){
					array_push($out,array(
						"id" => $pid,
						"username" => $username,
						"isAdmin" => $isAdmin
					));
				}
				$s->close();
				return $out;
			} else {throw new mysqli_sql_exception("Internal Error (Statement Execution)");}
		} else {throw new mysqli_sql_exception("Internal Error (Statement Preparation)");}
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

		$userUploadsDirectory = "./".$uploadsDirectory.$userPublicId."/";

		if(!rename($userUploadsDirectory,"./".$uploadsDirectory.".trash/".$userPublicId)) {
			throw new RuntimeException("Failed to move user upload directory to trash");
		}
	}

	/**
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param string $userPublicId: public_id of user to get
	 * @return array: Associative array of user record fields with the following
	 * 		keys:
	 * 			public_id => The user's public ID
	 * 			username => The user's username
	 * 			password => bCrypt hash of the user's password
	 * 			isAdmin => Boolean integer indicating whether or not the user
	 * 				is an administrator
	 *
	 * @throws mysqli_sql_exception: Thrown if there is an error executing the
	 *  	SQL statement or fetching its results.
	 */
	function getUserRecord($mysqliLink, $userPublicId) {
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				USERNAME,
				PASSWORD,
				ISADMIN
			FROM
				USERS
			WHERE
				PUBLIC_ID = ?"
		)) {
			$s->bind_param('s',$userPublicId);
			$out = array();
			$s->bind_result($out['public_id'],$out['username'],$out['password'],$out['isAdmin']);
			if(!($s->execute() && $s->fetch())){
				throw new mysqli_sql_exception("Error while executing user record retreival statement");
			}
			$s->close();
			return $out;
		} else {throw new mysqli_sql_exception("Error while preparing user record retreival statement");}
	}

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

	/**
	 * Verify the integrity of the given password hash
	 * 
	 * @param  string $passhash: bCrypt hash of password
	 * @return void
	 *
	 * @throws InvalidArgumentException: Thrown if password integrity cannot be
	 * 		verified.
	 */
	function verifyPasswordIntegrity($passhash) {
		if(strlen($passhash)!=60){
			throw new InvalidArgumentException("Password integrity could not be verified");
			
		}
	}

?>