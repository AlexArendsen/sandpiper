<?php

	require_once 'init.php';

	/**
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param int $ownerId: ID of user whose files will be dumped
	 * 
	 * @return array: Array of associative arrays containing all files owned
	 * 		by the given user
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected databse issue
	 * 		is encountered
	 */
	function dumpFiles($mysqliLink,$ownerId) {
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				NAME,
				FNAME,
				DATE_FORMAT(ENTRY_DATE,'%d %b %Y') AS EDATE,
				TAGS
			FROM
				FILES
			WHERE
				OWNER_ID = ?
			ORDER BY
				ENTRY_DATE DESC")){
			$s->bind_param("i",$ownerId);
			$s->bind_result($id,$name,$fname,$edate,$tags);
			if($s->execute()){
				$out = array();
				while($s->fetch()) {
					array_push($out,array(
							"id" => $id,
							"title" => $name,
							"file" => $fname,
							"edate" => $edate,
							"tags" => explode(',', $tags)
						));
				}
				$s->close();
				return $out;
			} else {throw new mysqli_sql_exception("Error while executing file dump statement");}
		} else {throw new mysqli_sql_exception("Error while preparing file dump statement");}
	}


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