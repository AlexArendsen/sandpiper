<?php

	/**
	 * Updates filename field of file record with the given PUBLIC_ID to the
	 * given filename
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to DB
	 * @param  string $filename: Filename of file
	 * @param  string $filePublicId: PUBLIC_ID of file record
	 * @param  boolean $fileThumb: Boolean representing whether or not the file
	 *                             has a thumnail that should be used on the
	 *                             dashboard.
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected database problem
	 * 		was encountered
	 */
	function associateFilename($mysqliLink, $filename, $filePublicId, $fileThumb) {
		if($s=$mysqliLink->prepare("
			UPDATE
				FILES
			SET
				FNAME = ?,
				HASTHUMB = ?
			WHERE
				PUBLIC_ID = ?
		")) {
			$fileThumb = filter_var($fileThumb,FILTER_VALIDATE_BOOLEAN);
			$s->bind_param('sis',$filename,$fileThumb,$filePublicId);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while executing filename update script");}
			$s->close();
		} else {throw new mysqli_sql_exception("Error while preparing filename update script");
		}
	}

	/**
	 * Attempt to create a thumbnail out of the given image file.
	 * 
	 * @param  string $directory : Directory containing source image
	 * @param  string $filename  : Filename of source image
	 * @return mixed             : true on thumbnail creation success, false
	 *                             on failure.
	 *
	 * @throws InvalidArgumentException: Thrown if source image file doesn't
	 *         exist.
	 *         
	 * @throws RuntimeException: Thrown if there is a problem while processing
	 *         the image.
	 */
	function createImageThumbnail($directory, $filename) {

		$fpath = "./".$directory.$filename;
		if(!file_exists($fpath)) {
			throw new InvalidArgumentException("File does not exist");
		}

		$fileExt = strtolower(getFileExtension($filename));
		$imgSrc = null;
		$createImageFunction = null;
		switch($fileExt) {
			case "jpg":
			case "jpeg":
				$imgSrc = imagecreatefromjpeg($fpath);
				break;
			case "png":
				$imgSrc = imagecreatefrompng($fpath);
				break;
			default:
				return false;
				break;
		}

		if($imgSrc==false||$imgSrc==null) {
			throw new RuntimeException("Error while opening image");
		}

		$THUMBNAIL_WIDTH = 360;
		$THUMBNAIL_CROP_HEIGHT = 240;
		$oldX = imagesx($imgSrc);
		$shrinkFactor = $oldX/$THUMBNAIL_WIDTH;
		if($shrinkFactor<1){}

		$oldY = imagesy($imgSrc);
		$newX = $THUMBNAIL_WIDTH; // Same as $oldX/$shrinkFactor
		$newY = $oldY/$shrinkFactor;
		$verticalOffset = 0;
		if($newY>$THUMBNAIL_CROP_HEIGHT) {
			$newY = $THUMBNAIL_CROP_HEIGHT;
			$verticalOffset = ($oldY/2)-(($newY*$shrinkFactor)/2);
		}

		$imgTgt = imagecreatetruecolor($newX, $newY);
		imagecopyresampled(
			$imgTgt,
			$imgSrc,
			0, 0, 0, $verticalOffset,
			$newX, $newY, $oldX, $newY*$shrinkFactor
		);

		imagepng($imgTgt,$fpath.".thumb.png");
		return true;
	}

	/**
	 * Removes an uploaded file from the file system.
	 * 
	 * @param string $directory: Directory of file to be deleted. Must include
	 * 		trailing slash
	 * @param string $filename: File of file to be deleted
	 *
	 * @return void
	 *
	 * @throws Exception: Thrown if there is a problem deleting the file
	 */
	function deleteFile($directory, $filename) {
		if(!unlink($directory.$filename)) {
			throw new Exception("Could not delete file");
		}
	}

	/**
	 * Removes a file record from the database. Does not remove file from
	 * 		file system.
	 * @see function deleteFile (deletes file from file system)
	 * 
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param string $filePublicId: public_id of file to delete
	 * @param int $ownerId: ID of user who owns file to be deleted
	 * 
	 * @return void
	 *
	 * @throws mysqli_sql_exception: Thrown when an unexpected database
	 * 		error is encountered
	 */
	function deleteFileRecord($mysqliLink, $filePublicId, $ownerId) {
		if($s=$mysqliLink->prepare("
			DELETE FROM
				FILES
			WHERE
				PUBLIC_ID = ?
				AND OWNER_ID = ?
		")) {
			$s->bind_param('si',$filePublicId, $ownerId);
			if(!$s->execute()){throw new mysqli_sql_exception("Error while deleting record");}
			$s->close();
		} else {throw new mysqli_sql_exception("Error while preparing delete statement", 1);}
	}

	/**
	 * Retreive list of all files owned by the given user.
	 * 
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param int $ownerId: numeric ID of user whose files will be dumped
	 * 
	 * @return array: Array of associative arrays containing all files owned
	 * 		by the given user
	 *
	 * @throws mysqli_sql_exception: Thrown if an unexpected databse issue
	 * 		is encountered
	 */
	function dumpFiles($mysqliLink,$ownerId) {
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				NAME,
				FNAME,
				HASTHUMB,
				DATE_FORMAT(ENTRY_DATE,'%d %b %Y') AS EDATE,
				TAGS
			FROM
				FILES
			WHERE
				OWNER_ID = ?
			ORDER BY
				ENTRY_DATE DESC")){
			$s->bind_param("i",$ownerId);
			$s->bind_result($id,$name,$fname,$hasThumb,$edate,$tags);
			if($s->execute()){
				$out = array();
				while($s->fetch()) {
					array_push($out,array(
							"id" => $id,
							"title" => $name,
							"file" => $fname,
							"has_thumb" => $hasThumb,
							"edate" => $edate,
							"tags" => explode(',', $tags)
						));
				}
				$s->close();
				return $out;
			} else {throw new mysqli_sql_exception("Error while executing file dump statement");}
		} else {throw new mysqli_sql_exception("Error while preparing file dump statement");}
	}

	/**
	 * Get file extension from the given filename
	 * 
	 * @param  string $filename : filename to inspect
	 * @return string           : the extension of the input filename
	 */
	function getFileExtension($filename) {
		return pathinfo($filename,PATHINFO_EXTENSION);
	}

	/**
	 * Get fields for file with given PUBLIC_ID.
	 * 
	 * @param  mysqli $mysqliLink: MySQLi link to use in the query
	 * @param  string $filePublicId: PUBLIC_ID field for the file in question
	 * @param  int $ownerId: ID of the user who owns the file
	 * @return array: Associative array of query results, with the following keys
	 * 		public_id => PUBLIC_ID of the queried file
	 * 		name => name (title) of the queried file
	 * 		fname => filename of the queried file
	 * 		has_thumb => boolean integer indicating whether the file has a
	 * 			thumbnail
	 * 		tags => tags of the queried file
	 *
	 *  @throws InvalidArgumentException: Thrown if arguments for either of the
	 *  	parameters are not given.
	 *  @throws mysqli_sql_exception: Thrown if there is an error executing the
	 *  	SQL statement or fetching its results.
	 *  @throws UnexpectedValueException: Thrown if no record with the given PUBLIC_ID is found.
	 */
	function getFileRecord($mysqliLink, $filePublicId, $ownerId) {
		if(!isset($mysqliLink)) {throw new InvalidArgumentException("MySQLi link is undefined");}
		if(!isset($filePublicId)) {throw new InvalidArgumentException("File public ID is undefined");}

		$output = array();
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				NAME,
				FNAME,
				HASTHUMB,
				TAGS
			FROM
				FILES
			WHERE
				PUBLIC_ID = ?
				AND OWNER_ID = ?
		")) {
				$s->bind_param('si', $filePublicId, $ownerId);
				$s->bind_result($output['public_id'],$output['name'],$output['fname'],$output['has_thumb'],$output['tags']);
				if(!$s->execute()){
					throw new mysqli_sql_exception("Error while executing prepared statement");
				} else {
					$fetchResult = $s->fetch();
					if($fetchResult==true) {
						if(!($output['public_id'] && $output['fname'])) {
							throw new UnexpectedValueException("No file record found");
						}
					} else if($fetchResult==false) {
						throw new mysqli_sql_exception("Failed to fetch file record results: $mysqliLink->error");
					} else {
						throw new UnexpectedValueException("No file record found");
					}

					$s->close();
				}
		} else {
			throw new mysqli_sql_exception("Error while preparing statement");
		}

		return $output;
	}

	/**
	 * Create new file record with the given plain fields
	 *
	 * @see function associateFilename (update FNAME field to filename)
	 * @see function replaceFileWithUpload (upload file into user's uploads
	 *      directory)
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
		$fileExt = getFileExtension($_FILES[$fileSuperglobalKey]['name']);
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
	 * Update plain fields of file record with the given PUBLIC_ID
	 *
	 * @see function associateFilename (update FNAME field to filename)
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

?>