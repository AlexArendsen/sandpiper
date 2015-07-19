<?php
	if(isset($argv[2])){
		$pwd = password_hash($argv[2],PASSWORD_BCRYPT);
		$userId = uniqid();
		echo "INSERT INTO USERS (PUBLIC_ID, USERNAME, PASSWORD, ISADMIN) VALUES ('$userId','".$argv[1]."','$pwd',1)";
	} else {
		echo "Usage: php ".$argv[0]." <username> <password>";
	}
?>