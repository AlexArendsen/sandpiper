<?php

	function makeErrorTuple($label,$message) {
		return array(
			"label" => $label,
			"message" => $message
		);
	}

	$_SERVER['error_lookup'] = array(
		"403" => makeErrorTuple("Forbidden","You must be logged in to view this content"),
		"404" => makeErrorTuple("Not Found","The file or resource requested does not exist"),
		"500" => makeErrorTuple("Internal Server Error","The server has encountered an unexpected error. Please report this incident to your web administrator with details describing how / when the error occurred.")
	);

	function sendError($twig,$ecode) {
		header("HTTP/1.1 $ecode");
		$arg = array();
		$arg['ecode'] = $ecode;
		if(isset($_SERVER['error_lookup'][$ecode])) {
			$arg['emessage'] = $_SERVER['error_lookup'][$ecode]['message'];
			$arg['elabel'] = $_SERVER['error_lookup'][$ecode]['label'];
		}
		echo $twig->render("error.html",$arg);
	}
?>