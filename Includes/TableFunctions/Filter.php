<?php

class Filter
{

    private $ImageBasepath = "https://mwonyaa.com/";
    private $conn;
    private $version;


    // track update info

    public function __construct($con)
    {
        $this->conn = $con;
        $this->version = 5; // VersionCode
    }

    // Function to calculate cosine similarity between two users
    function cosineSimilarity($user1, $user2): float
    {
        // Get the song IDs listened to by each user
        $query1 = "SELECT songid FROM frequency WHERE userid = '$user1'";
        $query2 = "SELECT songid FROM frequency WHERE userid = '$user2'";
        $result1 = mysqli_query($this->conn, $query1);
        $result2 = mysqli_query($this->conn, $query2);
        $songs1 = array();
        $songs2 = array();
        while ($row = mysqli_fetch_assoc($result1)) {
            array_push($songs1, $row['songid']);
        }
        while ($row = mysqli_fetch_assoc($result2)) {
            array_push($songs2, $row['songid']);
        }

        // Calculate the cosine similarity
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        foreach ($songs1 as $song) {
            if (in_array($song, $songs2)) {
                $dotProduct++;
            }
            $magnitude1++;
        }
        foreach ($songs2 as $song) {
            $magnitude2++;
        }
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        $similarity = $dotProduct / ($magnitude1 * $magnitude2);

        return $similarity;
    }

// Function to get the most similar users
    function getSimilarUsers($user): array
    {
        // Get all user IDs
        $query = "SELECT DISTINCT userid FROM frequency";
        $result = mysqli_query($this->conn, $query);
        $allUsers = array();
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($allUsers, $row['userid']);
        }

        // Calculate similarity between the given user and all other users
        $similarities = array();
        foreach ($allUsers as $otherUser) {
            if ($otherUser != $user) {
                $similarities[$otherUser] = $this->cosineSimilarity($user, $otherUser);
            }
        }

        // Sort the similarities in descending order
        arsort($similarities);

        return $similarities;
    }

    function getTopSongs($user): array
    {
        // Get the top songs listened to by the user
        $query = "SELECT songid, COUNT(*) as count FROM frequency WHERE userid = '$user' GROUP BY songid ORDER BY count DESC";
        $result = mysqli_query($this->conn, $query);
        $songs = array();
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($songs, $row['songid']);
        }

        return $songs;
    }

// Function to get the recommended songs for a user
    function getRecommendations($user): array
    {
        // Get the most similar users
        $similarUsers = $this->getSimilarUsers($user);

        // Get the songs listened to by the most similar users
        $allSongs = array();
        $maxSongs = 0;
        foreach ($similarUsers as $similarUser => $similarity) {
            $songs = $this->getTopSongs($similarUser);
            if (count($songs) > $maxSongs) {
                $maxSongs = count($songs);
            }
            foreach ($songs as $song) {
                if (!isset($allSongs[$song])) {
                    $allSongs[$song] = 0;
                }
                $allSongs[$song]++;
            }
        }

        // Get the top songs listened to by the most similar users
        $recommendations = array();
        $minListenCount = ceil(0.2 * $maxSongs); // Recommend songs that have been listened to 80% or more of the maximum listen count
        foreach ($allSongs as $song => $count) {
            if ($count >= $minListenCount) {
                array_push($recommendations, $song);
            }
        }

        return $recommendations;
    }


}
