<?php

class Events
{

    private $con;
    private $id;
    private $title;
    private $description;
    private $startDate;
    private $startTime;
    private $endDate;
    private $endtime;
    private $location;
    private $host_name;
    private $host_contact;
    private $image;
    private $ranking;
    private $featured;
    private $date_created;


    private $mysqliData;

    public function __construct($con, $id)
    {
        $this->con = $con;
        $this->id = $id;

        //select using Event ID
        $query = mysqli_query($this->con, "SELECT  `id`, `title`, `description`, `startDate`, `startTime`, `endDate`, `endtime`, `location`, `host_name`, `host_contact`, `image`, `ranking`, `featured`, `date_created` FROM `events` WHERE  id = '$this->id'");

        if (mysqli_num_rows($query) == 0) {
            $this->mysqliData = null;
            $this->id = null;
            $this->title = null;
            $this->description = null;
            $this->startDate = null;
            $this->startTime = null;
            $this->endDate = null;
            $this->endtime = null;
            $this->location = null;
            $this->host_name = null;
            $this->host_contact = null;
            $this->image = null;
            $this->ranking = null;
            $this->featured = null;
            $this->date_created = null;
            return false;
        } else {
            $this->mysqliData = mysqli_fetch_array($query);
            $this->id = $this->mysqliData['id'];;
            $this->title = $this->mysqliData['title'];;
            $this->description = $this->mysqliData['description'];;
            $this->startDate = $this->mysqliData['startDate'];;
            $this->startTime = $this->mysqliData['startTime'];;
            $this->endDate = $this->mysqliData['endDate'];;
            $this->endtime = $this->mysqliData['endtime'];;
            $this->location = $this->mysqliData['location'];;
            $this->host_name = $this->mysqliData['host_name'];;
            $this->host_contact = $this->mysqliData['host_contact'];;
            $this->image = $this->mysqliData['image'];;
            $this->ranking = $this->mysqliData['ranking'];;
            $this->featured = $this->mysqliData['featured'];;
            $this->date_created = $this->mysqliData['date_created'];;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return mixed|null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return mixed|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return mixed|null
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * @return mixed|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed|null
     */
    public function getHostName()
    {
        return $this->host_name;
    }

    /**
     * @return mixed|null
     */
    public function getHostContact()
    {
        return $this->host_contact;
    }

    /**
     * @return mixed|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return mixed|null
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * @return mixed|null
     */
    public function getFeatured()
    {
        return $this->featured;
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