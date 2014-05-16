<?php

    
    require 'database.php';
    require 'safeSession.php';
    startSessionSafe();
    
 //   if($_SESSION['token'] !== htmlentities($_POST['token'])) {
//	die("Request forgery detected");
 //   }
    
    header("Content-Type: application/json");
    
    $calendarName = htmlentities($_POST['calendarName']);
    $calendarColor = htmlentities($_POST['calendarColor']);
    $userId = htmlentities($_SESSION['userId']);
    
 //   if (!is_int($calendarColor)) {
 //       failedToAddCalendar();
 //   }
    
    // Add basic Home calendar for new user
    $sqlQuery = "insert into calendars (userId, calendarName, color) values(?,?,?)";
    $stmt = $mysqli->prepare($sqlQuery);
    
    
    //        $stmt = $mysqli->prepare("insert into calendars (userId, calendarName, color) values(?,?,?)");
    if(!$stmt){
           printf("Query Prep Failed: %s\n", $mysqli->error);
           exit;
    }
    
    $stmt->bind_param('isi', $userId, $calendarName, $calendarColor);
    
    
    if (!$stmt->execute()) {
        $stmt->close();
        failedToAddCalendar();
    }
    $stmt->close();
    echo json_encode(array(
			"success" => true,
                        "calendarName" => $calendarName
		));
    exit;
    
    function failedToAddCalendar() {
         echo json_encode(array(
			"success" => false,
			"message" => "Failed to add  calendar!"
		));
		exit;
        
    }

?>