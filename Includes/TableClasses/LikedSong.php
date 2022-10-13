<?php

class LikedSong
{

    private $con;
    private $id;
    private $songId;
    private $userID;
    private $dateAdded;
    private $dateUpdated;



    public function __construct($con, $userID)
    {
        $this->con = $con;
        $this->userID = $userID;

    }




    public function getNumberOfSongs()
    {
        $query = mysqli_query($this->con, "SELECT DISTINCT songId  FROM likedsongs WHERE userID='$this->userID'");
        return floatval(mysqli_num_rows($query));
    }


    public function getLikedSongIds($offset,$no_of_records_per_page){
        $query = mysqli_query($this->con, "SELECT DISTINCT songId FROM likedsongs WHERE userID='$this->userID' ORDER BY dateUpdated DESC, id DESC LIMIT " . $offset . "," . $no_of_records_per_page . "");
        $array = array();

        while($row = mysqli_fetch_array($query)){
            array_push($array, $row['songId']);
        }

        return $array;
    }







}
