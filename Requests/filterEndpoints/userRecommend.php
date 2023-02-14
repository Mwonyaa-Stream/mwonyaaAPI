<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../endpoints/includedFiles.php';
if (!empty($db)) {
    $handler = new Filter($db);
    $recommendations = $handler->getRecommendations("mw603382d49906aPka");

    // Output the recommended songs
    foreach ($recommendations as $recommendedSong) {
        $query = "SELECT title, artist, album, genre, duration, cover, path, lyrics, tag, dateAdded FROM songs WHERE id = $recommendedSong";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_assoc($result);
        echo "Title: " . $row['title'] . "<br>";
        echo "Artist: " . $row['artist'] . "<br>";
        echo "Album: " . $row['album'] . "<br>";
        echo "Genre: " . $row['genre'] . "<br>";
        echo "Duration: " . $row['duration'] . "<br>";
        echo "Cover: " . $row['cover'] . "<br>";
        echo "Path: " . $row['path'] . "<br>";
        echo "Lyrics: " . $row['lyrics'] . "<br>";
        echo "Tag: " . $row['tag'] . "<br>";
        echo "Date Added: " . $row['dateAdded'] . "<br><br>";
    }

    if($result){
        http_response_code(200);
        echo json_encode($result);
    }else{
        http_response_code(404);
        echo json_encode(
            array("message" => "No item found.")
        );
    }

}


