<?php

class FilterGateway
{

    private $conn;
    private $version;


    // track update info

    public function __construct($con)
    {
        $this->conn = $con;
        $this->version = 5; // VersionCode
    }


    function Recommendation(): array
    {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $userID = isset($_GET['userID']) ? $_GET['userID'] : null;

        $itemRecords = array();
        $recommendedSongs = array();

        // Query to fetch recommended songs for the given user ID
        $recommendation_table_Query = "SELECT `id`, `user_id`, `recommended_songs`, `created_at` FROM `recommendations` WHERE `user_id` =  '$userID'";
        $table_data = mysqli_query($this->conn, $recommendation_table_Query);

        while ($row = mysqli_fetch_array($table_data)) {
            $songs = explode(',', $row['recommended_songs']);
            $userID = $row['user_id'];
            $recommendedSongs = array_merge($recommendedSongs, $songs);
        }


        $user_model = new User($this->conn, $userID);
        $user_name = $user_model->getFirstname();
        $user_signDate = $user_model->getSignupDate();


        // Pagination
        $itemsPerPage = 10; // Number of items to display per page
        $totalItems = count($recommendedSongs); // Total number of recommended songs
        $totalPages = ceil($totalItems / $itemsPerPage); // Calculate total pages

        // Validate the "page" parameter
        if ($page < 1 || $page > $totalPages) {
            $page = 1;
        }

        // Shuffle the array for the first page
        if ($page === 1) {
            shuffle($recommendedSongs);
        }


        // Calculate the starting and ending indexes for the current page
        $startIndex = ($page - 1) * $itemsPerPage;
        $endIndex = min($startIndex + $itemsPerPage - 1, $totalItems - 1);

        // Get the recommended songs for the current page
        $songsForPage = array_slice($recommendedSongs, $startIndex, $endIndex - $startIndex + 1);

        //trackList
        $trackListArray = array();

        foreach ($songsForPage as $row) {
            $song = new Song($this->conn, $row);
            $temp = array();

            if($song->getTag() === "music") {
                $temp['id'] = $song->getId();
                $temp['title'] = $song->getTitle();
                $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                $temp['artistID'] = $song->getArtistId();
                $temp['album'] = $song->getAlbum()->getTitle();
                $temp['description'] = $song->getAlbum()->getDescription();
                $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                $temp['genre'] = $song->getGenre()->getGenre();
                $temp['genreID'] = $song->getGenre()->getGenreid();
                $temp['duration'] = $song->getDuration();
                $temp['cover'] = $song->getCover();
                $temp['path'] = $song->getPath();
                $temp['totalplays'] = $song->getPlays();
                $temp['albumID'] = $song->getAlbumId();
                $temp['tag'] = $song->getTag();

                array_push($trackListArray, $temp);
            }

        }

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["user_name"] = $user_name;
        $itemRecords["user_join_date"] = $user_signDate;
        $itemRecords["recommendations"] = $trackListArray;
        $itemRecords["total_pages"] = $totalPages;
        $itemRecords["total_results"] = $totalItems;

        return $itemRecords;
    }



}
