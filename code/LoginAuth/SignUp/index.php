<?php 
$root_path='../../';
include $root_path.'LibraryFiles/DatabaseConnection/config.php';
include $root_path.'LibraryFiles/SessionStore/session.php';
include $root_path.'LibraryFiles/ValidationPhp/InputValidation.php';
session::create_or_resume_session();

session::stay_in_session();

$error=$_SESSION['error'];


if (isset($_POST['submit'])) {
	$name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_SPECIAL_CHARS);
    $email=new EmailValidator(filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL));
    $password=new PasswordValidator(filter_input(INPUT_POST,'password',FILTER_SANITIZE_SPECIAL_CHARS));
    $dob=$_POST['dob'];
    $validate=new InputValidation();
    $institutions = filter_input(INPUT_POST,'institutions',FILTER_SANITIZE_SPECIAL_CHARS);
    $button_radio=filter_input(INPUT_POST,'btnradio',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirm= $_POST['cfpassword'];
    $check=$_POST['check_1'];
    $error=$_REQUEST['error'];
    $exists = "SELECT * FROM users WHERE email = '".$email->get_email()."'";
    $result=$database->performQuery($exists);
    if ($result->num_rows > 0) {
        $email='';
        $password='';
        $_REQUEST['password']='';
        $error="An account already exists with this email";
        
    }
    else if(!$email->email_validate() || !$password->password_match($confirm) || !$password->constraint_check() || !is_null($validate->Date_Validation($dob))){
        $email->email_validate();
        $password->constraint_check();
        $password->password_match($confirm);
    }
    else{
        $insertusers = "INSERT INTO users(email,name,password,institution,dob) VALUES ('".$email->get_email()."', '$name','".$password->get_password()."','$institutions','$dob')";
        $insertTable="INSERT INTO $button_radio(email) VALUES('".$email->get_email()."')";
        $_SESSION['email']=$email->get_original_email();
        unset($_POST['password']);
        unset($_POST['email']);	
        unset($_POST['cfpassword']);
        unset($institutions);
        unset($button_radio);
        unset($confirm);
        unset($check);
        unset($name);
        unset($dob);
        unset($error);
        unset($email);
        unset($password);
        unset($validate);
        unset($_SESSION['error']);
        $database->performQuery($insertusers);
        $database->performQuery($insertTable);
        $database->fetch_results($row,$exists);
        $_SESSION['name'] = $row['name'];
        header('Location: ConfirmEmail/index.php');
		
    }   
}
?>

<!DOCTYPE HTML>
<html>
<head>
<title>
    SignUp
</title>
<link rel="icon" href="<?php echo $root_path; ?>title_icon.jpg" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="<?php echo $root_path; ?>css/bootstrap.css" />
<script defer src="script.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://kit.fontawesome.com/d0f239b9af.js" crossorigin="anonymous"></script>
</head>
<body>
<script src="<?php echo $root_path; ?>js/bootstrap.js"></script>
<div class="container col-md-4 mb-5 mt-5">
    <div class="myCard">
    <div class="row">
        <div class="col-md">
            <div class="myLeftCtn">
                <form id="form" class="myForm text-center" action="" method="POST">
                    <header>Create New Account</header>
                    <div class="form-group">
                        <div class="btn-group col" role="group" aria-label="Basic radio toggle button group">

                            <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked value="<?php $button_radio="teacher";echo $button_radio; ?>">
                            <label class="btn btn-outline-primary" for="btnradio1">As Teacher</label>
                          
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" value="<?php $button_radio="student";echo $button_radio; ?>">
                            <label class="btn btn-outline-primary" for="btnradio2">As Student</label>
                          </div>
                    </div>
                    <div class="form-group" id="error" style="color:red;display:<?php
                        if(is_null($error)){
                            echo "none";
                        }
                        else{
                            echo "block";
                        }
                    ?>">
                            <?php echo $error;unset($error)?>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-user"> </i>
                        <input class="myInput" type="text" placeholder="Full Name" name="name" id="name" required>
                        <div class="invalid-feedback">Please fill out this field.</div>
                    </div>
                    <div class="form-group" id="error" style="color:red;display:<?php
                        if(is_null($email->error)){
                            echo "none";
                        }
                        else{
                            echo "block";
                        }
                    ?>">
                            <?php echo $email->error;?>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-envelope"> </i>
                        <input class="myInput" placeholder="Email" name="email" type="text" id="email" required value="<?php echo $_REQUEST['email']; ?>">
                        <div class="invalid-feedback">Please fill out this field.</div>
                    </div>
                    <div class="form-group">
                    <i class="fas fa-graduation-cap"></i>
                        <input class="myInput" type="text" name="institution" placeholder="Institution" id="institution" required value="<?php echo $institutions; ?>">
                        <div class="invalid-feedback">Please fill out this field.</div>
                    </div>
                    <div id="passwordError" class="form-group" style="color:red;display:<?php
                        if(is_null($password->password_error)){
                            echo "none";
                        }
                        else{
                            echo "block";
                        }
                    ?>">
                            <?php echo $password->password_error;?>

                        </div>
                    <div class="form-group">
                        <i class="fas fa-lock"> </i>
                        
                        <input class="myInput" placeholder="Password" type="password" id="password" name="password" required value="<?php echo $_POST['password']; ?>">
                        <i class="fas fa-eye-slash" id="togglePassword"></i>
                    </div>
                    <div id="confirmPasswordError" class="form-group" style="color:red;display:<?php
                        if(is_null($password->confirm_error)){
                            echo "none";
                        }
                        else{
                            echo "block";
                        }
                    ?>">
                            <?php echo $password->confirm_error;?>

                        </div>
                    <div class="form-group">
                        <i class="fas fa-lock"> </i>
                        <input class="myInput" placeholder="Confirm Password" type="password" id="cfpassword" name="cfpassword" required value="<?php echo $_POST['cfpassword']; ?>">
                        <i class="fas fa-eye-slash" id="togglePassword2"></i>
                    </div>
                    <div id="dateError" class="form-group" style="color:red;display:<?php
                        if(isset($validate) && is_null($validate->Date_Validation($dob))){
                            echo "none";
                        }
                        else if(isset($validate)){
                            echo "block";
                        }
                    ?>">
                            <?php 
                            if(isset($validate)){
                                echo $validate->Date_Validation($dob);
                            }
                            ?>

                        </div>
                    <div class="form-group">
                    <i class="fas fa-calendar-days"></i>
                        <span class="hovertext" data-hover="Enter Date Of Birth">
                            <input class="myInput" type="date" name="dob" id="dob" onclick="
                            var dateString=new Date().toLocaleDateString('en-ca');
                            this.setAttribute('max',dateString);" required value="<?php echo $dob ?>">
                        </span>
                    </div>
                    
                    
                    <div class="form-group">
                        <label>
                            <input id="check_1" name="check_1" type="checkbox" required>
                            <small>
                                I read and agree to <a href="TermsAndConditions.html" target="_blank">Terms & Conditions<a>
                            </small>
                        <div class="invalid-feedback">You must check the box.</div>
                        </label>
                    </div>
                    <div class="form-group">
                        <p>Already have an account? <a href="../Login/index.php">LOGIN</a></p>  
                    </div>
                    <input type="submit" value="Submit" class="butt" name="submit">             
                </form>
            </div>
        </div>

    </div>
    </div>
</div>
</body>
</html>
