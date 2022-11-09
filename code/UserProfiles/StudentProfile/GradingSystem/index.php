<?php
$root_path = '../../../../';
$profile_path='../../';
require $root_path . 'LibraryFiles/DatabaseConnection/config.php';
require $root_path . 'LibraryFiles/URLFinder/URLPath.php';
require $root_path . 'LibraryFiles/SessionStore/session.php';
require $root_path . 'LibraryFiles/Utility/Utility.php';
require $root_path . 'LibraryFiles/ValidationPhp/InputValidation.php';
session::profile_not_set($root_path);
$validate=new InputValidation();
$classCode = $_SESSION['class_code'];
$email=new EmailValidator($_SESSION['email']);
$authentication = $database->performQuery("SELECT * FROM student_classroom WHERE email='".$email->get_email()."' and class_code='$classCode'");
if ($authentication->num_rows == 0) {
  session::redirectProfile('student');
}

$allPost = $database->performQuery("SELECT * FROM post WHERE active='1';");
foreach($allPost as $j){
  $i=$j['post_id'];
  if(isset($_REQUEST[$i.'POST'])){
    $database->performQuery("DELETE FROM post WHERE post_id='$i'");
  }
}
$allComments = $database->performQuery("SELECT * FROM comments WHERE active='1';");
foreach($allComments as $j){
  $i=$j['comment_id'];
  if(isset($_REQUEST[$i.'COMMENT'])){
    $database->performQuery("DELETE FROM comments WHERE comment_id='$i'");
  }
}

$database->fetch_results($classroom_records,"SELECT * FROM classroom WHERE class_code = '$classCode' and active='1'");
$database->fetch_results($teacher_records,"SELECT * FROM users,teacher_classroom,classroom WHERE users.email=teacher_classroom.email and classroom.class_code='$classCode'");
if (isset($_REQUEST['post_msg']) && !is_null($_REQUEST['post_value'])) {
  $post_date = date('Y-m-d H:i:s');
  $post_id = $utility->generateRandomString(50);
  while (($database->performQuery("SELECT * FROM post WHERE post_id = '$post_id'"))->num_rows > 0) {
    $post_id = $utility->generateRandomString(50);
  }

  $post_value = $validate->post_sanitise_text('post_value');
  if (!is_null($post_value) && $post_value !== '') {
    $database->performQuery("INSERT INTO post(post_id,email,post_datetime,post_message) VALUES('$post_id','".$email->get_email()."','$post_date','$post_value');");
    $database->performQuery("INSERT INTO post_classroom(post_id,class_code) VALUES('$post_id','$classCode');");
  }
}

$posts = $database->performQuery("SELECT * FROM post,post_classroom WHERE post.post_id=post_classroom.post_id and post_classroom.class_code='$classCode' and active='1' order by post_datetime desc;");
foreach ($posts as $i) {
  $post_id = $i['post_id'];
  if (isset($_REQUEST[$post_id . 'comment_msg'])) {
    $comment_date = date('Y-m-d H:i:s');
    $comment_id = $utility->generateRandomString(50);
    while (($database->performQuery("SELECT * FROM comments WHERE comment_id = '$comment_id'"))->num_rows > 0) {
      $comment_id = $utility->generateRandomString(50);
    }
    $comment_text = $validate->post_sanitise_text($post_id . 'comment_text');
    if (!is_null($comment_text) && $comment_text !== '') {
      $database->performQuery("INSERT INTO comments(comment_id,email,post_id,comment_datetime,comment_message) VALUES('$comment_id','".$email->get_email()."','$post_id','$comment_date','$comment_text');");
    }
    unset($_REQUEST[$post_id . 'comment_msg']);
  }
}
$allPost = $database->performQuery("SELECT * FROM post WHERE active='1'");
$allComments = $database->performQuery("SELECT * FROM comments WHERE active='1'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Classroom</title>
  <link rel="icon" href="<?php echo $root_path; ?>title_icon.jpg" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="<?php echo $root_path; ?>css/bootstrap.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <link href="<?php echo $root_path; ?>boxicons-2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script defer src="script.js"></script>
</head>

<body>

  <script src="<?php echo $root_path; ?>js/bootstrap.js"></script>
  <div class="main-container d-flex">
    <?php
    require $profile_path . 'navbar.php';
    student_navbar($root_path);
    ?>
    <section class="content-section m-auto px-1 w-75">
      <div class="container bg-white rounded mt-5 mb-5"></div>
      <div class="px-3 me-3 d-flex flex-row-reverse">
        <button type="button" class="btn btn-outline-primary btn-join d-flex p-4 py-3" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@fat"><b>Join new classroom</b></button>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Join classroom</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form action="" method="POST">
                  <div class="mb-3">
                    <input type="text" name="classCode" class="form-control" placeholder="Enter classroom code" aria-label="Leave a comment">
                  </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <input type="submit" name="Join" value="Join" class="btn btn-primary btn-join">
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div id="error" style="color:red">
        <?php echo $error; ?>
      </div>
      <div class="container bg-white rounded m-auto justify-content-center mt-5 mb-5"></div>
      <!-- <h2 class="fs-5">Profile</h2> -->
      <div class="row justify-content-start m-auto">
        <?php
        foreach ($classrooms as $i) {
          $classCode = $i['class_code'];
          $database->fetch_results($instructor_name, "select name from users where email in (select teacher.email from teacher,teacher_classroom where teacher.email=teacher_classroom.email and class_code='$classCode')");
        ?>
          <div class="card-element col-lg-4 col-md-6 p-4 px-2">
            <div class="card card-box-shadow">
              <div class="card-header  task-card justify-content-around" style="height:100px">
                <div class="row">
                  <h4 class="card-title col py-2"><?php echo $i['course_code'] . ": " . $i['classroom_name']; ?></h4>
                  <?php $card = $i['class_code']; ?>
                  <div class="dropdown col-lg-auto col-sm-6 col-md-3 py-3">
                    <i onclick="<?php echo $card; ?>dropdownbtn()" class="<?php echo $card; ?>dropbtn bx bx-dots-horizontal-rounded"></i>
                    <form name='view_leave<?php echo $card; ?>' action='' method='POST'>
                      <div id="<?php echo $card; ?>myDropdown" class="<?php echo $card; ?>dropdown-content dropdown-menu">
                        <input type="submit" value="View Details" name='view<?php echo $card; ?>' class="dropdown-item">
                        <input type="submit" value="Leave Classroom" name='leave<?php echo $card; ?>' class="dropdown-item">
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <p class="card-text"><?php
                                      $class_code = $i['class_code'];
                                      $database->fetch_results($row, "SELECT * FROM classroom_creator,users WHERE classroom_creator.email=users.email AND class_code='$class_code'");
                                      ?></p>
                <p class="card-text"><?php echo "Created By: " . $row['name']; ?></p>
              </div>
              <form action="" method="POST">
                <div class="pb-5 px-5"><input type="submit" name="<?php echo $i['class_code'] ?>" value="Enter Class" class="btn btn-primary btn-go" /></div>
              </form>
            </div>
          </div>
        <?php
        }
        ?>
      </div>
    </section>
  </div>
  </div>
</body>

</html>