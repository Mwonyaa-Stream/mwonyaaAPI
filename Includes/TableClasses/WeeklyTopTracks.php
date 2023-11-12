<?php

class WeeklyTopTracks
{

    private $con;
    private $tracks_weekly;
    private $track_ids;

    private $track_id;
    private $title ;
    private $weekartist ;
    private $weekimage;


    public function __construct($con)
    {
        $this->con = $con;
        $this->track_ids = array();
        $this->tracks_weekly  = array();
        $check_weekly_query = mysqli_query($this->con, "SELECT `id`, `song_id`, `rank`, `weeks_on_chart`, `last_week_rank`, `peak_rank`, `entry_date` FROM `weeklytop10` ORDER BY rank ASC LIMIT 10");

        while ($row = mysqli_fetch_array($check_weekly_query)) {
            array_push($this->track_ids, $row['song_id']);
        }




    }
    public function getWeeklyData(): array
    {
        // Fetch metadata for the top track
        $this->WeeklyMetaData();
        $currentDate = date('D j F Y');
        $interestingHeading = $this->title . " by " . $this->weekartist;

        $feat_weekly = array();
        $feat_weekly['heading'] = "Weekly Top 10";
        $feat_weekly['subheading'] = "Featuring " . $interestingHeading . ". This track has taken the heat up again, securing the number one spot.";
        $feat_weekly['weekartist'] = $this->weekartist;  // Use the property from the class
        $feat_weekly['weekdate'] = $currentDate;  // Update this value if needed
        $feat_weekly['weekimage'] = $this->weekimage;
        $feat_weekly['type'] = "timely";
        $feat_weekly['Tracks'] = $this->WeeklyTrackSongs();

        if($this->weekartist == null){
            return array();
        }

        return $feat_weekly;
    }


    public function WeeklyMetaData(){
        $stmt = mysqli_prepare($this->con, "SELECT songs.id as song_id, songs.title, artists.name as weekartist, artists.profilephoto as weekimage FROM songs JOIN artists ON songs.artist = artists.id WHERE songs.id = ?");

        // Bind the parameter
        mysqli_stmt_bind_param($stmt, "i", $this->track_ids[0]);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get the result
        $meta_data = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($meta_data) == 0) {
            $this->track_id = null;
            $this->title = null;
            $this->weekartist = null;
            $this->weekimage = null;
        } else {
            $metaFetched = mysqli_fetch_array($meta_data);
            $this->track_id = $metaFetched['song_id'];
            $this->title = $metaFetched['title'];
            $this->weekartist = $metaFetched['weekartist'];
            $this->weekimage = $metaFetched['weekimage'];
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    }




    public function WeeklyTrackSongs(): array
    {
        foreach ($this->track_ids as $row) {
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
