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

        $checkupload = mysqli_query($this->con, "SELECT file_path FROM Uploads WHERE upload_id ='$this->uploadid' limit 1");

        if (mysqli_num_rows($checkupload) == 0) {
            $this->file_path = null;

        } else {
           $uploadfetched = mysqli_fetch_array($checkupload);

            $this->file_path = $uploadfetched['file_path'];
        }
    }

    /**
     * @return mixed
     */
    public function getUploadfile_path()
    {
        return $this->file_path;
    }
    

  

 
}
