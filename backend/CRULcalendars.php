<?php

require 'settings.php';


class CRULcalendars {
    public $mysqli, $userId, $respond;
    
    public function __construct($mysqliArg) {
	
	$this->mysqli = $mysqliArg;
	if($this->mysqli->connect_errno) {
		printf("Connection Failed: %s\n", $this->mysqli->connect_error);
		exit;
	}
	
	$this->userId = $_SESSION['userId'];
	
    }

    
    public function processRequest() {
	// Parse request:
	$requestMethod = $_SERVER['REQUEST_METHOD'];
	    
	
	if ($requestMethod == 'GET') {
	    $requestData = explode("?",basename($_SERVER['HTTP_HOST'].htmlentities($_SERVER["REQUEST_URI"])));
	    $requestType = $requestData[0];
	    
	    if ($requestType == "calendars") {
		$calendars = $this->getCalendars($this->userId);
		$calendarsResponse = array("calendars"=>$calendars);	
		return json_encode($calendarsResponse);
	    } elseif ($requestType == "events") {
		$events = $this->getEvent($this->userId);
		$eventsResponse = array("data"=>$events,"message"=>"Loaded data","success"=>true);	
		return json_encode($eventsResponse);
	    
	    } elseif ($requestType == "users") {
		$users = $this->getAllUsers($this->userId);
		return json_encode($users);
	    }
	} elseif ($requestMethod == 'POST') {
	    $requestData = explode("/",$_SERVER["REQUEST_URI"]);
	    $requestType = $requestData[count($requestData) - 2];
	    
	    if ($requestType == "events") {
		// ADD EVENT
		$params = $this->decodeReqeust();
		$eventArray = array();
		$eventArray['eventTitle'] = $params['title'];
		$eventArray['eventStart'] = $params['start'];
		$eventArray['eventEnd'] = $params['end'];
		$eventArray['userId'] = $this->userId;
		$eventArray['calendarId'] = $params['cid'];	
		$params['id'] = $this->addEvent($eventArray);
		return  json_encode($params);
	    } else {
		// DELETE ALL EVENT WITH THIS CALENDAR ID :
		$calendarId = htmlentities($_POST['calendarId']);
		
		$this->deleteEventsByCalendarId($calendarId);
		
		if ($this->deleteCalendar($calendarId)) {
		    $response = array("data"=>array(),"message"=>"Calendar deleted","success"=>true);
		} else {
		    $response = array("data"=>array(),"message"=>"Calendar deleted","success"=>false);
		}
		return  json_encode($response);
	    }
	} elseif ($requestMethod == 'PUT') {
	    $params = $this->decodeReqeust();
	    $eventArray = array(); 
	    
	    $eventArray['eventTitle'] = $params['title'];
	    $eventArray['eventStart'] = $params['start'];
	    $eventArray['eventEnd'] = $params['end'];
	    $eventArray['userId'] = $this->userId;
	    $eventArray['eventId'] = $params['id'];
	    $eventArray['calendarId'] = $params['cid'];
	    $this->updateEvent($eventArray);
	    
	    $eventsResponse = array("data"=>$params,"message"=>"Loaded data","success"=>true);
	    return  json_encode($eventsResponse);
	
	} elseif ($requestMethod == 'DELETE') {	
	    $params = $this->decodeReqeust();
	    $eventArray = array(); 	
	    $eventArray['userId'] = $this->userId;
	    $eventArray['eventId'] = $params['id'];
	    $this->deleteEvent($eventArray);
	    $eventsResponse = array("data"=>array(),"message"=>"event deleted","success"=>true);
	    return  json_encode($eventsResponse);
	} else {
	    return "ERROR: Other request was sent to backend";
	}
    }
    
    function decodeReqeust() {
	$raw  = '';
	$httpContent = fopen('php://input', 'r');
	while ($kb = fread($httpContent, 1024)) {
	    $raw .= $kb;
	}
	fclose($httpContent);
	
	$eventArray = array();
	$params = json_decode(stripslashes($raw), true);
	return $params;
    }
    
     function deleteEvent($eventArray) {
	$stmt = $this->mysqli->prepare("DELETE FROM events WHERE id=?");
	  
	if(!$stmt){
            printf("Query Prep Failed: %s\n", $this->mysqli->error);
            exit;
	}
        
	$stmt->bind_param('i', $eventArray['eventId']);
        
        $deleteEventSucceed = $stmt->execute();
        
        $stmt->close();
    }
    
    
    function deleteEventsByCalendarId($calendarId) {
	$stmt = $this->mysqli->prepare("DELETE FROM events WHERE calendarId=?");
	  
	if(!$stmt){
            printf("Query Prep Failed: %s\n", $this->mysqli->error);
            exit;
	}
        
	$stmt->bind_param('i', $calendarId);
        
        $deleteEventSucceed = $stmt->execute();
        
        $stmt->close();
    }
    
    function deleteCalendar($calendarId) {
	$stmt = $this->mysqli->prepare("DELETE FROM calendars WHERE id=?");
	if(!$stmt){
            printf("Query Prep Failed: %s\n", $this->mysqli->error);
            exit;
	}
        
	$stmt->bind_param('i', $calendarId);
        
        $deleteCalendarSucceed = $stmt->execute();
        
        $stmt->close();
	return $deleteCalendarSucceed;
    }
    
    function updateEvent($eventArray) {
	 $stmt = $this->mysqli->prepare("UPDATE events SET start=?, end=?, userId=?, eventTitle=?, calendarId=? WHERE id=?");
	  
	if(!$stmt){
            printf("Query Prep Failed: %s\n", $this->mysqli->error);
            exit;
	}
        
	$stmt->bind_param('ssisii',$eventArray['eventStart'], $eventArray['eventEnd'],
				    $eventArray['userId'], $eventArray['eventTitle'],
				    $eventArray['calendarId'], $eventArray['eventId']);
        
        $updateNewEventSucceed = $stmt->execute();
        
        $stmt->close();
    }
    
    
    
    function addEvent($eventArray) {
	
        $stmt = $this->mysqli->prepare("INSERT INTO events (start, end, userId, eventTitle, calendarId) values (?, ?, ?, ?, ?)");
	  
	if(!$stmt){
            printf("Query Prep Failed: %s\n", $this->mysqli->error);
            exit;
	}
        
	$stmt->bind_param('ssisi',$eventArray['eventStart'], $eventArray['eventEnd'],
				    $eventArray['userId'], $eventArray['eventTitle'],
				    $eventArray['calendarId']);
        
        $addNewEventSucceed = $stmt->execute();
        
        $stmt->close();
	
	return $this->mysqli->insert_id;
    }
    
    function getEvent($userId) {
	
	$events = array();
	
	//prepared statemnt
	$stmt = $this->mysqli->prepare("SELECT id, eventTitle, start, end, userId, calendarId FROM events WHERE userId=?");

	//bind parameter
	$stmt->bind_param('i', $userId);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($eventId, $eventTitle, $eventStart, $eventEnd, $userId, $calendarId);
	
	while ($stmt->fetch()) {
	    $event = array("cid"=>htmlentities($calendarId),
			    "id"=>htmlentities($eventId),
			    "start"=>htmlentities($eventStart),
			    "end"=>htmlentities($eventEnd),
			    "title"=>htmlentities($eventTitle));
	    array_push($events,$event);
	}
	
	$stmt->close();
	return $events;
    }
    
    function getAllUsers($userId) {
	$users = array();
	//prepared statemnt
	$stmt = $this->mysqli->prepare("SELECT id, userName FROM users WHERE id != ?");

	//bind parameter
	$stmt->bind_param('i', $userId);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($userId, $userName);
	
	while ($stmt->fetch()) {
	    $user = array("id"=>htmlentities($userId),
			    "userName"=>htmlentities($userName));
	    array_push($users,$user);
	}
	
	$stmt->close();
	return $users;
    }
    
    function createSharedCalendar($newCalendarName, $userId) {
	

	//prepared statemnt
	$stmt = $this->mysqli->prepare("SELECT id FROM calendars WHERE calendarName=? AND userId=?");

	//bind parameter
	$stmt->bind_param('si', $newCalendarName, $userId);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($calendarId);
	$stmt->fetch();
	$stmt->close();
	
	//if (mysqli_stmt_num_rows($stmt) > 0) {
	
	if ($calendarId != 0) {
	    return $calendarId;
	} else {
	


	    // Create a new Calendar with the given name:
	     $stmt = $this->mysqli->prepare("insert into calendars (userId, calendarName, color) values(?,?,?)");
	  
	    if(!$stmt){
		printf("Query Prep Failed: %s\n", $this->mysqli->error);
		exit;
	    }
	    
	    $SHARED_CALENDAR_COLOR = "6";
	    
	    $stmt->bind_param('isi',$userId, $newCalendarName, $SHARED_CALENDAR_COLOR);
	    $stmt->execute();
	    $stmt->close();
	    
	    return $this->mysqli->insert_id;
	}
    }
    
    function shareEvent($eventId, $senderUserName, $recieverUser) {
	$calendarId = $this->createSharedCalendar($senderUserName, $recieverUser);
    
	//prepared statemnt
	$stmt = $this->mysqli->prepare("SELECT id, eventTitle, start, end FROM events WHERE id=?");

	//bind parameter
	$stmt->bind_param('i', $eventId);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($eventId, $eventTitle, $eventStart, $eventEnd);
	$stmt->fetch();
	
	$event = array("calendarId"=>htmlentities($calendarId),
			"eventStart"=>htmlentities($eventStart),
			"eventEnd"=>htmlentities($eventEnd),
		        "eventTitle"=>htmlentities($eventTitle),
			"userId"=>htmlentities($recieverUser));
	
	$stmt->close();
	
	$this->addEvent($event);
	return $event;
    }
    
    
    
     function getCalendars($userId) {
	$calendars = array();
	
	//prepared statemnt
	$stmt = $this->mysqli->prepare("SELECT id, calendarName, color FROM calendars WHERE userId=?");

	//bind parameter
	$stmt->bind_param('i', $userId);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($calendarId, $calendarName, $calendarColor);
	
	while ($stmt->fetch()) {
	    $calendar = array("id"=>htmlentities($calendarId),
			    "title"=>htmlentities($calendarName),
			    "color"=>htmlentities($calendarColor));
	    array_push($calendars,$calendar);
	}
	
	$stmt->close();
	return $calendars;
        
    }
}
?>