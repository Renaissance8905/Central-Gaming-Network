<?php
/**
 * Created by PhpStorm.
 * User: cjs2599
 * Date: 12/3/15
 * Time: 7:27 PM
 */

require_once("dbconn.php");


$usr = $_POST["username"];
$pass = $_POST["password"];
$cpass = $_POST["c_password"];
$usr = sanitize($usr);
$pass = sanitize($pass);
$pass = md5($pass);
$cpass = md5($cpass);



if ($_POST["request"] == "register" && isset($_POST["username"])) {
    register($usr, $pass, $cpass);
}
elseif ($_POST["request"] == "login" && isset($_POST["username"])) {
    login($usr, $pass);
}
elseif ($_POST["request"] == "checklogin") {
    isLoggedIn();
}
elseif ($_POST["request"] == "logout") {
    logout();
}
else {
    die("bad request");
}



function register ($usr, $pass, $cpass) {

    if ($pass != $cpass) {
        die("Mismatched passwords");
    }

    $table = "fp_users";

    $conn = conn();

    $stmt = mysqli_prepare($conn, "INSERT INTO $table (username, password) VALUES (?,?)");
    mysqli_stmt_bind_param($stmt, 'ss', $usr, $pass);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    login($usr, $pass);
}


function login ($usr, $pass) {
    $conn = conn();
    $table = "fp_users";

    $sql = "SELECT username, days_active, games_played, contact FROM $table WHERE username = \"$usr\" AND password = \"$pass\"";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = $result->fetch_row();
        $profile =  array("username"=>$row[0], "days_active"=>$row[1], "games_played"=>$row[2], "contact"=>$row[3]);
        $profile = (json_encode($profile));

        setcookie('loggedIn', $profile, time()+(86400*7), '/'); // Active 7 Days

        echo ($profile);

    } elseif (!mysqli_query($conn, $result)) {
        echo ("ERROR: " . mysqli_error($conn)) . " :: " . mysqli_errno($conn);
        return;
    } else {
        echo "login failed";
        return;
    }

    $result->free();
    $conn->close();
    return;
}

function isLoggedIn () {
    if (isset($_COOKIE['loggedIn'])) {
        $c = $_COOKIE['loggedIn'];

        setcookie('loggedIn', $c, time()+(86400*7), '/'); // Refresh Cookie

        echo ($c);
    } else {
        echo("INVALID");
    }
    return;
}


function logout () {
    if (isset($_COOKIE['loggedIn'])) {
        setcookie('loggedIn', null, 0, '/');
        echo("LOGGED OUT");
    } else {
        echo("NO LOGIN FOUND");
    }
    return;
}


?>