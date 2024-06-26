<?php

    class SearchSlider {

        private $con;
        private $id;
        private $name;
        private $playlistID;
        private $owner;
        private $description;
        private $imagepath;
        private $status;
        private $date_created;
        private $mysqliData;


        public function __construct($con , $id) {
            $this->con = $con;
            $this->id = $id;

            $query = mysqli_query($this->con, "SELECT `id`, `name`, `playlistID`, `owner`, `description`, `imagepath`, `status`, `date_created` FROM `search_slider` WHERE id='$this->id'");


            if(mysqli_num_rows($query) == 0){
                $this->id = null;

                return false;
            }

            else {
                $this->mysqliData = mysqli_fetch_array($query);
                $this->id = $this->mysqliData['id'];
                $this->name = $this->mysqliData['name'];
                $this->playlistID = $this->mysqliData['playlistID'];
                $this->owner = $this->mysqliData['owner'];
                $this->description = $this->mysqliData['description'];
                $this->imagepath = $this->mysqliData['imagepath'];
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
         * @return mixed
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @return mixed
         */
        public function getPlaylistID()
        {
            return $this->playlistID;
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
        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @return mixed
         */
        public function getImagepath()
        {
            return $this->imagepath;
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
        public function getDateCreated()
        {
            return $this->date_created;
        }








    }

?>