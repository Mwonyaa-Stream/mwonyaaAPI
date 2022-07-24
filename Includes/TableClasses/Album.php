<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


    class Album {

        private $con;
        private $id;
        private $title;
        private $artistId;
        private $genre;
        private $tag;
        private $totaltrackplays;
        private $artworkPath;
        private $datecreated;
        private $description;

      

        public function __construct($con , $id) {
            $this->con = $con;
            $this->id = $id;

            $query = mysqli_query($this->con, "SELECT * FROM albums WHERE id='$this->id'");
            $album = mysqli_fetch_array($query);


            if(mysqli_num_rows($query) < 1){
            
                $this->title = null;
                $this->id = null;
                $this->artistId = null;
                $this->genre = null;
                $this->artworkPath = null;
                $this->description = null;
                $this->datecreated = null;
            
            } else{                            
                $this->title = $album['title'];
                $this->id = $album['id'];
                $this->artistId = $album['artist'];
                $this->genre = $album['genre'];
                $this->datecreated = $album['datecreated'];
                $this->artworkPath = $album['artworkPath'];
                $this->description = $album['description'];
                $this->tag = $album['tag'];
                $this->totaltrackplays = $album['totalsongplays'];
            }


        }

        public function getTitle(){

            return $this->title;
        }

        public function getId(){
            return $this->id;
        }

        public function getArtistId(){
            return $this->artistId;
        }

        public function getArtist(){

            return  new Artist($this->con, $this->artistId);
        }

        public function getArtworkPath(){

            return $this->artworkPath;
        }

        public function getDatecreated(){

            $phpdate = strtotime($this->datecreated);
            $mysqldate = date('d M Y', $phpdate);

            return $mysqldate;
        }

        public function getDescription(){

            return $this->description;
        }



        public function getGenre(){

            return  new Genre($this->con, $this->genre);
        }

        public function getNumberOfSongs(){
            $sql = "SELECT COUNT(*) as count FROM songs WHERE album = '". $this->id . "'  limit 1";
            $result = mysqli_query($this->con, $sql);
            $data = mysqli_fetch_assoc($result);
            $track_count = floatval($data['count']);
            return $track_count;
        }

        public function getSongIds($offset,$no_of_records_per_page){
            $query = mysqli_query($this->con, "SELECT id FROM songs WHERE album='$this->id' ORDER BY albumOrder ASC LIMIT " . $offset . "," . $no_of_records_per_page . "");
            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['id']);
            }

            return $array;
        }

        public function getSongPaths(){
            $query = mysqli_query($this->con, "SELECT path FROM songs WHERE album='$this->id' ORDER BY albumOrder ASC");
            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['path']);
            }

            return $array;
        }

        /**
         * @return mixed
         */
        public function getTag()
        {
            return $this->tag;
        }

        /**
         * @return mixed
         */
        public function getTotaltrackplays()
        {
            return $this->totaltrackplays;
        }





    }