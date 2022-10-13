<?php
//set headers to NOT cache a page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'includedFiles.php';

if (!empty($db)) {
    $handler = new Handler($db);
    $data = json_decode(file_get_contents("php://input"));

    if(!empty($data->user_id) && !empty($data->liteRecentTrackList) && !empty($data->liteLikedTrackList)){

        $current_Time_InSeconds = time();
        $update_date = date('Y-m-d H:i:s', $current_Time_InSeconds );

        if($handler->updateTrackUserData($data->user_id, $data->liteRecentTrackList, $data->liteLikedTrackList,$update_date)){
            http_response_code(201);
            $response['error'] = false;
            $response['message'] = 'Update was successful.';
            echo json_encode($response);

        } else{
            http_response_code(503);
            $response['error'] = true;
            $response['message'] = 'Update failed.';
            echo json_encode($response);
        }
    }else{
        http_response_code(400);
        $response['error'] = true;
        $response['message'] = 'Update failed. Data is incomplete.';
        echo json_encode($response);
    }
}

?>