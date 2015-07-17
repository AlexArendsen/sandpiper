<?php
	require_once 'init.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		$errors = array();

		// Retreive user account details
		$retreivalSuccess = false;
		if($s=$i->prepare("SELECT PUBLIC_ID, USERNAME, PASSWORD, ISADMIN FROM USERS WHERE PUBLIC_ID = ?")) {
			$s->bind_param('s',$_POST['id']);
			$s->bind_result($pid,$username,$password,$isAdmin);
			if($s->execute() && $s->fetch()){$retreivalSuccess = true;}
			else {array_push($errors,"Error while executing retreival statement for user ".$_POST['id']);}
			$s->close();
		} else {array_push($errors,"Error while preparing statement");}

		// If user is changing password, verify integrity (must come in hashed)
		$passwordSuccess = false;
		if(isset($_POST['password'])) {
			if(strlen($_POST['password'])!=60) {
				array_push($errors,"Password integrity could not be verified.");
			} else {
				$password = $_POST['password'];
				$passwordSuccess = true;
			}
		} else {$passwordSuccess = true;}

		// Apply other changed fields
		if(isset($_POST['username']) && $_POST['username']!=""){
			$username = $_POST['username'];
		}
		if(isset($_POST['isAdmin'])) {
			$isAdmin = filter_var($_POST['isAdmin'],FILTER_VALIDATE_BOOLEAN);
		}

		// Do the update
		if($s=$i->prepare("
			UPDATE
				USERS
			SET
				USERNAME = ?,
				PASSWORD = ?,
				ISADMIN = ?
			WHERE
				PUBLIC_ID = ?
		")) {
			$s->bind_param('ssis',$username,$password,$isAdmin,$pid);
			if($s->execute()){
				echo json_encode(array(
					"success" => true,
					"userData" => array(
						"username" => $username,
						"isAdmin" => $isAdmin,
						"id" => $pid
					)
				));
			} else {array_push($errors,"Error while executing update script");}
			$s->close();
		} else {array_push($errors,"Error while preparing statement");}

		foreach($errors as $e) {error_log("User Update Error: $e");}

	} else {echo error("Access Denied");}

?>