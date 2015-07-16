<?php

	require_once 'init.php';

	if($arg['loggedIn']) {

		$warnings = array();
		$errors = array();
		$fileId = '';
		$fileUploaded = !(!file_exists($_FILES['file-file']['tmp_name']) || !is_uploaded_file($_FILES['file-file']['tmp_name']));
		$isNew = $_POST['file-id']=="";

		if(!$isNew) {

			// Updating existing record
			// Get existing record information
			$record = array();
			if($s=$i->prepare("
				SELECT
					PUBLIC_ID,
					FNAME
				FROM
					FILES
				WHERE
					PUBLIC_ID = ?
			")) {
					$s->bind_param('s',$_POST['file-id']);
					$s->bind_result($record['public_id'],$record['fname']);
					if(!$s->execute()){
						array_push($errors,"Failed to get existing record data");
					} else {
						$s->fetch();
					}
					$s->close();
			} else {
				array_push($errors,"Failed to prepare existing record statement");
			}

			$updateSelectError = !($record['public_id'] && $record['fname']);

			if($updateSelectError) {
				array_push($warnings, "Could not find all fields of existing record: ID (from request) = (".$_POST['file-id']."), ID (from query) = (".$record['public_id']."), FName (from query) = (".$record['fname'].") ");
			}

			// Upload new file, if necessary
			if($fileUploaded) {
				// Check for safe extension
				$fileExt = pathinfo($_FILES['file-file']['name'],PATHINFO_EXTENSION);
				if(ctype_alnum($fileExt)) {

					// Generate new filename
					$fileName = preg_replace('/[^\da-z]/i','-',pathinfo($_FILES['file-file']['name'],PATHINFO_FILENAME));
					$targetFile = substr($record['public_id']."-".$fileName,0,254-strlen($fileExt)).".".$fileExt;
					$targetPath = "uploads/".$_SESSION['userPublic']."/".$targetFile;

					// Delete old file
					if($record['fname'] && !unlink("uploads/".$_SESSION['userPublic']."/".$record['fname'])) {
						array_push($errors,"Failed to remove old file");
					}

					if(move_uploaded_file($_FILES['file-file']['tmp_name'], $targetPath)){
						if($s=$i->prepare("UPDATE FILES SET FNAME = ? WHERE PUBLIC_ID = ?")) {
							$s->bind_param('ss',$targetFile,$_POST['file-id']);
							if(!$s->execute()){array_push($errors,"Error updating record file field");}
							$s->close();
						} else {array_push($errors,"Internal server error while preparing file script");}
					} else {array_push($errors,"Failed to upload / move file");}
				} else {array_push($errors,"File upload rejected: unsafe file extension");}
			}

			// Update record plain fields
			if($s=$i->prepare("
				UPDATE
					FILES
				SET
					NAME = ?,
					TAGS = ?
				WHERE
					PUBLIC_ID = ?
			")) {
				$s->bind_param('sss',$_POST['file-title'],$_POST['file-tags'],$_POST['file-id']);
				if(!$s->execute()){array_push($errors,"Error while updating record plain fields");}
				$s->close();
			} else {array_push($errors,"Statement preparation error on plain field update");}

			$fileId = $_POST['file-id'];

		} else {

			// Insert new record
			// Insert record
			$fileId = uniqid();
			$insertSuccess = 0;

			if($s=$i->prepare("
				INSERT INTO FILES (
					OWNER_ID,
					PUBLIC_ID,
					NAME,
					TAGS
				)
				VALUES (?,?,?,?)
			")) {
				$s->bind_param('isss',$_SESSION['user'],$fileId,$_POST['file-title'],$_POST['file-tags']);
				if($s->execute()) {$insertSuccess = 1;}
				else {array_push($errors,"Failed to insert new record");}
				$s->close();
			} else {array_push($errors,"Failed to prepare record insertaion statement; aborting before uploading files");}

			// Upload document file if necessary
			if($fileUploaded && $insertSuccess) {
				// Check for safe extension
				$fileExt = pathinfo($_FILES['file-file']['name'],PATHINFO_EXTENSION);
				if(ctype_alnum($fileExt)) {

					// Generate filename
					$fileName = preg_replace('/[^\da-z]/i','-',pathinfo($_FILES['file-file']['name'],PATHINFO_FILENAME));
					$targetFile = substr($fileId."-".$fileName,0,254-strlen($fileExt)).".".$fileExt;
					$targetPath = "uploads/".$_SESSION['userPublic']."/".$targetFile;

					if(move_uploaded_file($_FILES['file-file']['tmp_name'], $targetPath)){
						if($s=$i->prepare("UPDATE FILES SET FNAME = ? WHERE PUBLIC_ID = ?")) {
							$s->bind_param('ss',$targetFile,$fileId);
							if(!$s->execute()){array_push($errors,"Failed to associate new file with record");}
							$s->close();
						} else {array_push($errors,"Failed to prepare file update statement");}
					} else {array_push($errors,"Failed to upload / move file");}
				} else {array_push($errors,"File upload rejected: unsafe file extension");}
			}

		}

		foreach($errors as $e) {
			error_log("Save Error: $e");
		}

		$redir = $_POST['redirection'];
		if($redir) {
			if($redir=="continue"){
				header("Location: file.edit.php"); // User clicked "Create and add another"
			} else {header("Location: index.php");}
		} else {header("Location: index.php");}

	} else {
		header("Location: index.php");
	}

?>
