<?php

    require 'safeSession.php';
    startSessionSafe();
    
    session_destroy(); # destroy the session
    exit();
?>