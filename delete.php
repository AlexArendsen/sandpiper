<?php 
	
	require_once 'init.php';

	$errors = array();
	if($arg['loggedIn']) {

		if($s=$i->prepare("
				SELECT
					PUBLIC_ID,
					FNAME
				FROM
					FILES
				WHERE
					PUBLIC_ID = ?
					AND OWNER_ID = ?
			")) {

			$s->bind_param('si',$_GET['i'],$_SESSION['user']);
			$s->bind_result($pid,$fname);
			if($s->execute() && $s->fetch()){

				$s->close();
				if($pid && $fname) {
					// Delete file record
					if($ds=$i->prepare("DELETE FROM FILES WHERE PUBLIC_ID = ? AND OWNER_ID = ?")) {
						$ds->bind_param('si',$pid,$_SESSION['user']);
						if(!$ds->execute()){array_push($errors,"Error while deleting record");}
						$ds->close();
					} else {array_push($errors,"Error while preparing delete statement");}

					// Unlink file
					if(!unlink('uploads/'.$fname)) {
						array_push($errors,"Could not unlink file");
					}
				} else {array_push($errors,"Unknown file deletion error. ID = ".$pid."; FNAME = ".$fname);}
			} else {array_push($errors,"Error while executing retreival statement");}
		} else {array_push($errors,"Error while preparing retreival statement");}
	}

	foreach($errors as $e) {
		error_log("Deletion Error: $e");
	}

	header("Location: index.php");

?>