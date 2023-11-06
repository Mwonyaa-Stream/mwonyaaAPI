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
        private $releaseDate;
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
                $this->releaseDate = null;
            
            } else{                            
                $this->title = $album['title'];
                $this->id = $album['id'];
                $this->artistId = $album['artist'];
                $this->genre = $album['genre'];
                $this->releaseDate = $album['releaseDate'];
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

        public function getReleaseDate(): string
        {

            $php_date = strtotime($this->releaseDate);
            return date('M Y', $php_date);
        }

        public function getDescription(){

            if ($this->description === null) {
                return  "Genre: ".$this->getGenre()->getGenre()." • Release Date: ".$this->getReleaseDate();
            } else {
                return $this->description;
            }
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

            if($this->tag !== 'music'){
                $query = mysqli_query($this->con, "SELECT id FROM songs WHERE album='$this->id' ORDER BY dateAdded DESC LIMIT " . $offset . "," . $no_of_records_per_page . "");
            } else{
                $query = mysqli_query($this->con, "SELECT id FROM songs WHERE album='$this->id' ORDER BY albumOrder ASC LIMIT " . $offset . "," . $no_of_records_per_page . "");
            }

            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['id']);
            }

            return $array;
        }

        public function getSongPaths(){


            if($this->tag !== 'music'){
                $query = mysqli_query($this->con, "SELECT path FROM songs WHERE album='$this->id' ORDER BY dateAdded DESC");
            } else{
                $query = mysqli_query($this->con, "SELECT path FROM songs WHERE album='$this->id' ORDER BY albumOrder ASC");
            }
            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['path']);
            }

            return $array;
        }

        public function getTracks(){
            $allProducts = array();

            $all_tracks = "SELECT s.id,s.title, s.artist, ar.name, s.album, a.title,s.lyrics, a.artworkPath, s.genre,g.name, s.duration,s.path, s.albumOrder, s.plays, s.weekplays, s.lastplayed, s.tag, s.dateAdded FROM songs s INNER JOIN albums a on s.album = a.id INNER JOIN artists ar on s.artist = ar.id INNER JOIN genres g on s.genre = g.id WHERE s.album='$this->id' ORDER BY s.albumOrder ASC";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->con, $all_tracks);

            // Execute the query
            mysqli_stmt_execute($stmt);

            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $title, $artistID, $artistName, $albumID, $albumName,$lyrics, $albumArtwork, $trackGenreID, $trackGenreName, $duration, $path,$trackOrder,$trackPlays, $weeklyplays, $lastplayed,$tag, $dateAdded);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['title'] = $title;
                $temp['artist'] = $artistName;
                $temp['artistID'] = $artistID;
                $temp['album'] = $albumName;
                $temp['artworkPath'] = $albumArtwork;
                $temp['genre'] = $trackGenreName;
                $temp['genreID'] = $trackGenreID;
                $temp['duration'] = $duration;
                $temp['lyrics'] = $lyrics;
                $temp['path'] = $path;
                $temp['totalplays'] = $trackPlays;
                $temp['albumID'] = $this->id;
                array_push($allProducts, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            return $allProducts;
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