<?php
    $servername = "localhost";
    $username = "UserManager";
    $password = "12345678";
    $dbname = "user";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    error_reporting(0);

    session_start();

    if (isset($_SESSION['email'])) {
        $tableName=$_SESSION['tableName'];
        if($tableName==='teacher'){
            header('Location: ../TeacherProfile/index.php');
        }
        else{
            header('Location: ../StudentProfile/index.php');
        }
    }


?>
