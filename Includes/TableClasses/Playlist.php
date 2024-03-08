<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class Playlist
{

    private $con;
    private $id;
    private $name;
    private $owner;
    private $ownerID;
    private $dateCreated;
    private $description;
    private $coverurl;
    private $status;
    private $featuredplaylist;


    public function __construct($con, $id)
    {


        //data is a string
        $query = mysqli_query($con, "SELECT `no`, `id`, `name`, `owner`, `ownerID`, `dateCreated`, `description`, `coverurl`, `status`, `featuredplaylist` FROM playlists WHERE id='$id'");
        $data = mysqli_fetch_array($query);

        if ($data) {
            $this->con = $con;
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->owner = $data['owner'];
            $this->ownerID = $data['ownerID'];
            $this->dateCreated = $data['dateCreated'];
            $this->description = $data['description'];
            $this->coverurl = $data['coverurl'];
            $this->status = $data['status'];
            $this->featuredplaylist = $data['featuredplaylist'];
        } else {
            http_response_code(200);
            echo json_encode(
                array("message" => "No Item Found")
            );
            exit;
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return mixed
     */
    public function getOwnerID()
    {
        return $this->ownerID;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getCoverurl()
    {
        return $this->coverurl;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getFeaturedplaylist()
    {
        return $this->featuredplaylist;
    }


    public function getNumberOfSongs()
    {
        $query = mysqli_query($this->con, "SELECT DISTINCT songId FROM playlistsongs WHERE playlistId='$this->id'");
        return mysqli_num_rows($query);
    }

    public function getSongIds()
    {
        // Initialize an empty array to hold the song IDs
        $songIds = [];

        // Set the default query to select song IDs based on the playlist ID
        $query = "SELECT DISTINCT songId as id FROM playlistsongs WHERE playlistId=? ORDER BY `playlistsongs`.`dateAdded` DESC";

        // Check if the playlist ID is one of the predefined queries
        switch ($this->id) {
            case "mwPL_query_base_rap_genre":
                $query = "SELECT f.songid as id, s.title, s.genre, g.name, ( SELECT COUNT(DISTINCT f2.userid) FROM frequency f2 WHERE f2.songid = f.songid AND f2.lastPlayed BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() ) as user_count FROM frequency f JOIN songs s ON f.songid = s.id JOIN genres g on s.genre = g.id WHERE f.lastPlayed BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() AND s.tag = 'music' AND s.genre = 1 GROUP BY f.songid ORDER BY user_count DESC, f.lastPlayed DESC LIMIT 40";
                break;
            case "mwPL_query_base_trending":
                $query = "SELECT f.songid as id, s.title, s.genre, g.name, ( SELECT COUNT(DISTINCT f2.userid) FROM frequency f2 WHERE f2.songid = f.songid AND f2.lastPlayed BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() ) as user_count FROM frequency f JOIN songs s ON f.songid = s.id JOIN genres g on s.genre = g.id WHERE f.lastPlayed BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() AND s.tag = 'music' AND s.genre != 3 GROUP BY f.songid ORDER BY user_count DESC, f.lastPlayed DESC LIMIT 40";
                break;
            case "mwPL_query_base_2022_review":
                $query = "SELECT f.songid as id, s.title, s.genre, s.tag, g.name, SUM(f.playsmonth) as total_plays FROM frequency f JOIN songs s ON f.songid = s.id JOIN genres g on s.genre = g.id WHERE f.lastPlayed BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() AND s.tag = 'music' AND s.genre != 3 GROUP BY f.songid ORDER BY total_plays DESC, f.lastPlayed DESC LIMIT 20";
                break;
            default:
        }

        // Prepare the statement
        $stmt = mysqli_prepare($this->con, $query);

        // Bind the parameter to the prepared statement
        mysqli_stmt_bind_param($stmt, "s", $this->id);

        // Use exception handling to handle any errors that may occur during execution
        try {
            // Execute the prepared statement
            mysqli_stmt_execute($stmt);

            // Get the result set
            $result = mysqli_stmt_get_result($stmt);

            // Retrieve all rows from the result set as an associative array
            $songIds = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            // Handle the error
        }

        // Return the array of song IDs
        return array_column($songIds, 'id');
    }


}