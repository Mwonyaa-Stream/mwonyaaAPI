<?php

class Artist
{

    private $con;
    private $id;
    private $no;
    private $name;
    private $email;
    private $phone;
    private $facebookurl;
    private $twitterurl;
    private $instagramurl;
    private $RecordLable;
    private $profilephoto;
    private $coverimage;
    private $bio;
    private $genre;
    private $tag;
    private $dateAdded;
    private $overalplays;
    private $status;

    public function __construct($con, $id)
    {
        $this->con = $con;
        $this->id = $id;

        $query = mysqli_query($this->con, "SELECT `no`, `id`, `name`, `email`, `phone`, `facebookurl`, `twitterurl`, `instagramurl`, `RecordLable`, `password`, `profilephoto`, `coverimage`, `bio`, `genre`, `datecreated`, `lastupdate`, `tag`, `overalplays`, `status` FROM artists WHERE id='$this->id'");
        $artistfetched = mysqli_fetch_array($query);


        if (mysqli_num_rows($query) < 1) {
            $this->no = null;
            $this->id = null;
            $this->name = null;
            $this->email = null;
            $this->phone = null;
            $this->facebookurl = null;
            $this->twitterurl = null;
            $this->instagramurl = null;
            $this->RecordLable = null;
            $this->profilephoto = null;
            $this->coverimage = null;
            $this->bio = null;
            $this->genre = null;
            $this->tag = null;
            $this->dateAdded = null;
            $this->overalplays = null;
            $this->status = null;
        } else {
            $this->no = $artistfetched['no'];
            $this->name = $artistfetched['id'];
            $this->name = $artistfetched['name'];
            $this->email = $artistfetched['email'];
            $this->phone = $artistfetched['phone'];
            $this->facebookurl = $artistfetched['facebookurl'];;
            $this->twitterurl = $artistfetched['twitterurl'];;
            $this->instagramurl = $artistfetched['instagramurl'];;
            $this->RecordLable = $artistfetched['RecordLable'];;
            $this->profilephoto = $artistfetched['profilephoto'];
            $this->coverimage = $artistfetched['coverimage'];
            $this->bio = $artistfetched['bio'];
            $this->genre = $artistfetched['genre'];
            $this->tag = $artistfetched['tag'];
            $this->dateAdded = $artistfetched['datecreated'];
            $this->overalplays = $artistfetched['overalplays'];
            $this->status = $artistfetched['status'];
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getdateadded()
    {
        $phpdate = strtotime($this->dateAdded);
        $mysqldate = date('d M Y', $phpdate);
        // $mysqldate = date( 'd/M/Y H:i:s', $phpdate );

        return $mysqldate;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed|null
     */
    public function getFacebookurl()
    {
        return $this->facebookurl;
    }

    /**
     * @return mixed|null
     */
    public function getTwitterurl()
    {
        return $this->twitterurl;
    }

    /**
     * @return mixed|null
     */
    public function getInstagramurl()
    {
        return $this->instagramurl;
    }

    /**
     * @return mixed|null
     */
    public function getRecordLable()
    {
        return $this->RecordLable;
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed|null
     */
    public function getOveralplays()
    {
        return $this->overalplays;
    }



    public function getProfilePath()
    {
        return $this->profilephoto;
    }

    public function getArtistCoverPath()
    {
        return $this->coverimage;
    }

    public function getArtistBio()
    {
        return $this->bio;
    }


    public function getGenre()
    {
        return $this->genre;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getGenrename()
    {
        return  new Genre($this->con, $this->genre);
    }

    public function getTotalSongs()
    {
        $query = mysqli_query($this->con, "SELECT COUNT(*) as totalsongs FROM songs WHERE artist ='$this->id'");
        $row = mysqli_fetch_array($query);
        return $row['totalsongs'];
    }

    public function getTotalablums()
    {
        $query = mysqli_query($this->con, "SELECT COUNT(*) as totalalbum FROM albums WHERE artist ='$this->id'");
        $row = mysqli_fetch_array($query);
        return $row['totalalbum'];
    }


    public function getTotalPlays()
    {
        $query = mysqli_query($this->con, "SELECT SUM(`plays`) AS totalplays FROM songs where `artist` = '$this->id' and tag != 'ad'");
        $row = mysqli_fetch_array($query);
        return $row['totalplays'];
    }

    public function getLatestRelease()
    {
        $query = mysqli_query($this->con, "SELECT id FROM albums where artist='$this->id' and tag != 'ad' ORDER BY datecreated DESC LIMIT 1");
        $row = mysqli_fetch_array($query);
        $id = $row['id'];
        return new Album($this->con, $id);
    }

    public function getSongIds()
    {
        $query = mysqli_query($this->con, "SELECT id FROM songs WHERE artist='$this->id' and tag != 'ad' ORDER BY plays DESC Limit 8");
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            array_push($array, $row['id']);
        }

        return $array;
    }

    public function getRelatedArtists()
    {
        $rel_array_query = mysqli_query($this->con, "SELECT id FROM artists WHERE genre='$this->genre' AND id != '$this->id'  ORDER BY overalplays DESC Limit 8");
        $rel_array = array();

        while ($rel_array_row = mysqli_fetch_array($rel_array_query)) {
            array_push($rel_array, $rel_array_row['id']);
        }

        return $rel_array;
    }

    public function getArtistAlbums()
    {
        $query = mysqli_query($this->con, "SELECT id FROM albums where artist='$this->id' and tag != 'ad' LIMIT 8");
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            array_push($array, $row['id']);
        }

        return $array;
    }


}
