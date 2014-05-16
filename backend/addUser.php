<?php
        require 'database.php';
        require 'settings.php';
    
        require 'safeSession.php';
        startSessionSafe();
        
        header("Content-Type: application/json");
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $hashed_pass = crypt($password);
        
        // Connect to the database
        // Add user
        $stmt = $mysqli->prepare("insert into users (userName, password) values(?,?)");
        if(!$stmt){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
        }
        // save to database
        $stmt->bind_param('ss', htmlentities($username), htmlentities($hashed_pass));
         
        $stmt->execute();
        
        $newUserId = $mysqli->insert_id;
        $stmt->close();
        
        // Add basic Home calendar for new user
      //  $sqlQuery = "insert into calendars (userId, calendarName, color) values('".)";
        //$stmt = $mysqli->prepare($sqlQuery);
       $stmt = $mysqli->prepare("insert into calendars (userId, calendarName, color) values(?,?,?)");
        if(!$stmt){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
        }
        // save to database
        $stmt->bind_param('sss', $newUserId, $DEAFAULT_CALENDAR_NAME, $DEFAULT_CALENDAR_COLOR);
         
        $stmt->execute();
        $stmt->close();
?>