<?php

	require_once 'init.php';

	if($arg['loggedIn']) {
		echo $twig->render("dash.html",$arg);
	} else {
		if(isset($_SESSION['LOGIN_ERROR'])){$arg['message'] = $_SESSION['LOGIN_ERROR'];}
		if(isset($_SESSION['LOGIN_USERNAME'])){$arg['username'] = $_SESSION['LOGIN_USERNAME'];}

		if($LOGIN_CAPTCHA_TRIGGERED) {
			include("simple-php-captcha/simple-php-captcha.php");
			$_SESSION['captcha'] = simple_php_captcha();
			$arg['captchaSrc'] = $_SESSION['captcha']['image_src'];
		}
		echo $twig->render("front.html",$arg);
	}

?>