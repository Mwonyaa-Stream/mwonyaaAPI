<?php

class Artist
{

    private $con;
    private $id;
    private $no;
    private $name;
    private $email;
    private $phone;
    private $facebookurl;
    private $twitterurl;
    private $instagramurl;
    private $RecordLable;
    private $profilephoto;
    private $profileimageid;
    private $coverimageid;
    private $coverimage;
    private $bio;
    private $genre;
    private $tag;
    private $dateAdded;
    private $overalplays;
    private $status;
    private $verified;
    private $circle_cost;
    private $circle_duration;
    private $circle_cost_maximum;

    public function __construct($con, $id)
    {
        $this->con = $con;
        $this->id = $id;

        $query = mysqli_query($this->con, "SELECT `no`, `id`, `name`, `email`, `phone`, `profile_image_id`,`cover_image_id`, `facebookurl`, `twitterurl`, `instagramurl`, `RecordLable`, `password`, `profilephoto`, `coverimage`, `bio`, `genre`, `datecreated`, `lastupdate`, `tag`, `overalplays`, `status`, `verified`, `circle_cost`, `circle_cost_maximum`, `circle_duration` FROM artists WHERE available = 1 AND id='$this->id' ");
        $artistfetched = mysqli_fetch_array($query);


        if (mysqli_num_rows($query) < 1) {
            $this->no = null;
            $this->id = null;
            $this->name = null;
            $this->email = null;
            $this->phone = null;
            $this->facebookurl = null;
            $this->twitterurl = null;
            $this->instagramurl = null;
            $this->RecordLable = null;
            $this->profilephoto = null;
            $this->coverimage = null;
            $this->profileimageid = null;
            $this->coverimageid = null;
            $this->bio = null;
            $this->genre = null;
            $this->tag = null;
            $this->dateAdded = null;
            $this->overalplays = null;
            $this->status = null;
            $this->verified = null;
            $this->circle_cost = null;
            $this->circle_duration = null;
            $this->circle_cost_maximum = null;
        } else {
            $this->no = $artistfetched['no'];
            $this->id = $artistfetched['id'];
            $this->name = $artistfetched['name'];
            $this->email = $artistfetched['email'];
            $this->phone = $artistfetched['phone'];
            $this->facebookurl = $artistfetched['facebookurl'];;
            $this->twitterurl = $artistfetched['twitterurl'];;
            $this->instagramurl = $artistfetched['instagramurl'];;
            $this->RecordLable = $artistfetched['RecordLable'];;
            $this->profilephoto = $artistfetched['profilephoto'];
            $this->coverimage = $artistfetched['coverimage'];
            $this->profileimageid = $artistfetched['profile_image_id'];
            $this->coverimageid = $artistfetched['cover_image_id'];
            $this->bio = $artistfetched['bio'];
            $this->genre = $artistfetched['genre'];
            $this->tag = $artistfetched['tag'];
            $this->dateAdded = $artistfetched['datecreated'];
            $this->overalplays = $artistfetched['overalplays'];
            $this->status = $artistfetched['status'];
            $this->verified = $artistfetched['verified'];
            $this->circle_cost = $artistfetched['circle_cost'];
            $this->circle_duration = $artistfetched['circle_duration'];
            $this->circle_cost_maximum = $artistfetched['circle_cost_maximum'];
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getdateadded()
    {
        $phpdate = strtotime($this->dateAdded);
        $mysqldate = date('d M Y', $phpdate);
        // $mysqldate = date( 'd/M/Y H:i:s', $phpdate );

        return $mysqldate;
    }

    public function getVerified()
    {
        return (int)$this->verified === 1;
    }

    public function getDetermineUserPermission($userID, $artistID)
    {

        $response = false;

        try {
            // Query to find the most recent subscription for the given user
            $stmt = $this->con->prepare("SELECT plan_end_datetime FROM pesapal_transactions WHERE user_id = ? AND subscription_type_id = ? ORDER BY plan_end_datetime DESC LIMIT 1");
            $stmt->bind_param("ss", $userID, $artistID);
            $stmt->execute();
            $stmt->bind_result($planEndDatetime);
            $stmt->fetch();
            $stmt->close();

            // Check if the most recent subscription is still active
            if ($planEndDatetime) {
                $currentDate = new DateTime('now', new DateTimeZone('UTC'));
                $endDate = new DateTime($planEndDatetime, new DateTimeZone('UTC'));

                if ($endDate > $currentDate) {
                    $response = true;
                }
            }
        } catch (Exception $e) {
            // Handle exceptions if needed, e.g., logging the error
        }

        return $response;

    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }




    /**
     * @return mixed|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed|null
     */
    public function getFacebookurl()
    {
        return $this->facebookurl;
    }

    /**
     * @return mixed|null
     */
    public function getTwitterurl()
    {
        return $this->twitterurl;
    }

    /**
     * @return mixed|null
     */
    public function getInstagramurl()
    {
        return $this->instagramurl;
    }

    /**
     * @return mixed|null
     */
    public function getRecordLable()
    {
        return $this->RecordLable;
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed|null
     */
    public function getOveralplays()
    {
        return $this->overalplays;
    }


    public function getProfilePath()
    {
        //return $this->profilephoto;
        if ($this->profileimageid) {
            $upload = new Uploads($this->con, $this->profileimageid);
            return $upload->getUploadfile_path() ?: "assets/images/default_profile_image.png";
        } else {
            return "assets/images/default_profile_image.png";
        }
    }

    public function getArtistCoverPath()
    {
        //return $this->coverimage;
        if ($this->coverimageid) {
            $upload = new Uploads($this->con, $this->coverimageid);
            return $upload->getUploadfile_path() ?: "assets/images/default_cover_image.png";
        } else {
            return "assets/images/default_cover_image.png";
        }
    }

    public function getArtistBio()
    {
        return $this->bio;
    }


    public function getGenre()
    {
        return $this->genre;
    }

    public function getIntro()
    {
        $genre = $this->getGname();
        $text_sentence = "Experience the exceptional talent of $this->name, an artist known for their diverse content in $genre and beyond. They are proudly signed with $this->RecordLable. Stay updated and captivated with the latest releases from $this->name.";
        return $this->convertToSentenceCase($text_sentence);
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getGenrename()
    {
        return new Genre($this->con, $this->genre);
    }

    public function getGname()
    {
        $gn = new Genre($this->con, $this->genre);

        return $gn->getGenre();
    }

    public function getCircleCost() : float
    {
        return $this->circle_cost;
    }

    public function getCircleDuration() : int
    {
        return $this->circle_duration;
    }
    public function getCircleCostMaximum():int
    {
        return $this->circle_cost_maximum;
    }


    function convertToSentenceCase($string)
    {
        $sentence = strtolower($string); // Convert the string to lowercase
        $sentence = ucwords($sentence); // Capitalize the first letters
        return $sentence;
    }

    public function getTotalSongs()
    {
        $query = mysqli_query($this->con, "SELECT COUNT(*) as totalsongs FROM songs WHERE available = 1 and  artist ='$this->id'");
        $row = mysqli_fetch_array($query);
        return $row['totalsongs'];
    }

    public function getTotalablums()
    {
        $query = mysqli_query($this->con, "SELECT COUNT(*) as totalalbum FROM albums WHERE available = 1 and artist ='$this->id'");
        $row = mysqli_fetch_array($query);
        return $row['totalalbum'];
    }


    public function getTotalPlays()
    {
        // Assuming $this->con is your database connection

        // Prepare the statement
        $stmt = mysqli_prepare($this->con, "CALL GetTotalListeners(?)");

        if (!$stmt) {
            die("Error preparing statement: " . mysqli_error($this->con));
        }

        // Bind the parameter
        mysqli_stmt_bind_param($stmt, "s", $this->id);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Bind the result variable
        mysqli_stmt_bind_result($stmt, $totalListeners);

        // Fetch the result
        mysqli_stmt_fetch($stmt);

        // Close the statement
        mysqli_stmt_close($stmt);

        // Format the total listeners
        $formattedTotalListeners = number_format($totalListeners);

        return "$formattedTotalListeners Listeners.";
    }


    public function getLatestRelease()
    {
        $query = mysqli_query($this->con, "SELECT a.id as id FROM albums a INNER JOIN songs s ON a.id = s.album WHERE a.available = 1 AND  a.artist='$this->id' AND a.tag != 'ad' GROUP BY a.id ORDER BY a.datecreated DESC LIMIT 1");

        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_array($query);
            $id = $row['id'];
            return new Album($this->con, $id);
        }

        return null; // Return null or handle the case when no result is found
    }

    public function getSongIds()
    {

        if ($this->tag !== 'music') {
//            $query = mysqli_query($this->con, "SELECT id, featuring FROM songs WHERE  available = 1 AND (artist='$this->id' OR FIND_IN_SET('$this->id', featuring) > 0) AND tag != 'ad' ORDER BY `dateAdded` DESC LIMIT 8");
            $query = mysqli_query($this->con, "select s.id,t.total_plays, s.title, a.name, s.featuring from track_plays t join songs s on s.id = t.songid join artists a on a.id = s.artist where s.tag != 'ad' and s.available = 1 and (a.id='$this->id' OR FIND_IN_SET('$this->id', s.featuring) > 0) order by t.total_plays desc limit 8");
        } else {
//            $query = mysqli_query($this->con, "SELECT id, featuring FROM songs WHERE available = 1 AND (artist='$this->id' OR FIND_IN_SET('$this->id', featuring) > 0) AND tag != 'ad' ORDER BY plays DESC LIMIT 8");
            $query = mysqli_query($this->con, "select s.id,t.total_plays, s.title, a.name, s.featuring from track_plays t join songs s on s.id = t.songid join artists a on a.id = s.artist where s.tag != 'ad' and s.available = 1 and (a.id='$this->id' OR FIND_IN_SET('$this->id', s.featuring) > 0) order by t.total_plays desc limit 8");


        }
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            $songId = $row['id'];
            $featuring = $row['featuring'];

            // Append featuring artists to the song ID
            if (!empty($featuring)) {
                $featuringArray = explode(',', $featuring);
                $songId .= ',' . implode(',', $featuringArray);
            }

            array_push($array, $songId);
        }

        return $array;
    }


    public function getRelatedArtists()
    {
        $rel_array_query = mysqli_query($this->con, "SELECT id FROM artists WHERE available = 1 AND genre='$this->genre' AND id != '$this->id'  ORDER BY overalplays DESC Limit 8");
        $rel_array = array();

        while ($rel_array_row = mysqli_fetch_array($rel_array_query)) {
            array_push($rel_array, $rel_array_row['id']);
        }

        return $rel_array;
    }

    public function getArtistAlbums()
    {
        $query = mysqli_query($this->con, "SELECT a.id as id FROM albums a INNER JOIN songs s ON a.id = s.album WHERE a.available = 1 and a.artist='$this->id' and a.tag != 'ad'GROUP BY a.id ORDER BY a.datecreated DESC LIMIT 8");
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            array_push($array, $row['id']);
        }

        return $array;
    }

    public function getArtistDiscography(): array
    {
        $query = mysqli_query($this->con, "SELECT a.id as id FROM albums a INNER JOIN songs s ON a.id = s.album WHERE a.available = 1 and a.artist='$this->id' and a.tag != 'ad'GROUP BY a.id ORDER BY a.datecreated desc ");
        $array = array();

        while ($row = mysqli_fetch_array($query)) {
            array_push($array, $row['id']);
        }

        return $array;
    }

    public function getFollowStatus(string $user_ID)
    {
        $stmt = $this->con->prepare("SELECT COUNT(*) FROM artistfollowing WHERE artistid = ? AND userid = ?");
        $stmt->bind_param("ss", $this->id, $user_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


}
