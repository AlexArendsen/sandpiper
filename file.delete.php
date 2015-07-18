<?php 
	
	require_once 'init.php';
	require_once 'utils.php';

	/**
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param string $filePublicId: public_id of file to delete
	 * @param int $ownerId: ID of user who owns file to be deleted
	 * 
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown when an unexpected database
	 * 		error is encountered
	 */
	function deleteFileRecord($mysqliLink, $filePublicId, $ownerId) {
		if($s=$mysqliLink->prepare("
			DELETE FROM
				FILES
			WHERE
				PUBLIC_ID = ?
				AND OWNER_ID = ?
		")) {
			$s->bind_param('si',$filePublicId, $ownerId);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while deleting record");}
			$s->close();
		} else {throw new mysqli_sql_exception("Error while preparing delete statement", 1);}
	}

	/**
	 * @param string $directory: Directory of file to be deleted. Must include
	 * 		trailing slash
	 * @param string $filename: File of file to be deleted
	 *
	 * @return void
	 *
	 * @throws Exception: Thrown if there is a problem deleting the file
	 */
	function deleteFile($directory, $filename) {
		if(!unlink($directory.$filename)) {
			throw new Exception("Could not delete file");
		}
	}




	if($arg['loggedIn']) {
		
		try {
			$record = array();
			try {
				$record = getFileRecord($i, $_GET['i'], $_SESSION['user']);
			} catch (UnexpectedValueException $exc) { throw new Exception("File record not found"); }
			deleteFileRecord($i, $record['public_id'], $_SESSION['user']);
			deleteFile("uploads/".$_SESSION['userPublic']."/",$record['fname']);
		} catch (mysqli_sql_exception $exc) { tossError($exc,"There was an internal issue while deleting your file."); }
		  catch (Exception $exc) { tossError($exc,"There was an error while deleting your file: $exc"); }

	}

	header("Location: index.php");

?>