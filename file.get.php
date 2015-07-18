<?php 

	require_once 'init.php';
	require_once 'file.utils.php';

	if($arg['loggedIn']) {
		try {
			$record = getFileRecord($i, $_GET['fileId'], $_SESSION['user']);
			$record['id'] = $record['public_id'];
			$record['title'] = $record['name'];
			$record['path'] = $record['fname'];

			echo json_encode(array(
				"success" => true,
				"fileInfo" => $record
			));
		} catch(mysqli_sql_exception $exc){ tossError($exc, "There was an internal error while retreiving your file"); }
		  catch(UnexpectedValueException $exc){ tossError($exc, "This file no longer exists"); }
	} else {
		echo error("Access denied");
	}

?>