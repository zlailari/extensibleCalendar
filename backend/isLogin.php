<?php
    
    session_start();
    if (isset($_SESSION['userId'])) {
        echo json_encode(array(
			"success" => true,
			"username" => htmlentities($_SESSION['username']),
                        "tryToLogin" => false
		));
		exit;
    } else {
         echo json_encode(array(
			"success" => false,
                        "tryToLogin" => false
		));
		exit;
    }

?>