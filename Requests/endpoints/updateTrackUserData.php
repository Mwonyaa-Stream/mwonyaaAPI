<?php
//set headers to NOT cache a page
//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once 'includedFiles.php';


if (!empty($db)) {
    echo "love";

    $handler = new Handler($db);
    $data = json_decode(file_get_contents("php://input"));
//
//    if(!empty($data->user_id) && !empty($data->liteRecentTrackList) && !empty($data->liteLikedTrackList)){
//
//        $current_Time_InSeconds = time();
//        $update_date = date('Y-m-d H:i:s', $current_Time_InSeconds );
//
//        $result = $handler->updateTrackUserData($data->user_id, $data->liteRecentTrackList, $data->liteLikedTrackList,$update_date);
//        if($result){
//            http_response_code(200);
//            echo json_encode($result);
//        }else{
//            http_response_code(404);
//            echo json_encode(
//                array("error" => true),
//                array("message" => "Update failed."),
//                array("trackIds" => []),
//            );
//        }
//    }else{
//        http_response_code(400);
//        $response['error'] = true;
//        $response['message'] = 'Update failed. Data is incomplete.';
//        $response['trackIds'] = [];
//
//        echo json_encode($response);
//    }
}

?>