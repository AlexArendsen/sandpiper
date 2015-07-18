<?php

	require_once 'init.php';

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