<?php

if (isset($_POST['uniqueUN']))
    $sUsername = $_POST['uniqueUN'];
else if (isset($_POST['login']))
    $jsonCredentials = $_POST['login'];
else if (isset($_POST['createAccount']))
    $jsonUserData = $_POST['createAccount'];
else if (isset($_POST['postComment']))
    $jsonComment = $_POST['postComment'];
else if (isset($_POST['pullComment']))
    $nID = $_POST['pullComment'];
else if (isset($_POST['genSnowflake']))
    $sSnowflake = $_POST['genSnowflake'];
else if (isset($_POST['castVote']))
    $jsonVote = $_POST['castVote'];
else if (isset($_POST['sendFriendRequest']))
    $jsonIDs = $_POST['sendFriendRequest'];
else if (isset($_POST['denyFriendRequest']))
    $jsonDenyIDs = $_POST['denyFriendRequest'];
else if (isset($_POST['acceptFriendRequest']))
    $jsonAcceptIDs = $_POST['acceptFriendRequest'];
else if (isset($_POST['editComment']))
    $jsonEditedComment = $_POST['editComment'];
else if (isset($_POST['pullBurstComments']))
    $nPageNumber = $_POST['pullBurstComments'];
else if (isset($_POST['storeChat']))
    $jsonChat = $_POST['storeChat'];
else if (isset($_POST['pullChats']))
    $jsonChatIDs = $_POST['pullChats'];
else if (isset($_POST['searchByTag']))
    $sTagSearch = $_POST['searchByTag'];
else if (isset($_POST['search']))
    $sSearch = $_POST['search'];
else if (isset($_POST['getCommentChain']))
    $nParentID = $_POST['getCommentChain'];



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
else if ($nID)
    $sFeedback = pullComment ($nID);
else if ($jsonVote)
    $sFeedback = castAVote ($jsonVote);
else if ($jsonIDs)
    $sFeedback = sendFriendRequest ($jsonIDs);
else if ($jsonDenyIDs)
    $sFeedback = denyFriendRequest ($jsonDenyIDs);
else if ($jsonAcceptIDs)
    $sFeedback = acceptFriendRequest ($jsonAcceptIDs);
else if ($jsonEditedComment)
    $sFeedback = editComment ($jsonEditedComment);
else if ($nPageNumber)
    $sFeedback = pullBurstComments ($nPageNumber);
else if ($jsonChat)
    $sFeedback = storeChat ($jsonChat);
else if ($jsonChatIDs)
    $sFeedback = pullChats ($jsonChatIDs);
else if ($sTagSearch)
    $sFeedback = searchByTag ($sTagSearch);
else if ($sSearch)
    $sFeedback = search ($sSearch);
else if ($nParentID)
    $sFeedback = getCommentChain ($nParentID);

echo $sFeedback;

// c
function fetchTagNames ($aTagIDs) {
    for ($i=0; $i<count($aTagIDs); $i++) {
        $sSQL = "SELECT dentry FROM Tags WHERE id=" . $aTagIDs[$i];
        $sTag = QueryDB ($sSQL)->fetch_assoc()["dentry"];
        $aTagIDs[$i] = $sTag;
    }
    return json_encode ($aTagIDs);
}

// c
function fetchTagIDs ($aTagNames) {
    for ($i=0; $i<count($aTagNames); $i++) {
        $sSQL = "SELECT id FROM Tags WHERE dentry='" . $aTagNames[$i] . "'";
        $nTag = QueryDB ($sSQL)->fetch_assoc()["id"];
        $aTagNames[$i] = $nTag;
    }
    return $aTagNames;
}

// c
function pullChats ($jsonChatIDs) {
    $objIDs = json_decode ($jsonChatIDs);

    $sSQL = "SELECT * FROM Chats WHERE (t=" . $objIDs->to . " AND f=" . $objIDs->from . ") OR (t=" . $objIDs->from . " AND f=" . $objIDs->to . ")";
    $tResult = QueryDB ($sSQL);

    $aChats = [];
    for ($i=0; $i<$tResult->num_rows; $i++) {
        $row = $tResult->fetch_assoc();
        $aChats[$i] = new stdClass();
        $aChats[$i]->to = $row["t"];
        $aChats[$i]->from = $row["f"];
        $aChats[$i]->id = $row["id"];
        $aChats[$i]->dentry = $row["dentry"];
        $aChats[$i]->created = $row["created"];
    }
    return json_encode ($aChats);
}

// c
function sendFriendRequest ($jsonIDs) {
    $objIDs = json_decode ($jsonIDs);
    $sSQL = "INSERT INTO Friends (a, b) VALUES (" . $objIDs->from . ", " . $objIDs->to . ")";
    return QueryDB ($sSQL);
}

// c
function denyFriendRequest ($jsonDenyIDs) {
    $objIDs = json_decode ($jsonDenyIDs);
    $sSQL = "DELETE FROM Friends WHERE a=" . $objIDs->from . " AND b=" . $objIDs->to;
    return QueryDB ($sSQL);

}

// c
function acceptFriendRequest ($jsonAcceptIDs) {
    $objIDs = json_decode ($jsonAcceptIDs);
    $sSQL = "UPDATE Friends SET pending=0  WHERE a=" . $objIDs->from . " AND b=" . $objIDs->to;
    return QueryDB ($sSQL);
}

// c
function editComment ($jsonEditedComment) {
    $objEditedComment = json_decode($jsonEditedComment);

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmtI = $dbconnect->prepare("UPDATE Comments SET dentry=? WHERE id=?");
    $stmtI->bind_param("si", $objEditedComment->comment, $objEditedComment->id);
    $bStatus = $stmtI->execute();
    $stmtI->close();

    return $bStatus;
}

// c
function pullComment ($nID) {
    $sSQL = "SELECT * FROM Comments WHERE id=" . $nID;
    $tResult = QueryDB ($sSQL);
    $row = $tResult->fetch_assoc();
    $objComment = new stdClass();
    $objComment->id = $nID;
    $objComment->uuid = $row["uuid"]; // of the commenter
    $objComment->dentry = $row["dentry"];
    $objComment->snowflake = $row["snowflake"];
    $objComment->created = $row["created"];
    $objComment->prev = $row["prev"];
    $objComment->parent = $row["parent"];
    $objComment->tags = fetchTagNames(explode(" ", $row["tags"]));
    return json_encode($objComment);
}

// c
function pullBurstComments ($nPageNumber) {
    $sSQL = "SELECT COUNT(*) FROM Comments";
    $tResult = QueryDB($sSQL);
    $nComments = $tResult->fetch_assoc()["COUNT(*)"];

    // $sSQL = "SELECT * FROM Comments WHERE id BETWEEN " . ($nComments - 15) . " AND " . $nComments;
    $sSQL = "SELECT * FROM Comments WHERE prev=0 AND id BETWEEN 0 AND " . $nComments;
    $tResult = QueryDB($sSQL);

    $aComments = [];
    for ($i=0; $i<$tResult->num_rows; $i++) {
        $row = $tResult->fetch_assoc();
        $aComments[$i] = new stdClass();
        $aComments[$i]->id = $row["id"];
        $aComments[$i]->uuid = $row["uuid"];
        $aComments[$i]->votes = $row["votes"];
        $aComments[$i]->tags = fetchTagNames(explode(" ", $row["tags"]));
        $aComments[$i]->snowflake = $row["snowflake"];
        $aComments[$i]->created = $row["created"];
        $aComments[$i]->prev = $row["prev"];
        $aComments[$i]->parent = $row["parent"];

    }
    return json_encode ($aComments);
}

// c
function storeChat ($jsonChat) {
    $objChat = json_decode ($jsonChat);

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmtI = $dbconnect->prepare("INSERT INTO Chats (dentry, t, f) VALUES (?, ?, ?)");
    $stmtI->bind_param("sii", $objChat->chat, $objChat->to, $objChat->from);
    $bStatus = $stmtI->execute();
    $stmtI->close();

    return $bStatus;
}

// c
function createTag ($sTag) {
    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmtI = $dbconnect->prepare("INSERT INTO Tags (dentry) VALUES (?)");
    $stmtI->bind_param("s", $sTag);
    $bStatus = $stmtI->execute();
    $stmtI->close();

    return $bStatus;
}

// c
function castAVote ($jsonVote) {
    $objVote = json_decode ($jsonVote);
    $sSQL = "UPDATE Comments SET votes = votes + " . $objVote->nvote . " WHERE id=" . $objVote->id;
    QueryDB ($sSQL);
    $sSQL = "SELECT uuid FROM Comments WHERE id=" . $objVote->id;
    $tResult = QueryDB ($sSQL);
    $row = $tResult->fetch_assoc();
    if (1 == $objVote->nvote)
        updateKarma ($row["uuid"], 5);
    else
        updateKarma ($row["uuid"], -2);

    return true;
}

// c
function updateKarma ($nID, $nKarma) {
    $sSQL = "UPDATE Users SET karma = karma + " . $nKarma . " WHERE id=" . $nID;
    QueryDB ($sSQL);
}

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

// c
function postComment ($jsonComment) {
    $objCommentData = json_decode($jsonComment);


    if (0 == $objCommentData->prev) {
        $aTags = explode(" ", $objCommentData->tags);
        for ($i=0; $i<count($aTags); $i++) {
            checkUniqueTag ($aTags[$i]);
        }
        $aTagIDs = fetchTagIDs ($aTags);
        $sTags = joinArray ($aTagIDs);
    }

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $sSnowflake = '';
    if (0 == $objCommentData->prev)
        $sSnowflake = generateSnowflake ();

    $stmtI = $dbconnect->prepare("INSERT INTO Comments (uuid, dentry, prev, tags, snowflake, parent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtI->bind_param("isiss", $objCommentData->uuid, $objCommentData->dentry, $objCommentData->prev, $sTags, $sSnowflake, $objCommentData->parent);
    $bSent = $stmtI->execute();
    $stmtI->close();

    $dbconnect->close();

    updateKarma ($objCommentData->uuid, 1);

    return $bSent;
}

// c
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

    if (0 == $tResult->num_rows) {
        $stmt = $dbconnect->prepare("INSERT INTO Tags (dentry) VALUES (?)");
        $stmt->bind_param("s", $sTag);
        $stmt->execute();
        $tResult = $stmt->get_result();
        $stmt->close();
    }

    $dbconnect->close();

    return true;
}

// c
function joinArray ($aTagIDs) {
    $sTagify = "";
    for ($i=0; $i<count($aTagIDs) - 1; $i++) {
        $sTagify .= $aTagIDs[$i];
        $sTagify .= " ";
    }
    $sTagify .= $aTagIDs[count($aTagIDs) - 1];
    return $sTagify;
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

// c
function searchByTag ($sTag) {
    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmt = $dbconnect->prepare("SELECT id FROM Tags WHERE lower(dentry)=?");
    $stmt->bind_param("s", strtolower($sTag));
    $stmt->execute();
    $nTagID = $stmt->get_result()->fetch_assoc()["id"];
    $stmt->close();
    $dbconnect->close();

    $sSQL = "SELECT * FROM Comments WHERE tags LIKE '%" . $nTagID . "%'";
    $tResult = QueryDB ($sSQL);

    $aComments = [];
    for ($i=0; $i<$tResult->num_rows; $i++) {
        $row = $tResult->fetch_assoc();
        $aComments[$i] = new stdClass();
        $aComments[$i]->id = $row["id"];
        $aComments[$i]->uuid = $row["uuid"];
        $aComments[$i]->votes = $row["votes"];
        $aComments[$i]->prev = $row["prev"];
        $aComments[$i]->parent = $row["parent"];
        $aComments[$i]->tags = fetchTagNames(explode(" ", $row["tags"]));
        $aComments[$i]->snowflake = $row["snowflake"];
        $aComments[$i]->created = $row["created"];
    }
    return json_encode ($aComments);
}

// c
function search ($sSearch) {
    $aSearchKeys = multiexplode(array(",", ".", " "), $sSearch);
    for ($i=count($aSearchKeys) - 1; $i >= 0; $i--) {
        if (strlen($aSearchKeys[$i]) < 2)
            array_splice($aSearchKeys, $i, 1);
    }

    $dbhost = 'localhost';
    $dbuser = 'rootpisser';
    $dbpass = 'k9BLCB29tN';
    $db = "reststop";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $aResults = [];

    for ($i=0; $i<count($aSearchKeys); $i++) {
        $sCurrSearchKey = "%" . $aSearchKeys[$i] . "%";
        $stmtI = $dbconnect->prepare("SELECT id FROM Comments WHERE dentry LIKE ?");
        $stmtI->bind_param("s", $sCurrSearchKey);
        $stmtI->execute();
        $tResult = $stmtI->get_result();
        for ($x=0; $x<$tResult->num_rows; $x++) {
            $nID = $tResult->fetch_assoc()["id"];
            $bInList = false;
            for ($y=0; $y<count($aResults); $y++) {
                if ($nID == $aResults[$y]->id) {
                    $aResults[$y]->score++;
                    $bInList = true;
                    break;
                }
            }
            if (!$bInList) {
                $objCurrInsert = new stdClass();
                $objCurrInsert->id = $nID;
                $objCurrInsert->score = 1;
                array_push($aResults, $objCurrInsert);
            }
        }
        $stmtI->close();
    }

    for ($i=0; $i<count($aSearchKeys); $i++) {
        $stmt = $dbconnect->prepare("SELECT id FROM Tags WHERE lower(dentry)=?");
        $stmt->bind_param("s", strtolower($aSearchKeys[$i]));
        $stmt->execute();
        $tTagID = $stmt->get_result();
        if ($tTagID->num_rows < 1)
            continue;
        $nTagID = $tTagID->fetch_assoc()["id"];
        $stmt->close();
        $sSQL = "SELECT id FROM Comments WHERE tags LIKE '%" . $nTagID . "%'";
        $tResult = QueryDB ($sSQL);
        for ($x=0; $x<$tResult->num_rows; $x++) {
            $nID = $tResult->fetch_assoc()["id"];
            $bInList = false;
            for ($y=0; $y<count($aResults); $y++) {
                if ($nID == $aResults[$y]->id) {
                    $aResults[$y]->score += 3;
                    $bInList = true;
                    break;
                }
            }
            if (!$bInList) {
                $objCurrInsert = new stdClass();
                $objCurrInsert->id = $nID;
                $objCurrInsert->score = 3;
                array_push($aResults, $objCurrInsert);
            }
        }
    }

    $dbconnect->close();

    usort($aResults, function($a, $b) {
        return $a->score < $b->score;
    });

    if (count($aResults) > 20)
        array_splice($aResults, 20, count($aResults));

    $aResultData = [];
    for ($i=0; $i<count($aResults); $i++) {
        $sSQL = "SELECT * FROM Comments WHERE id=" . $aResults[$i]->id;
        $tResult = QueryDB ($sSQL);
        $row = $tResult->fetch_assoc();
        $objComment = new stdClass();
        $objComment->id = $nID;
        $objComment->uuid = $row["uuid"]; // of the commenter
        $objComment->dentry = $row["dentry"];
        $objComment->snowflake = $row["snowflake"];
        $objComment->prev = $row["prev"];
        $objComment->parent = $row["parent"];
        $objComment->created = $row["created"];
        $objComment->tags = fetchTagNames(explode(" ", $row["tags"]));
        $aResultData[$i] = $objComment;
    }

    return json_encode($aResultData);
}

// c
function multiexplode ($delimiters, $string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
}

// c
function getCommentChain ($nParentID) {
    $sSQL = "SELECT * FROM Comments WHERE parent=" . $nParentID;
    $tResult = QueryDB ($sSQL);

    for ($i=0; $i<$tResult->num_rows; $i++) {
        $row = $tResult->fetch_assoc();
        $aComments[$i] = new stdClass();
        $aComments[$i]->id = $row["id"];
        $aComments[$i]->uuid = $row["uuid"];
        $aComments[$i]->votes = $row["votes"];
        $aComments[$i]->tags = $row["tags"];
        $aComments[$i]->prev = $row["prev"];
        $aComments[$i]->parent = $row["parent"];
        $aComments[$i]->snowflake = $row["snowflake"];
        $aComments[$i]->created = $row["created"];
    }

    $sSQL = "SELECT * FROM Comments WHERE id=" . $nParentID;
    $tResult = QueryDB ($sSQL);
    $row = $tResult->fetch_assoc();


    $objParent = new stdClass();
    $objParent->id = $row["id"];
    $objParent->uuid = $row["uuid"];
    $objParent->votes = $row["votes"];
    $objParent->tags = fetchTagNames(explode(" ", $row["tags"]));
    $objParent->prev = $row["prev"];
    $objParent->parent = $row["parent"];
    $objParent->snowflake = $row["snowflake"];
    $objParent->created = $row["created"];
    array_push($aComments, $objParent);

    return json_encode ($aComments);
}

?>
