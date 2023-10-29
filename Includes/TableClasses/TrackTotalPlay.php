<?php

class TrackTotalPlay
{

    private $con;
    private $id;
    private $songid;
    private $total_pays;
    private $last_updated;


    public function __construct($con, $song_id)
    {
        $this->con = $con;

        $track_query = mysqli_query($this->con, "SELECT `id`, `songid`, `total_plays`, `last_updated` FROM `track_plays` WHERE songid ='$song_id'");
        $track_fetched = mysqli_fetch_array($track_query);

        if (mysqli_num_rows($track_fetched) == 0) {
            $this->id = null;
            $this->songid = null;
            $this->total_pays = null;
            $this->last_updated = null;

        } else {

            $this->id = $track_fetched['id'];
            $this->songid = $track_fetched['songid'];
            $this->total_pays = $track_fetched['total_plays'];
            $this->last_updated = $track_fetched['last_updated'];
        }
    }


    public function getId()
    {
        return $this->id;
    }


    public function getSongid()
    {
        return $this->songid;
    }


    public function getTotalPays(): int
    {

        return $this->total_pays ?? 0;
    }


    public function getLastUpdated()
    {
        return $this->last_updated;
    }


}
