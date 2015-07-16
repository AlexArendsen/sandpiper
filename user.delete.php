<?php
	require_once 'init.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		$errors = array();

		// 1 - Move storage directory to trash
		$moveSuccess = false;
		if($s=$i->prepare("SELECT PUBLIC_ID FROM USERS WHERE PUBLIC_ID = ?")) {
			$s->bind_param('s',$_GET['i']);
			$s->bind_result($pid);
			if($s->execute() && $s->fetch()){
				if(rename("./uploads/$pid/","./uploads/.trash/$pid")) {$moveSuccess = true;}
				else {array_push($errors,"Failed to move user uploads directory to trash");}
			} else {array_push($errors,"Error while executing public ID retreival statement");}
			$s->close();
		} else {array_push($errors,"Error while preparing ID retreival statement");}

		// 2 - Delete user record (file records will delete via cascade)
		$deleteSuccess = false;
		if($moveSuccess && $s=$i->prepare("DELETE FROM USERS WHERE PUBLIC_ID = ?")) {
			$s->bind_param('s',$_GET['i']);
			if($s->execute()){
				$deleteSuccess = true;
			} else {array_push($errors,"Unexpected error while deleting user");}
			$s->close();
		} else {array_push($errors,"Unexpected error while deleting user");}

		foreach($errors as $e) {error_log("User Deletion Error: $e");}

		if($moveSuccess && $deleteSuccess) {
			echo success();
		} else {echo error("Could not delete user. Please contact your administrator.");}
	} else {echo error("Access Denied");}
?>