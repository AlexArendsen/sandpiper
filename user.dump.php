<?php

	require_once 'init.php';

	if(!$arg['loggedIn'] || !$arg['isAdmin']) {
		echo error("Access Denied");
	} else {
		if($s=$i->prepare("
			SELECT
				PUBLIC_ID,
				USERNAME,
				ISADMIN
			FROM
				USERS
			")){
			$s->bind_result($pid,$uname,$isadmin);
			if($s->execute()){
				$usersOut = array();
				while($s->fetch()){
					array_push($usersOut,array(
							"id" => $pid,
							"username" => $uname,
							"isAdmin" => $isadmin
						));
				}
				$out = array(
					"success" => true,
					"payload" => $usersOut
					);
				echo json_encode($out);
			} else {
				echo error("Internal Error (Statement Execution)");
			}
		} else {
			echo error("Internal Error (Statement Preparation)");
		}
	}

?>