<?php
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

    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->OrderTrackingId)) {
        $handler = new Handler($db,$redis_con);
        $current_Time_InSeconds = time();
        $update_date = date('Y-m-d H:i:s', $current_Time_InSeconds);
        $OrderTrackingId = $data->OrderTrackingId;
        $OrderNotificationType = $data->OrderNotificationType;
        $OrderMerchantReference = $data->OrderMerchantReference;
        $result = $handler->PesaPalPaymentIPNUpdate($OrderTrackingId,$OrderNotificationType,$OrderMerchantReference,$update_date);
        if ($result) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(
                array("message" => "No item found.", "status" => 404)
            );
        }
    } else {
        http_response_code(400);
        $response['error'] = true;
        $response['message'] = 'Update failed. Data is incomplete.';
        $response['status'] = 400;

        echo json_encode($response);
    }
}

?>