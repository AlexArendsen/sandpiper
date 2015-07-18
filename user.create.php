<?php
	require_once 'init.php';
	require_once 'user.utils.php';

	if($arg['loggedIn'] && $arg['isAdmin']) {

		try {
			// Check that password has been hashed
			verifyPasswordIntegrity($_POST['password']);

			$userId = uniqid();
			
			// Create uploads directory for user
			createUserUploadDirectory("uploads/",$userId);

			// Create user record
			$isAdmin = filter_var($_POST['isAdmin'],FILTER_VALIDATE_BOOLEAN);
			$newUserRecord = createUserRecord($i, $userId, $_POST['username'], $_POST['password'], $isAdmin);


			echo json_encode(array(
				"success" => true,
				"userData" => $newUserRecord
			));
		} catch (mysqli_sql_exception $exc) { tossError($exc,"There was an internal issue while deleting your file."); }
		  catch (RuntimeException $exc) {tossError($exc, "There was an error creating your uploads directory."); }
		  catch (InvalidArgumentException $exc) { tossError($exc, $exc); }

	} else {echo error("Access Denied");}
?>