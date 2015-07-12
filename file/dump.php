<?php

	require_once '../init.php';

	if(!$arg['loggedIn']) {
		echo error("Access Denied");
	} else {
		if($s=$i->prepare("
			SELECT
				ID,
				NAME,
				FNAME,
				DATE_FORMAT(ENTRY_DATE,'%d %b %Y') AS EDATE,
				TAGS
			FROM
				FILES
			WHERE
				OWNER_ID = ?
			ORDER BY
				ENTRY_DATE DESC")){
			$s->bind_param("i",$arg['user']);
			$s->bind_result($id,$name,$fname,$edate,$tags);
			if($s->execute()){
				$filesOut = array();
				while($s->fetch()){
					array_push($filesOut,array(
							"id" => $id,
							"name" => $name,
							"file" => $fname,
							"edate" => $edate,
							"tags" => explode(',', $tags)
						));
				}
				echo json_encode($filesOut);
			} else {
				echo error("Internal Error (Statment Execution)");
			}
		} else {
			echo error("Internal Error (Statement Preparation)");
		}
	}

?>