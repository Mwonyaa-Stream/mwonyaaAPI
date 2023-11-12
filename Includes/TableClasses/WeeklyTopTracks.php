<?php

class WeeklyTopTracks
{

    private $con;
    private $tracks_weekly;
    private $track_ids;


    public function __construct($con)
    {
        $this->con = $con;
        $this->track_ids = array();
        $this->tracks_weekly  = array();
        $check_weekly_query = mysqli_query($this->con, "SELECT `id`, `song_id`, `rank`, `weeks_on_chart`, `last_week_rank`, `peak_rank`, `entry_date` FROM `weeklytop10` ORDER BY rank ASC LIMIT 10");

        while ($row = mysqli_fetch_array($check_weekly_query)) {
            array_push($this->track_ids, $row['id']);
        }


    }

    public function WeeklyTrackSongs(): array
    {
        foreach ($this->track_ids as $row) {
            echo $row;
            $song = new Song($this->con,$row);
            $temp = array();
            $temp['id'] = $song->getId();
            $temp['title'] = $song->getTitle();
            $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
            $temp['lyrics'] = $song->getLyrics();
            $temp['path'] = $song->getPath();
            $temp['totalplays'] = $song->getPlays();
            $temp['albumID'] = $song->getAlbumId();


            array_push($this->tracks_weekly, $temp);
        }

        return $this->tracks_weekly;
    }









  

 
}
