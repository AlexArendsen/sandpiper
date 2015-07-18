<?php
	
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
				TAGS
			FROM
				FILES
			WHERE
				PUBLIC_ID = ?
				AND OWNER_ID = ?
		")) {
				$s->bind_param('si', $filePublicId, $ownerId);
				$s->bind_result($output['public_id'],$output['name'],$output['fname'],$output['tags']);
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
	 * Verify the integrity of the given password hash
	 * 
	 * @param  string $passhash: bCrypt hash of password
	 * @return void
	 *
	 * @throws InvalidArgumentException: Thrown if password integrity cannot be
	 * 		verified.
	 */
	function verifyPasswordIntegrity($passhash) {
		if(strlen($passhash)!=60){
			throw new InvalidArgumentException("Password integrity could not be verified");
			
		}
	}

	/**
	 * @param mysqli $mysqliLink: MySQLi link to database
	 * @param string $userPublicId: public_id of user to get
	 * @return array: Associative array of user record fields with the following
	 * 		keys:
	 * 			public_id => The user's public ID
	 * 			username => The user's username
	 * 			password => bCrypt hash of the user's password
	 * 			isAdmin => Boolean integer indicating whether or not the user
	 * 				is an administrator
	 *
	 * @throws mysqli_sql_exception: Thrown if there is an error executing the
	 *  	SQL statement or fetching its results.
	 */
	function getUserRecord($mysqliLink, $userPublicId) {
		if($s=$mysqliLink->prepare("
			SELECT
				PUBLIC_ID,
				USERNAME,
				PASSWORD,
				ISADMIN
			FROM
				USERS
			WHERE
				PUBLIC_ID = ?"
		)) {
			$s->bind_param('s',$userPublicId);
			$out = array();
			$s->bind_result($out['public_id'],$out['username'],$out['password'],$out['isAdmin']);
			if(!($s->execute() && $s->fetch())){
				throw new mysqli_sql_exception("Error while executing user record retreival statement");
			}
			$s->close();
			return $out;
		} else {throw new mysqli_sql_exception("Error while preparing user record retreival statement");}
	}

?>