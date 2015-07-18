<?php 

	require_once 'init.php';
	require_once 'utils.php';

	if($arg['loggedIn']) {
		$record = getFileRecord($i, $_GET['fileId'], $_SESSION['user']);
		$record['title'] = $record['name'];
		$record['path'] = $record['fname'];

		echo json_encode(array(
			"success" => true,
			"fileInfo" => $record
		));
	} else {
		echo error("Access denied");
	}

?>