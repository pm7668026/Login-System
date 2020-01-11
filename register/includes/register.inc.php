<?php

session_start();

if (isset($_POST['submit'])) {

    require '../../assets/setup/db.inc.php';

    // var_dump($_POST);
    // var_dump( $_FILES['avatar']['name']);
    // exit();

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordRepeat  = $_POST['confirmpassword'];
    $gender = $_POST['gender'];
    $headline = $_POST['headline'];
    $bio = $_POST['bio'];
    $full_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];


    /*
    * -------------------------------------------------------------------------------
    *   Data Validation
    * -------------------------------------------------------------------------------
    */

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {

        $_SESSION['ERRORS']['formerror'] = 'required fields cannot be empty, try again';
        header("Location: ../");
        exit();
    } else if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {

        $_SESSION['ERRORS']['usernameerror'] = 'invalid username';
        header("Location: ../");
        exit();
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['ERRORS']['emailerror'] = 'invalid email';
        header("Location: ../");
        exit();
    } else if ($password !== $passwordRepeat) {

        $_SESSION['ERRORS']['passworderror'] = 'passwords donot match';
        header("Location: ../");
        exit();
    } else {

        $sql = "select id from users where username=?;";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("Location: ../?error=sqlerror");
            exit();
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            $resultCheck = mysqli_stmt_num_rows($stmt);

            if ($resultCheck > 0) {

                $_SESSION['ERRORS']['usernameerror'] = 'username already taken';
                header("Location: ../");
                exit();
            } else {

                /*
                * -------------------------------------------------------------------------------
                *   Image Upload
                * -------------------------------------------------------------------------------
                */

                $FileNameNew = '_defaultUser.png';
                $file = $_FILES['avatar'];

                if (!empty($_FILES['avatar']['name'])){

                    $fileName = $_FILES['avatar']['name'];
                    $fileTmpName = $_FILES['avatar']['tmp_name'];
                    $fileSize = $_FILES['avatar']['size'];
                    $fileError = $_FILES['avatar']['error'];
                    $fileType = $_FILES['avatar']['type']; 

                    $fileExt = explode('.', $fileName);
                    $fileActualExt = strtolower(end($fileExt));

                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    if (in_array($fileActualExt, $allowed)){

                        if ($fileError === 0){

                            if ($fileSize < 10000000){

                                $FileNameNew = uniqid('', true) . "." . $fileActualExt;
                                $fileDestination = '../../assets/uploads/users/' . $FileNameNew;
                                move_uploaded_file($fileTmpName, $fileDestination);

                            }
                            else {

                                $_SESSION['ERRORS']['imageerror'] = 'image size should be less than 10MB';
                                header("Location: ../");
                                exit(); 
                            }
                        }
                        else {

                            $_SESSION['ERRORS']['imageerror'] = 'image upload failed, try again';
                            header("Location: ../");
                            exit();
                        }
                    }
                    else {

                        $_SESSION['ERRORS']['imageerror'] = 'invalid image type, try again';
                        header("Location: ../");
                        exit();
                    }
                }


                /*
                * -------------------------------------------------------------------------------
                *   User Creation
                * -------------------------------------------------------------------------------
                */

                $sql = "insert into users(username, email, password, first_name, last_name, gender, 
                        headline, bio, profile_image, created_at) 
                        values ( ?,?,?,?,?,?,?,?,?, NOW() )";
                $stmt = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmt, $sql)) {

                    header("Location: ../?error=sqlerror");
                    exit();
                } else {

                    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

                    mysqli_stmt_bind_param($stmt, "sssssssss", $username, $email, $hashedPwd, $full_name, $last_name, $gender, $headline, $bio, $FileNameNew);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);

                    $_SESSION['STATUS']['signupsuccess'] = 'Account Created, please Login';
                    header("Location: ../../login/");
                    exit();
                }
            }
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} 
else {

    header("Location: ../");
    exit();
}
