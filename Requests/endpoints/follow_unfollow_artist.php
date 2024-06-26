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

if (isset($data->userId) && isset($data->artistId) && isset($data->action)) {
    $userId = $data->userId;
    $artistId = $data->artistId;
    $action = $data->action;

// Validate the user ID and artist ID
    if (!preg_match('/^m[a-zA-Z0-9]+$/', $userId) || !preg_match('/^m[a-zA-Z0-9]+$/', $artistId)) {
        echo json_encode(["result" => "error", "message" => "Invalid user ID or artist ID format"]);
        exit;
    }

    if ($action == 'follow') {
        // Check if record already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM artistfollowing WHERE artistid = ? AND userid = ?");
        $stmt->bind_param("ss", $artistId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $count = $result->fetch_row()[0];
        if ($count > 0) {
            echo json_encode(["result" => "error", "message" => "Artist already followed by user"]);
            exit;
        } else {
            // Insert a new row into the artistfollowing table
            $stmt = $conn->prepare("INSERT INTO artistfollowing (artistid, userid, datefollowed) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $artistId, $userId);
            $stmt->execute();
            $message = "Now following Artist";
        }
    } elseif ($action == 'unfollow') {
            // Delete the corresponding row from the artistfollowing table
        $stmt = $conn->prepare("DELETE FROM artistfollowing WHERE artistid = ? AND userid = ?");
        $stmt->bind_param("ss", $artistId, $userId);
        $stmt->execute();
        $message = "Artist Unfollowed";

    } else {
        echo json_encode(["result" => "error", "message" => "Invalid Action. Only 'follow' or 'unfollow' actions are allowed"]);
        exit;
    }
} else {
    echo json_encode(["result" => "error", "message" => "Unset user ID or artist ID or Action"]);
    exit;
}

// Return success message
echo json_encode(["result" => "success", "message" => $message]);



?>