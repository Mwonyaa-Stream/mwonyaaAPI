<?php

class Genre
{

    private $con;
    private $genreid;
    private $genre;
    private $tag;



    public function __construct($con, $genreid)
    {
        $this->con = $con;
        $this->genreid = $genreid;

        $checkgenre = mysqli_query($this->con, "SELECT name,tag FROM genres WHERE id='$this->genreid'");
        $genrefetched = mysqli_fetch_array($checkgenre);

        if (mysqli_num_rows($checkgenre) == 0) {
            $this->genre = null;
            $this->tag = null;
            $this->genreid = null;

        } else {
            $this->genre = $genrefetched['name'];
            $this->tag = $genrefetched['tag'];
        }
    }

    /**
     * @return mixed
     */
    public function getGenreid()
    {
        return $this->genreid;
    }



    public function getGenre()
    {
        return $this->genre;
    }

    public function getTag()
    {
        return $this->tag;
    }


    public function getGenreTopPic(){
        $sql = "SELECT id FROM songs  WHERE genre = '$this->genreid' AND tag != 'ad' ORDER BY `songs`.`plays` DESC LIMIT 1";
        $result = mysqli_query($this->con, $sql);
        $id_data = mysqli_fetch_assoc($result);
        $song = new Song($this->con,$id_data['id']);

        return $song->getAlbum()->getArtworkPath();
    }

    public function getGenre_Songs($limit){

        //fetch other categories Begin
        $song_ids = array();
        $home_genre_tracks = array();
        $genre_song_stmt = "SELECT id FROM songs  WHERE genre = '$this->genreid' AND tag != 'ad' ORDER BY `songs`.`plays` DESC LIMIT $limit";
        $genre_song_stmt_result = mysqli_query($this->con, $genre_song_stmt);

        while ($row = mysqli_fetch_array($genre_song_stmt_result)) {

            array_push($song_ids, $row['id']);
        }

        foreach ($song_ids as $row) {
            $song = new Song($this->con,$row);
            $temp = array();
            $temp['id'] = $song->getId();
            $temp['title'] = $song->getTitle();
            $temp['artist'] = $song->getArtist()->getName();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
            $temp['path'] = $song->getPath();
            $temp['totalplays'] = $song->getPlays();
            $temp['weeklyplays'] = $song->getWeeklyplays();


            array_push($home_genre_tracks, $temp);
        }

        return $home_genre_tracks;
    }

    public function getGenreDJ_Songs($limit){

        //fetch other categories Begin
        $song_ids = array();
        $home_genre_tracks = array();
        $genre_song_stmt = "SELECT id FROM songs  WHERE genre = '$this->genreid' AND  tag='dj' ORDER BY `songs`.`plays` DESC LIMIT $limit";
        $genre_song_stmt_result = mysqli_query($this->con, $genre_song_stmt);

        while ($row = mysqli_fetch_array($genre_song_stmt_result)) {
            array_push($song_ids, $row['id']);
        }

        foreach ($song_ids as $row) {
            $song = new Song($this->con,$row);
            $temp = array();
            $temp['id'] = $song->getId();
            $temp['title'] = $song->getTitle();
            $temp['artist'] = $song->getArtist()->getName();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
            $temp['path'] = $song->getPath();
            $temp['totalplays'] = $song->getPlays();
            $temp['weeklyplays'] = $song->getWeeklyplays();


            array_push($home_genre_tracks, $temp);
        }

        return $home_genre_tracks;
    }


  

 
}
