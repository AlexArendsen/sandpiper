<?php

	require_once 'init.php';

	function send304() {
		header('HTTP/1.0 304 Not Modified');
	}

	function send403() {
		header('HTTP/1.0 403 Forbidden');
		echo "<h1>403 - Forbidden</h1><h3>You must be logged in to see this. <a href='../index.php'>Click here to log in</a>.</h3>";
	}

	function send404() {
		header('HTTP/1.0 404 Not Found');
		echo "<h1>404 - Not Found</h1><h3>The file you have requested does not exist. <a href='../index.php'>Click here to go back</a>.</h3>";
	}

	function send500($msg) {
		header('HTTP/1.1 500 Internal Server Error');
		echo "<h1>500 - Internal Server Error</h1><p>$msg</p>";
	}

	$fname = $_REQUEST['file'];
	$fpath = 'uploads/'.$fname;

	if($arg['loggedIn']) {
		if(!$_REQUEST['file']) {
			send403();
		} else {
			if(!file_exists($fpath)){
				send404();
			} else {
				$etag = MD5(filemtime($fpath));
				header('Cache-Control: public, max-age=31536000');
				if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
					send304();
					exit;
				} else {
					header('Content-Type: '.mime_content_type($fpath));
					header('Content-Length: '.filesize($fpath));
					header('etag: '.$etag);
					readfile($fpath);
					exit;
				}
			}
		}
	} else {echo send403();}

?>
