<?php

	require_once 'init.php';
	require_once 'errors.php';

	$fname = $_REQUEST['file'];
	$fpath = 'uploads/'.$_SESSION['userPublic'].'/'.$fname;

	$real = realpath(pathinfo($fpath)['dirname']);
	error_log("Realpath = ".$real);
	
	// Check that user hasn't left their uploads directory (used ../ in the path)
	if(strpos($real,"uploads/".$_SESSION['userPublic'])==false){
		sendError($twig,'403');
		exit;
	}

	if($arg['loggedIn']) {
		if(!$_REQUEST['file']) {
			sendError($twig,'401');
		} else {
			if(!file_exists($fpath)){
				sendError($twig,'404');
			} else {
				$filetime = filemtime($fpath);
				$etag = MD5(filemtime($fpath));
				header('Cache-Control: public, max-age=31536000');

				$notChanged = 
					(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $filetime)
						||
					(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag);

				if($notChanged) {
					sendError($twig,'304');
					exit;
				} else {
					$nextmonth = time() + 2419200;
					header('Content-Type: '.mime_content_type($fpath));
					header('Content-Length: '.filesize($fpath));
					header('Expires: '.date('r',$nextmonth));
					header('Last-Modified: '.date('r',$filetime));
					header('etag: '.$etag);
					readfile($fpath);
					exit;
				}
			}
		}
	} else {sendError($twig,'401');}

?>
