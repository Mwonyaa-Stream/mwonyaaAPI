<?php
//set headers to NOT cache a page
//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once 'includedFiles.php';


if (!empty($db)) {

    $data = json_decode(file_get_contents("php://input"));
//
    if(!empty($data->user_id) && !empty($data->liteRecentTrackList) && !empty($data->liteLikedTrackList)){
        $handler = new Handler($db);

        $current_Time_InSeconds = time();
        $update_date = date('Y-m-d H:i:s', $current_Time_InSeconds );
        $handler->user_id = $data->user_id;
        $handler->update_date = $update_date;
        $handler->liteRecentTrackList = $data->liteRecentTrackList;
        $handler->liteLikedTrackList = $data->liteLikedTrackList;
        $update_user_data = $handler->updateTrackUserData();
        if($update_user_data){
            echo "me me";
            http_response_code(200);
            echo json_encode($update_user_data);
        }else{
            http_response_code(404);
            echo json_encode(
                array("error" => true),
                array("message" => "Update failed."),
                array("trackIds" => []),
            );
        }
    }else{
        http_response_code(400);
        $response['error'] = true;
        $response['message'] = 'Update failed. Data is incomplete.';
        $response['trackIds'] = [];

        echo json_encode($response);
    }
}

?>