<?php

class Caption
{

    private $con;
    private $id;
    private $track_id;
    private $captions;
    private $dateCreated;
    private $dateUpdated;
    private $available;



    public function __construct($con, $track_id)
    {
        $this->con = $con;
        $this->track_id = $track_id;

        $check_caption = mysqli_query($this->con, "SELECT id, track_id, captions, dateCreated, dateUpdated,available FROM captions WHERE track_id='$this->track_id' and available = 2");
        $caption_fetched = mysqli_fetch_array($check_caption);

        if (mysqli_num_rows($check_caption) == 0) {
            $this->id =  null;
            $this->track_id =  null;
            $this->captions =  null;
            $this->dateCreated = null;
            $this->dateUpdated =  null;
            $this->available = null;

        } else {
            $this->id = $caption_fetched['id'];
            $this->track_id = $caption_fetched['track_id'];
            $this->captions = $caption_fetched['captions'];
            $this->dateCreated = $caption_fetched['dateCreated'];
            $this->dateUpdated = $caption_fetched['dateUpdated'];
            $this->available = $caption_fetched['available'];
        }
    }


    public function getId()
    {
        return $this->id;
    }


    public function getTrackId()
    {
        return $this->track_id;
    }

    public function getCaptions()
    {
        $lyricsArray = array();
        // Normalize line endings to \n
        $lyrics = str_replace("\r\n", "\n", $this->captions);

        // Extract valid timestamped lines
        $lines = explode("\n", $lyrics);
        foreach ($lines as $line) {
            if (preg_match('/^\[\d+:\d+\.\d+\]/', $line)) {
                $lyricsArray[] = $line;
            }
        }

        // Check if there are valid timestamped lines
        if (empty($lyricsArray)) {
            return null;
        } else {
            return implode("\n", $lyricsArray);
        }
    }



    public function getDateCreated()
    {
        return $this->dateCreated;
    }


    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }


    public function getAvailable()
    {
        return $this->available;
    }

 
}
