<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../endpoints/includedFiles.php';


if (!empty($db)) {
    $recommendation = new FilterGateway($db);
    $result = $recommendation->Recommendation();

    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(
            array("message" => "No item found.")
        );
    }

}


