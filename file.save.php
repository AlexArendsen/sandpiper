<?php

	require_once 'init.php';
	require_once 'utils.php';

	/**
	 * Move uploaded file to new location within given directory. Will replace
	 * existing file if one is given.
	 * 
	 * @param  string $uploadDirectory: Directory where new file should be
	 * 		placed, and (if given) where existing file resides. Trailing slash
	 * 		must be included.
	 * @param  string $fileSuperglobalKey: Key within $_FILES superglobal where
	 * 		file can be found.
	 * @param  string $filePublicId: Public_id for new file.
	 * @param  string $existingFileName: File of existing file to be replaced.
	 * 		If false, no files are removed.
	 * 
	 * @return string: Filename of newly uploaded file 
	 *
	 * @throws RuntimeException: Thrown if existing file was provided, but
	 * 		could not be removed
	 * @throws Exception: Thrown if new file could not be moved. This usually
	 * 		occurs because the server does not have proper read/write access to
	 * 		the uploads/ directory
	 * @throws InvalidArgumentException: Thrown if the uploaded file has an
	 * 		unsafe extension
	 */
	function replaceFileWithUpload($uploadDirectory, $fileSuperglobalKey, $filePublicId, $existingFileName) {
		// Check for safe extension
		$fileExt = pathinfo($_FILES[$fileSuperglobalKey]['name'],PATHINFO_EXTENSION);
		if(ctype_alnum($fileExt)) {

			// Generate new filename
			$fileName = preg_replace('/[^\da-z]/i','-',pathinfo($_FILES[$fileSuperglobalKey]['name'],PATHINFO_FILENAME));
			$targetFile = substr($filePublicId."-".$fileName,0,254-strlen($fileExt)).".".$fileExt;
			$targetPath = $uploadDirectory.$targetFile;

			// Delete old file if requested
			if($existingFileName!=false && !unlink($uploadDirectory.$existingFileName)) {
				throw new RuntimeException("Failed to remove old file");
			}

			if(!move_uploaded_file($_FILES[$fileSuperglobalKey]['tmp_name'], $targetPath)){
				throw new Exception("Failed to upload / move file");
			}

			return $targetFile;

		} else {throw new InvalidArgumentException("Unsafe file extension");}
	}

	/**
	 * Updates filename field of file record with the given PUBLIC_ID to the
	 * given filename
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to DB
	 * @param  string $filename: Filename of file
	 * @param  string $filePublicId: PUBLIC_ID of file record
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database problem
	 * 		was encountered
	 */
	function associateFilename($mysqliLink, $filename, $filePublicId) {
		if($s=$mysqliLink->prepare("
			UPDATE
				FILES
			SET
				FNAME = ?
			WHERE
				PUBLIC_ID = ?
		")) {
			$s->bind_param('ss',$filename,$filePublicId);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while executing filename update script");}
			$s->close();
		} else {throw new mysqli_sql_exception("Error while preparing filename update script");
		}
	}

	/**
	 * Update plain fields of file record with the given PUBLIC_ID
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to DB
	 * @param  string $fileTitle: New file title field for record
	 * @param  string $fileTags: New tags field value for record
	 * @param  string $filePublicId: PUBLIC_ID of record
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database problem
	 * 		was encountered
	 */
	function updateFileRecord($mysqliLink, $fileTitle, $fileTags, $filePublicId) {
		if($s=$mysqliLink->prepare("
			UPDATE
				FILES
			SET
				NAME = ?,
				TAGS = ?
			WHERE
				PUBLIC_ID = ?
		")) {
			$s->bind_param('sss',$fileTitle,$fileTags,$filePublicId);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while updating record plain fields");}
			$s->close();
		} else {throw new mysqli_sql_exception("Statement preparation error on plain field update");}
	}

	/**
	 * Create new file record with the given plain fields
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to DB
	 * @param  int $ownerId: ID of user that owns the file to be inserted
	 * @param  string $filePublicId: ID of file to be inserted
	 * @param  string $fileTitle: Title of file to be inserted
	 * @param  string $fileTags: Tags field value for file to be inserted
	 * 
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database problem
	 * 		was encountered
	 */
	function insertFileRecord($mysqliLink, $ownerId, $filePublicId, $fileTitle, $fileTags) {
		if($s=$mysqliLink->prepare("
			INSERT INTO FILES (
				OWNER_ID,
				PUBLIC_ID,
				NAME,
				TAGS
			)
			VALUES (?,?,?,?)
		")) {
			$s->bind_param('isss',$ownerId, $filePublicId, $fileTitle, $fileTags);
			if(!$s->execute()) {throw new mysqli_sql_exception("Error while executing new file script");}
			$s->close();
		} else {throw new mysqli_sql_exception($errors,"Failed to prepare record insertaion statement; aborting before uploading files");}
	}








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
					associateFilename($i, $filename, $record['public_id']);
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
					$filename = replaceFileWithUpload("uploads/".$_SESSION['userPublic']."/","file-file",$fileId,false);
					associateFilename($i, $filename, $fileId);
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
