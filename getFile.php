<?php 

	require_once 'init.php';

	if(!$arg['loggedIn']) {
		echo error("Access denied");
	} else {
		if($s=$i->prepare("
			SELECT
				PUBLIC_ID,
				NAME,
				FNAME,
				TAGS
			FROM
				FILES
			WHERE
				OWNER_ID = ?
				AND PUBLIC_ID = ?
			ORDER BY
				ENTRY_DATE DESC")){
			$s->bind_param("is",$arg['user'],$_GET['fileId']);
			$s->bind_result($id,$name,$fname,$tags);
			if($s->execute() && $s->fetch()){
				$out = array(
					"success" => true,
					"fileInfo" => array(
						"id" => $id,
						"title" => $name,
						"path" => $fname,
						"tags" => explode(',', $tags)
					));
				echo json_encode($out);
			} else {
				echo error("Server error (failed to execute statement)");
			}
		} else {
			echo error("Server error (failed to prepare statement)");
		}
	}

?>