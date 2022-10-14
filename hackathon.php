<?php

if (isset($_POST['uniqueUN']))
    $sUsername = $_POST['uniqueUN'];
else if (isset($_POST['login']))
    $jsonCredentials = $_POST['login'];
else if (isset($_POST['createAccount']))
    $jsonUserData = $_POST['createAccount'];
else if (isset($_POST['postComment']))
    $jsonComment = $_POST['postComment'];
else if (isset($_POST['genSnowflake']))
    $sSnowflake = $_POST['genSnowflake'];

if ($sUsername)
    $sFeedback = uniqueUN ($sUsername);
else if ($jsonCredentials)
    $sFeedback = login ($jsonCredentials);
else if ($jsonUserData)
    $sFeedback = createAccount ($jsonUserData);
else if ($jsonComment)
    $sFeedback = postComment ($jsonComment);
else if ($sSnowflake)
    $sFeedback = generateSnowflake ();

echo $sFeedback;

// c
function uniqueUN ($sUsername) {
    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmt = $dbconnect->prepare("SELECT * FROM Users WHERE username=?");
    $stmt->bind_param("s", $sUsername);
    $stmt->execute();
    $tResult = $stmt->get_result();
    $stmt->close();
    $dbconnect->close();

    return 0 == $tResult->num_rows ? $sUsername : null;
}

// c
function login ($jsonCredentials) {
    $objCredentials = json_decode($jsonCredentials);

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmt = $dbconnect->prepare("SELECT * FROM Users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $objCredentials->username, $objCredentials->password);
    $stmt->execute();
    $tResult = $stmt->get_result();
    $row = $tResult->fetch_assoc();
    $stmt->close();
    $dbconnect->close();

    if (1 != $tResult->num_rows)
        return false;

    $sSQL = "UPDATE Users SET lastlogin=CURRENT_TIMESTAMP WHERE id=" . $row["id"];
    QueryDB ($sSQL);

    $objUserData = new stdClass();
    $objUserData->id= $row["id"];
    $objUserData->karma= $row["karma"];

    return json_encode($objUserData);
}

// c
function createAccount ($jsonUserData) {
    $objUserData = json_decode($jsonUserData);

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmtI = $dbconnect->prepare("INSERT INTO Users (username, password) VALUES (?, ?)");
    $stmtI->bind_param("ss", $objUserData->username, $objUserData->password);
    $stmtI->execute();
    $stmtI->close();

    $stmtS = $dbconnect->prepare("SELECT id FROM Users WHERE username=?");
    $stmtS->bind_param("s", $objUserData->username);
    $stmtS->execute();
    $tResult = $stmtS->get_result();
    $row = $tResult->fetch_assoc();
    $dbconnect->close();

    return $row["id"];
}

function postComment ($jsonComment) {
    $objCommentData = json_decode($jsonComment);

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmtI = $dbconnect->prepare("INSERT INTO Comments (uuid, dentry, prev, tags) VALUES (?, ?, ?, ?)");
    $stmtI->bind_param("isis", $objCommentData->uuid, $objCommentData->dentry, $objCommentData->prev, $objCommentData->tags);
    $stmtI->execute();
    $stmtI->close();

    $dbconnect->close();
}

function checkUniqueTag ($sTag) {
    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmt = $dbconnect->prepare("SELECT * FROM Tags WHERE dentry=?");
    $stmt->bind_param("s", $sTag);
    $stmt->execute();
    $tResult = $stmt->get_result();
    $stmt->close();
    $dbconnect->close();

    return 0 == $tResult->num_rows ? $sTag : null;
}

// c
function getUserIPAddr() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) // ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) // ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}

// c
function generateSnowflake () {
    $seed = str_split('abcdefghijklmnopqrstuvwxyz'
        .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        .'0123456789');
    $rand = '';
    foreach (array_rand($seed, 10) as $k) $rand .= $seed[$k];
    return $rand;
}

// c
function QueryDB ($sSQL) {
    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);
    $Result = $dbconnect->query($sSQL);
    $dbconnect->close();
    return $Result;
}

?>
