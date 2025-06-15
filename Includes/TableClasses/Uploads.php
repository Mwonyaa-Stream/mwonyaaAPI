<?php

class Uploads
{

    private $con;
    private $uploadid;



    public function __construct($con, $uploadid)
    {
        $this->con = $con;
        $this->uploadid = $uploadid;
        $this->file_path;
        $this->file_name;

        $checkupload = mysqli_query($this->con, "SELECT file_path,file_name FROM Uploads WHERE upload_id ='$this->uploadid' limit 1");

        if (mysqli_num_rows($checkupload) == 0) {
            $this->file_path = null;
            $this->file_name = null;

        } else {
           $uploadfetched = mysqli_fetch_array($checkupload);

            $this->file_path = $uploadfetched['file_path'];
            $this->file_name = $uploadfetched['file_name'];
        }
    }

    /**
     * @return mixed
     */
    public function getUploadfile_path()
    {
        return $this->file_path;
    }
    

    //streaming url for tracks
    public function getTrackStreamingUrl()
    {
        //first strip the extension from file name
        if ($this->file_name) {
            $file_name_without_extension = pathinfo($this->file_name, PATHINFO_FILENAME);
            return "https://audio.mwonya.com/stream/" . $file_name_without_extension . "/playlist.m3u8"; // Adjust the URL as needed
        } else {
            return null;
        }
    }

  

 
}
