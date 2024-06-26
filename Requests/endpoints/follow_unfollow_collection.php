<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once 'includedFiles.php';
$conn = $db;
$message = "success";
$data = json_decode(file_get_contents("php://input"));
if ($conn == null) {
    echo json_encode(["result" => "error", "message" => "Error connecting to the database"]);
    exit;
}

if (isset($data->userId) && isset($data->collectionID) && isset($data->action)) {
    $userID = $data->userId;
    $collectionID = $data->collectionID;
    $action = $data->action;

// Validate the user ID and collectionID
    if (!preg_match('/^m[a-zA-Z0-9]+$/', $userID) || !preg_match('/^m[a-zA-Z0-9]+$/', $collectionID)) {
        echo json_encode(["result" => "error", "message" => "Invalid user ID or collectionID ID format"]);
        exit;
    }

    if ($action == 'follow') {
        // Check if record already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM collectionfollowing WHERE collectionID = ? AND userID = ?");
        $stmt->bind_param("ss", $collectionID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $count = $result->fetch_row()[0];
        if ($count > 0) {
            echo json_encode(["result" => "error", "message" => "collection already followed by user"]);
            exit;
        } else {
            // Insert a new row into the artistfollowing table
            $stmt = $conn->prepare("INSERT INTO collectionfollowing (collectionID, userID, dateFollowed) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $collectionID, $userID);
            $stmt->execute();
            $message = "Now following Collection";
        }
    } elseif ($action == 'unfollow') {
            // Delete the corresponding row from the artistfollowing table
        $stmt = $conn->prepare("DELETE FROM collectionfollowing WHERE collectionID = ? AND userID = ?");
        $stmt->bind_param("ss", $collectionID, $userID);
        $stmt->execute();
        $message = "Artist Unfollowed";

    } else {
        echo json_encode(["result" => "error", "message" => "Invalid Action. Only 'follow' or 'unfollow' actions are allowed"]);
        exit;
    }
} else {
    echo json_encode(["result" => "error", "message" => "Unset user ID or collectionID ID or Action"]);
    exit;
}

// Return success message
echo json_encode(["result" => "success", "message" => $message]);



?>