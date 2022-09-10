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
        } else{
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
        $query = mysqli_query($this->con, "SELECT DISTINCT songId FROM playlistsongs WHERE playlistId='mwPL61c3429798c03mw61c' ORDER BY playlistOrder ASC");
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            array_push($array, $row['songId']);
        }

        return $array;
    }


}