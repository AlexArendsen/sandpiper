<?php

	require_once 'init.php';

	function homeError($twig,$message,$arg) {
		error_log("Login Error: $message");
		$_SESSION['LOGIN_ERROR'] = $message;
		$_SESSION['LOGIN_USERNAME'] = $_POST['username'];
		header('Location: index.php');
	}

	function invalidLogin($twig,$message,$arg) {
		if(!isset($_SESSION['LOGIN_FAILS'])) {
			$_SESSION['LOGIN_FAILS'] = 0;
		}
		$_SESSION['LOGIN_FAILS'] += 1;
		homeError($twig,$message,$arg);
	}

	// Save resources by checking CAPTCHA before anything
	if($LOGIN_CAPTCHA_TRIGGERED && $_POST['captcha']!=$_SESSION['captcha']['code']) {
		invalidLogin($twig,"Text does not match image",$arg);
	} else if($_POST['username'] && $_POST['password']) {
		if($s=$i->prepare("SELECT ID, PUBLIC_ID, PASSWORD, USERNAME, ISADMIN FROM USERS WHERE USERNAME = ?")) {
			$s->bind_param('s',$_POST['username']);
			$s->bind_result($uid,$upid,$pwd,$uname,$isAdmin);
			if($s->execute()) {
				if($s->fetch() && $uid && $pwd) {
					if(password_verify($_POST['password'],$pwd)) {
						$_SESSION['LOGIN_FAILS'] = 0;
						$_SESSION['user'] = intval($uid);
						$_SESSION['userPublic'] = $upid;
						$_SESSION['username'] = $uname;
						$_SESSION['isAdmin'] = $isAdmin;
						header("Location: index.php");
					} else {invalidLogin($twig,"Invalid Login",$arg);} // Username found, password mismatch
				} else {invalidLogin($twig,"Invalid Login",$arg);} // Username not found
			} else {homeError($twig, "Internal System Error",$arg);} // Error while executing prepared statement
			$s->close();

		} else {homeError($twig, "System error. Contact system administrator.",$arg);} // Error while preparing statement. DB probably isn't install; run install/install.sql
	} else {
		homeError($twig,"Please enter username and password",$arg);
	}

?>