<?php 
	
	require_once 'init.php';
	require_once 'file.utils.php';

	if($arg['loggedIn']) {
		
		try {
			$record = array();
			try {
				$record = getFileRecord($i, $_GET['i'], $_SESSION['user']);
			} catch (UnexpectedValueException $exc) { throw new Exception("File record not found"); }
			deleteFileRecord($i, $record['public_id'], $_SESSION['user']);

			deleteFile("uploads/".$_SESSION['userPublic']."/",$record['fname']);
			if($record['has_thumb']==1) {
				deleteFile("uploads/".$_SESSION['userPublic']."/",$record['fname'].".thumb.png");
			}
		} catch (mysqli_sql_exception $exc) { tossError($exc,"There was an internal issue while deleting your file."); }
		  catch (Exception $exc) { tossError($exc,"There was an error while deleting your file"); }

	}

	header("Location: index.php");

?>