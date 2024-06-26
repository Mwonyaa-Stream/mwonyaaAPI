<?php

class ArtistEvents
{

    private $con;
    private $id;
    private $name;
    private $artistID;
    private $title;
    private $venu;
    private $description;
    private $date;
    private $status;
    private $date_created;
    private $mysqliData;

    public function __construct($con, $artistID_id)
    {
        $this->con = $con;
        $this->artistID = $artistID_id;

        //select using artistID

        $query = mysqli_query($this->con, "SELECT `id`, `name`, `artistID`, `title`, `venu`, `description`, `date`, `status`, `date_created` FROM `artist_events` WHERE  artistID='$this->artistID' ORDER BY `artist_events`.`date` ASC");


        if (mysqli_num_rows($query) == 0) {
            $this->id = null;
            $this->mysqliData = null;
            $this->id =null;
            $this->name = null;
            $this->artistID = null;
            $this->title = null;
            $this->venu = null;
            $this->description = null;
            $this->date = null;
            $this->status = null;
            $this->date_created = null;
            return false;
        } else {
            $this->mysqliData = mysqli_fetch_array($query);
            $this->id = $this->mysqliData['id'];
            $this->name = $this->mysqliData['name'];
            $this->artistID = $this->mysqliData['artistID'];
            $this->title = $this->mysqliData['title'];
            $this->venu = $this->mysqliData['venu'];
            $this->description = $this->mysqliData['description'];
            $this->date = $this->mysqliData['date'];
            $this->status = $this->mysqliData['status'];
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed|null
     */
    public function getArtistID()
    {
        return $this->artistID;
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
    public function getVenu()
    {
        return $this->venu;
    }

    /**
     * @return mixed|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed|null
     */
    public function getDate()
    {
        $phpdate = strtotime($this->date);
        $mysqldate = date('d M Y', $phpdate);

        return $mysqldate;
    }

    public function getTime()
    {
        $phpdate = strtotime($this->date);
        $mysql_time = date("D, h:i A", $phpdate);

        return $mysql_time;
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
    public function getDateCreated()
    {
        $phpdate = strtotime($this->date_created);
        $mysqldate = date('d M Y', $phpdate);

        return $mysqldate;
    }






}

?>