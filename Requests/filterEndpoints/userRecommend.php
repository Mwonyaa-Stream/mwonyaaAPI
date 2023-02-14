<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../endpoints/includedFiles.php';
if (!empty($db)) {
    $handler = new Filter($db);
    $recommendations = $handler->getRecommendations("mw603382d49906aPka");
    $filter_result = array();
    // Output the recommended songs
    foreach ($recommendations as $recommendedSong) {
        $query = "SELECT title, artist, album, genre, duration, cover, path, lyrics, tag, dateAdded FROM songs WHERE id = $recommendedSong";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_array($result);

        $temp = array();
        $temp['Title'] = $row['title'];
        $temp['Artist'] = $row['artist'];
        $temp['Album'] = $row['album'];
        $temp['Genre'] = $row['genre'];
        $temp['Duration'] = $row['duration'];
        $temp['Cover'] = $row['cover'];
        $temp['Path'] = $row['path'];
        $temp['Tag'] = $row['tag'];
        $temp['Date Added'] = $row['dateAdded'];
        array_push($filter_result, $temp);

    }


    if($filter_result){
        http_response_code(200);
        echo json_encode($filter_result);
    }else{
        http_response_code(404);
        echo json_encode(
            array("message" => "No songs found")
        );
    }
}


