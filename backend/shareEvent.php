<?php

    require 'CRULcalendars.php';
    require 'database.php';
    require 'settings.php';
    
    require 'safeSession.php';
    startSessionSafe();
    
    if (isset($_SESSION['userId'])) {
        $eventId = htmlentities($_POST['eventId']);
        $senderUserName = htmlentities($_POST['senderUserName']);
        $recieverUser = htmlentities($_POST['recieverUserId']);
        
        $calendar = new CRULcalendars($mysqli);
        echo(json_encode($calendar->shareEvent($eventId, $senderUserName, $recieverUser)));
    }  
?>