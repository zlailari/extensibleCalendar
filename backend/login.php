<?php 
	require 'database.php';
	
	require 'safeSession.php';
        startSessionSafe();
	
	header("Content-Type: application/json");
	
	
	$username = htmlentities($_POST['username']);
	$password = $_POST['password'];

	//prepared statemnt
	$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE userName=?");

	//bind parameter
	$stmt->bind_param('s', $username);
	$stmt->execute();

	//bind the results
	$stmt->bind_result($user_id, $pwd_hash);
	$stmt->fetch();

	// Compare the submitted password to the actual password hash
	if(crypt($password, htmlentities($pwd_hash))==htmlentities($pwd_hash)){
		$_SESSION['username'] = $username;
		$_SESSION['token'] = substr(md5(rand()), 0, 10);
		$_SESSION['userId'] = htmlentities($user_id);
		echo json_encode(array(
			"success" => true,
			"username" => $username,
			"tryToLogin" => true
		));
		exit;
	}else{
		echo json_encode(array(
			"success" => false,
			"message" => "Incorrect Useranme or Password",
			"tryToLogin" => true
		));
		exit;
	}
?>