<?php

class ArtistPick
{

    private $con;
    private $id;
    private $title;
    private $artistID;
    private $CoverArt;
    private $songID;
    private $date_created;
    private $mysqliData;


    public function __construct($con, $id_artistID)
    {
        $this->con = $con;
        $this->artistID = $id_artistID;

        //select using artist ID

        $query = mysqli_query($this->con, "SELECT `id`, `tile`, `artistID`, `CoverArt`, `songID`, `date_created` FROM `artistpick` WHERE  artistID='$this->artistID'");


        if (mysqli_num_rows($query) == 0) {
            $this->id = null;
            $this->title = null;
            $this->artistID = null;
            $this->CoverArt = null;
            $this->songID = null;
            $this->date_created = null;
            return false;
        } else {
            $this->mysqliData = mysqli_fetch_array($query);
            $this->id = $this->mysqliData['id'];
            $this->title = $this->mysqliData['tile'];
            $this->artistID = $this->mysqliData['artistID'];
            $this->CoverArt = $this->mysqliData['CoverArt'];
            $this->songID = $this->mysqliData['songID'];
            $this->date_created = $this->mysqliData['date_created'];
            return true;
        }


    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed|null
     */
    public function getCoverArt()
    {
        return $this->CoverArt;
    }

    /**
     * @return mixed|null
     */
    public function getSongID()
    {
        return $this->songID;
    }

    public function getSong()
    {
        return new Song($this->con, $this->songID);
    }

    /**
     * @return mixed|null
     */
    public function getArtistID()
    {
        return $this->artistID;
    }

    public function getArtist()
    {
        return new Artist($this->con, $this->artistID);
    }


    /**
     * @return mixed|null
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }


}

?>