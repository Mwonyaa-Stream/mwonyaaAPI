<?php

    class Song {

        private $con;
        private $id;   
        private $mysqliData;
        private $title;
        private $artistId;
        private $albumId;
        private $genre;
        private $duration;
        private $path;
        private $plays;
        private $weekplays;

        public function __construct($con , $id) {
            $this->con = $con;
            $this->id = $id;

            $query = mysqli_query($this->con, "SELECT * FROM songs WHERE id='$this->id'");


            if(mysqli_num_rows($query) == 0){
                $this->id = null;
                $this->title = null;
                $this->artistId = null;
                $this->albumId = null;
                $this->genre = null;
                $this->duration = null;
                $this->path = null;
                $this->plays = null;
                $this->weekplays = null;
                return false;
            }

            else {
                $this->mysqliData = mysqli_fetch_array($query);

                $this->title = $this->mysqliData['title'];
                $this->artistId = $this->mysqliData['artist'];
                $this->albumId = $this->mysqliData['album'];
                $this->genre = $this->mysqliData['genre'];
                $this->duration = $this->mysqliData['duration'];
                $this->path = $this->mysqliData['path'];
                $this->plays = $this->mysqliData['plays'];
                $this->weekplays = $this->mysqliData['weekplays'];

                return true;
            }
       

        }

        public function getTitle(){
            return $this->title;
        }
        public function getId(){
            return $this->id;
        }

        /**
         * @return mixed|null
         */
        public function getArtistId()
        {
            return $this->artistId;
        }

        /**
         * @return mixed|null
         */
        public function getAlbumId()
        {
            return $this->albumId;
        }


        public function getArtist(){
            return new Artist($this->con, $this->artistId);
        }
        public function getAlbum(){
            return new Album($this->con, $this->albumId);
        }

        public function getDuration(){
            return $this->duration;
        }
        public function getPath(){
            return $this->path;
        }
        public function getMysqliData(){
            return  $this->mysqliData;
        }
        public function getGenre(){
            return new Genre($this->con, $this->genre);
        }

        public function getPlays(){
            return $this->plays;
        }
        public function getWeeklyplays(){
            return $this->weekplays;
        }

    



    }

?>