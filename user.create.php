<?php
	require_once 'init.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		// Check that password has been hashed
		if(strlen($_POST['password'])!=60){
			echo error("Could not verify password integrity (".$_POST['password'].")");
		} else {
			$errors = array();

			$insertSuccess = false;
			$userId = uniqid();
			if($s=$i->prepare("
				INSERT INTO USERS (
					PUBLIC_ID,
					USERNAME,
					PASSWORD,
					ISADMIN
				) VALUES (?,?,?,?)
					
			")) {
				$isAdmin = filter_var($_POST['isAdmin'],FILTER_VALIDATE_BOOLEAN);
				$s->bind_param('sssi',$userId,$_POST['username'],$_POST['password'],$isAdmin);
				if(!$s->execute()){array_push($errors,"Error while executing prepared statement");}
				else{$insertSuccess=true;}
				$s->close();
			} else {array_push($errors,"Error while preparing statement");}

			// Create uploads director for user
			$directorySuccess = false;
			if(!$insertSuccess || !mkdir("./uploads/$userId",0755)){
				array_push($errors,"Failed to create upload directory for new user");
			} else {$directorySuccess = true;}

			foreach($errors as $e) {error_log("User Creation Error: $e");}

			if(count($errors)>1) {
				echo error("There was an error while creating the user");
			} else {
				echo success("");
			}
		}
	} else {echo error("Access Denied");}
?>