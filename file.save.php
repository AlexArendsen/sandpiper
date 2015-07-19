<?php

	require_once 'init.php';
	require_once 'file.utils.php';


	if($arg['loggedIn']) {

		$fileUploaded = !(!file_exists($_FILES['file-file']['tmp_name']) || !is_uploaded_file($_FILES['file-file']['tmp_name']));
		$isNew = $_POST['file-id']=="";

		if(!$isNew) {

			// === Updating existing record
			try {
				// Get existing record information
				$record = getFileRecord($i,$_POST['file-id'],$_SESSION['user']);

				// Upload new file, if necessary
				if($fileUploaded) {
					try {
						$filename = replaceFileWithUpload("uploads/".$_SESSION['userPublic']."/","file-file",$record['public_id'],$record['fname']);
					} catch(RuntimeException $exc) { tossError($exc,"Existing file could not be removed"); }

					// Try to create a thumbnail
					$thumbField = false;
					try {
						$thumbField = createImageThumbnail("uploads/".$_SESSION['userPublic']."/",$filename);
					} catch (Exception $exc) { /* Non-fatal */ }

					associateFilename($i, $filename, $record['public_id'], $thumbField);
				}

				// Update record plain fields
				updateFileRecord($i, $_POST['file-title'], $_POST['file-tags'], $record['public_id']);
			} catch (mysqli_sql_exception $exc){ tossError($exc, "There was an internal error while updating your file"); }
			  catch (UnexpectedValueException $exc) {tossError($exc, "The file you are updating no longer exists"); }
			  catch (InvalidArgumentException $exc) {tossError($exc, "The uploaded file has an unsafe extension"); }
			  catch (Exception $exc) {tossError($exc, "Server is misconfigured, please contact your administrator"); }



		} else {

			// === Insert new record
			$fileId = uniqid();

			try {
				// Insert record
				insertFileRecord($i, $_SESSION['user'], $fileId, $_POST['file-title'], $_POST['file-tags']);

				// Upload document file if necessary
				if($fileUploaded) {
					$userUploadsDirectory = "uploads/".$_SESSION['userPublic']."/";

					// If user uploads directory doesn't exist, create it
					if(!file_exists($userUploadsDirectory)) {mkdir($userUploadsDirectory);}
					
					$filename = replaceFileWithUpload($userUploadsDirectory,"file-file",$fileId,false);

					// Try to create a thumbnail
					$thumbField = false;
					try {
						$thumbField = createImageThumbnail($userUploadsDirectory,$filename);
					} catch (Exception $exc) { /* Non-fatal */ }
					
					associateFilename($i, $filename, $fileId, $thumbField);
				}
			} catch (mysqli_sql_exception $exc){ tossError($exc, "There was an internal error while uploading your file"); }
			  catch (InvalidArgumentException $exc) {tossError($exc, "The uploaded file has an unsafe extension"); }
			  catch (Exception $exc) {tossError($exc, "Server is misconfigured, please contact your administrator"); }

		}



		$redir = $_POST['redirection'];
		if($redir) {
			if($redir=="continue"){
				header("Location: file.edit.php"); // User clicked "Create and add another"
			} else { header("Location: index.php"); }
		} else { header("Location: index.php"); }

	} else {
		header("Location: index.php");
	}

?>
