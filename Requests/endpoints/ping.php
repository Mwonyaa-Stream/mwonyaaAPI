<?php
header('Content-Type: application/json');
header("Content-Type: application/json; charset=UTF-8");
$response = array();
$response['status'] = 'success';
$response['message'] = 'Connected to server';

echo json_encode($response);
exit;
?>