<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// Connect to the database
try {
//    $db = new PDO("mysql:host=localhost;dbname=mwonya", "root", "");
    $db = new PDO("mysql:host=178.79.148.46;dbname=mwonya", "mobile", "PBWuyqRPtjfq13GB");
} catch (PDOException $e) {
    // Return error message if connection fails
    echo json_encode(["status" => "error", "message" => "Error connecting to the database: " . $e->getMessage()]);
    exit;
}

// Get the user ID and artist ID from the request


if (isTheseParametersAvailable(array('userId', 'artistId', 'action'))){
    $userId = $_POST['userId'];
    $artistId = $_POST['artistId'];
    $action = $_POST['action'];

// Validate the user ID and artist ID
    if (!preg_match('/^m[a-zA-Z0-9]+$/', $userId) || !preg_match('/^m[a-zA-Z0-9]+$/', $artistId)) {
        echo json_encode(["status" => "error", "message" => "Invalid user ID or artist ID format"]);
        exit;
    }

    if($action == 'follow') {
        // Check if record already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM artistfollowing WHERE artistid = ? AND userid = ?");
        $stmt->execute([$artistId, $userId]);
        $count = $stmt->fetchColumn();
        if($count > 0) {
            echo json_encode(["status" => "error", "message" => "Artist already followed by user"]);
            exit;
        } else {
            // Insert a new row into the artistfollowing table
            try {
                $stmt = $db->prepare("INSERT INTO artistfollowing (artistid, userid, datefollowed) VALUES (?, ?, NOW())");
                $stmt->execute([$artistId, $userId]);
            } catch (PDOException $e) {
                // Return error message if insert fails
                echo json_encode(["status" => "error", "message" => "Error inserting data into the database: " . $e->getMessage()]);
                exit;
            }
        }
    } elseif($action == 'unfollow') {
        // Delete the corresponding row from the artistfollowing table
        try {
            $stmt = $db->prepare("DELETE FROM artistfollowing WHERE artistid = ? AND userid = ?");
            $stmt->execute([$artistId, $userId]);
        } catch (PDOException $e) {
            // Return error message if delete fails
            echo json_encode(["status" => "error", "message" => "Error deleting data from the database: " . $e->getMessage()]);
            exit;
        }
    }else{
        echo json_encode(["status" => "error", "message" => "Invalid Action. Only 'follow' or 'unfollow' actions are allowed"]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "Unset user ID or artist ID or Action"]);
    exit;
}


// Return success message
echo json_encode(["status" => "success"]);

function isTheseParametersAvailable($params)
{

    //traversing through all the parameters
    foreach ($params as $param) {
        //if the paramter is not available
        if (!isset($_POST[$param])) {
            //return false
            return false;
        }
    }
    //return true if every param is available
    return true;
}

?>
