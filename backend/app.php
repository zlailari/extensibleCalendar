<?php
    
    require 'CRULcalendars.php';
    require 'database.php';
    require 'settings.php';
    
    require 'safeSession.php';
    startSessionSafe();
    
    
    
    if (!isset($_SESSION['userId'])) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $response = array("data"=>array(),"message"=>"You must login first!","success"=>false);	
            echo(json_encode($response));
            exit;
        } else {
            $response = array("data"=>array(),"message"=>"You must login first!","success"=>true);	
	    echo(json_encode($response));
            exit;
        }
    } else {
        $calendar = new CRULcalendars($mysqli);
        echo($calendar->processRequest());
    }  
?>