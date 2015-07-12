<?php

	require_once 'init.php';

	function homeError($twig,$message,$arg) {
		error_log("Login Error: $message");
		$arg['message'] = $message;
		$arg['username'] = $_POST['username'];

		echo $twig->render("front.html",$arg);
	}

	if($_POST['username'] && $_POST['password']) {
		if($s=$i->prepare("SELECT ID, PASSWORD, USERNAME FROM USERS WHERE USERNAME = ?")) {
			$s->bind_param('s',$_POST['username']);
			$s->bind_result($uid,$pwd,$uname);
			if($s->execute()) {
				if($s->fetch() && $uid && $pwd) {

					$passpass = false;
					if($HASHING_TYPE=="MD5") {
						$passpass = MD5($_POST['password'])==$pwd;
					} else {
						$passpass = password_verify($_POST['password'],$pwd);
					}

					if($passpass){
						$_SESSION['user'] = intval($uid);
						$_SESSION['username'] = $uname;
						error_log("Login Success!");
						header("Location: index.php");
					} else {
						homeError($twig, "Invalid Login",$arg);
					}
				} else {
					homeError($twig, "Invalid Login",$arg);
				}
			} else {
				homeError($twig, "Internal System Error",$arg);
			}
			$s->close();
		} else {
			homeError($twig, "Internal System Error",$arg);
		}
	} else {
		echo $twig->render("front.html",$arg);
	}

?>