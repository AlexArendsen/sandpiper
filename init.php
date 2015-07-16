<?php
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	// Settings
	$MYSQL_HOST = "localhost";
	$MYSQL_USER = "root";
	$MYSQL_PASS = "toor";
	$MYSQL_DBNAME = "SANDPIPER";

	$HASHING_TYPE = "bcrypt";
	$LOGIN_ATTEMPTS_CAPTCHA = 3;
	if (!isset($_SESSION['LOGIN_FAILS'])) {
		$_SESSION['LOGIN_FAILS'] = 0;
	}
	$LOGIN_CAPTCHA_TRIGGERED = $_SESSION['LOGIN_FAILS'] >= $LOGIN_ATTEMPTS_CAPTCHA;

	// Connect to MySQL
	$i = new mysqli($MYSQL_HOST,$MYSQL_USER,$MYSQL_PASS,$MYSQL_DBNAME);

	// Intialize Twig
	require_once 'lib/Twig/Autoloader.php';
	Twig_Autoloader::register();

	$loader = new Twig_Loader_Filesystem('views');
	$twig = new Twig_Environment($loader);

	$lexer = new Twig_Lexer($twig, array(
		'tag_comment'   => array('{#', '#}'),
		'tag_block'     => array('{%', '%}'),
		'tag_variable' => array('{[{','}]}'),
		'interpolation' => array('#{', '}')
	));

	$twig->setLexer($lexer);

  // Generate argument stub

  $arg = array(
    "loggedIn" => isset($_SESSION['user']) && isset($_SESSION['username']),
    "maxUploadSize" => ini_get('upload_max_filesize')
  );

  if($arg['loggedIn']) {
    $arg['user'] = $_SESSION['user'];
    $arg['username'] = $_SESSION['username'];
    $arg['isAdmin'] = $_SESSION['isAdmin'];
  }

  // Error JSON response convenience function
  function error($emsg) {
  	echo "{\"error\":\"$emsg\",\"success\":false}";
  }

  // Succcess JSON response convenience function
  function success($msg) {
  	echo "{\"message\":\"$msg\",\"success\":true}";
  }
?>
