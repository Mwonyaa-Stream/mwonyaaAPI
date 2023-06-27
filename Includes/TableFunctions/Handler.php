<?php

class Handler
{

    private $ImageBasepath = "https://mwonyaa.com/";
    public $pageNO;
    public $albumID;
    private $conn;
    private $version;
    private $exe_status;
    public $user_id;
    public $liteRecentTrackList;
    public $liteLikedTrackList;
    public $update_date;

    // track update info

    public function __construct($con)
    {
        $this->conn = $con;
        $this->version = 9; // VersionCode
    }


    function readArtistProfile(): array
    {

        $itemRecords = array();

        $artistID = htmlspecialchars(strip_tags($_GET["artistID"]));
        $this->pageNO = htmlspecialchars(strip_tags($_GET["page"]));

        if ($artistID) {
            $this->pageNO = floatval($this->pageNO);
            $artist_instance = new Artist($this->conn, $artistID);

            $itemRecords["page"] = $this->pageNO;
            $itemRecords["Artist"] = array();

            // Artist Bio
            $artist_into = array();
            $temp = array();
            $temp['id'] = $artist_instance->getId();
            $temp['name'] = $artist_instance->getName();
            $temp['profilephoto'] = $artist_instance->getProfilePath();
            $temp['coverimage'] = $artist_instance->getArtistCoverPath();
            $temp['monthly'] = $artist_instance->getTotalPlays();
            array_push($artist_into, $temp);

            $artistIntro = array();

            $artistIntro['ArtistIntro'] = $artist_into;
            $artistIntro['Type'] = "intro";
            array_push($itemRecords["Artist"], $artistIntro);


            // popular tracks
            $populartracks = $artist_instance->getSongIds();
            $popular = array();
            foreach ($populartracks as $songId) {
                $song = new Song($this->conn, $songId);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();

                array_push($popular, $temp);
            }


            $popular_temps = array();
            $popular_temps['heading'] = "Most Played";
            $popular_temps['Type'] = "trending";
            $popular_temps['Tracks'] = $popular;
            array_push($itemRecords["Artist"], $popular_temps);


            // Artist Pick - Top playlist created by the Artist
            $stmt = $this->conn->prepare("SELECT `id`, `tile`, `artistID`, `CoverArt`, `songID`, `date_created` FROM `artistpick` WHERE  artistID=? LIMIT 1");
            $stmt->bind_param("s", $artistID);
            $stmt->execute();
            $result = $stmt->get_result();

            $ArtistPick = [];

            if ($row = $result->fetch_assoc()) {
                $ar_id = $row['id'];
                $ar_title = $row['tile'];
                $ar_artistID = $row['artistID'];
                $ar_CoverArt = $row['CoverArt'];
                $ar_songID = $row['songID'];
                $ar_date_created = $row['date_created'];
                $ar_Artist = new Artist($this->conn, $ar_artistID);
                $ar_Song = new Song($this->conn, $ar_songID);

                $temp = [
                    'id' => $ar_id,
                    'type' => "Playlist",
                    'out_now' => $ar_title . " - out now",
                    'coverimage' => $ar_CoverArt,
                    'song_title' => $artist_instance->getName() . " - " . $ar_Song->getAlbum()->getTitle(),
                    'song_cover' => $ar_Song->getAlbum()->getArtworkPath(),
                ];
                array_push($ArtistPick, $temp);
            } else {
                // latest release
                $arry = $artist_instance->getLatestRelease();
                if ($arry !== null) {
                    $temp = [
                        'id' => $arry->getId(),
                        'type' => "Album",
                        'out_now' => $arry->getTitle() . " - out now",
                        'coverimage' => $arry->getArtworkPath(),
                        'song_title' => $arry->getArtist()->getName() . " - " . $arry->getTitle(),
                        'song_cover' => $arry->getArtworkPath(),
                    ];
                    array_push($ArtistPick, $temp);
                }
            }


            $artistpick_array = array();
            $artistpick_array['heading'] = "Artist Pick";
            $artistpick_array['Type'] = "pick";
            $artistpick_array['ArtistPick'] = $ArtistPick;
            array_push($itemRecords["Artist"], $artistpick_array);

            // popular releases
            $albumsIDs = $artist_instance->getArtistAlbums();
            $popular_release = array();
            foreach ($albumsIDs as $Id) {
                $album = new Album($this->conn, $Id);
                $temp = array();
                $temp['id'] = $album->getId();
                $temp['title'] = $album->getTitle();
                $temp['artist'] = $album->getArtist()->getName();
                $temp['genre'] = $album->getGenre()->getGenre();
                $temp['artworkPath'] = $album->getArtworkPath();
                $temp['tag'] = $album->getTag();
                $temp['description'] = $album->getDescription();
                $temp['datecreated'] = $album->getDatecreated();
                $temp['totalsongplays'] = $album->getTotaltrackplays();


                array_push($popular_release, $temp);
            }

            $popular_temps = array();
            $popular_temps['heading'] = "Popular Release";
            $popular_temps['Type'] = "release";
            $popular_temps['ArtistAlbum'] = $popular_release;
            array_push($itemRecords["Artist"], $popular_temps);


            //Related Artist
            $related_artists = $artist_instance->getRelatedArtists();
            $popular_release = array();
            foreach ($related_artists as $re_artist) {
                $artist = new Artist($this->conn, $re_artist);
                $temp = array();
                $temp['id'] = $artist->getId();
                $temp['name'] = $artist->getName();
                $temp['genre'] = $artist->getGenrename()->getGenre();
                $temp['profilephoto'] = $artist->getProfilePath();
                array_push($popular_release, $temp);
            }

            $popular_temps = array();
            $popular_temps['heading'] = "Related Artist";
            $popular_temps['Type'] = "related_artist";

            $popular_temps['RelatedArtist'] = $popular_release;
            array_push($itemRecords["Artist"], $popular_temps);


            // Event
            $ArtistEvent = array();
            $artist_event = new ArtistEvents($this->conn, $artistID);
            $temp = array();

            if($artist_event->getId() != null){
                $temp['id'] = $artist_event->getId();
                $temp['name'] = $artist_event->getName();
                $temp['title'] = $artist_event->getTitle();
                $temp['description'] = $artist_event->getDescription();
                $temp['venue'] = $artist_event->getVenu();
                $temp['date'] = $artist_event->getDate();
                $temp['time'] = $artist_event->getTime();
                array_push($ArtistEvent, $temp);
            }


            $events_array = array();
            $events_array['heading'] = "Artist Events";
            $events_array['Type'] = "events";
            $events_array['Events'] = $ArtistEvent;
            array_push($itemRecords["Artist"], $events_array);

            // Artist Bio
            $bio_array = array();
           if ($artist_instance->getId() != null){
               $temp = array();
               $temp['id'] = $artist_instance->getId();
               $temp['name'] = $artist_instance->getName();
               $temp['email'] = $artist_instance->getEmail();
               $temp['phone'] = $artist_instance->getPhone();
               $temp['facebookurl'] = $artist_instance->getFacebookurl();
               $temp['twitterurl'] = $artist_instance->getTwitterurl();
               $temp['instagramurl'] = $artist_instance->getInstagramurl();
               $temp['RecordLable'] = $artist_instance->getRecordLable();
               $temp['profilephoto'] = $artist_instance->getProfilePath();
               $temp['coverimage'] = $artist_instance->getArtistCoverPath();

               $temp['bio'] = $artist_instance->getArtistBio();
               $temp['genre'] = $artist_instance->getGenrename()->getGenre();
               $temp['datecreated'] = $artist_instance->getdateadded();
               $temp['tag'] = $artist_instance->getTag();
               $temp['overalplays'] = $artist_instance->getOveralplays();
               $temp['monthly'] = $artist_instance->getTotalPlays();
               $temp['status'] = $artist_instance->getStatus();
               array_push($bio_array, $temp);
           }

            $events_array = array();
            $events_array['heading'] = "Artist Bio";
            $events_array['Type'] = "bio";
            $events_array['Bio'] = $bio_array;
            array_push($itemRecords["Artist"], $events_array);


            $itemRecords["total_pages"] = 1;
            $itemRecords["total_results"] = 1;


        }
        return $itemRecords;
    }


    function allCombined(): array
    {

        // Set up the prepared statement to retrieve the number of genres
        $tag_music = "music";
        $genre_count_stmt = mysqli_prepare($this->conn, "SELECT COUNT(DISTINCT g.id) as total_genres FROM genres g JOIN songs s ON s.genre = g.id WHERE s.tag = ?");

        mysqli_stmt_bind_param($genre_count_stmt, "s", $tag_music);

        mysqli_stmt_execute($genre_count_stmt);

        mysqli_stmt_bind_result($genre_count_stmt, $total_genres);

        mysqli_stmt_fetch($genre_count_stmt);

        mysqli_stmt_close($genre_count_stmt);

        // Calculate the total number of pages
        $no_of_records_per_page = 10;
        $total_pages = ceil($total_genres / $no_of_records_per_page);

        // Retrieve the "page" parameter from the GET request
        $page = isset($_GET['page']) ? intval(htmlspecialchars(strip_tags($_GET["page"]))) : 1;
        $userID = isset($_GET['userID']) ? htmlspecialchars(strip_tags($_GET["userID"])) : null;


        // Validate the "page" parameter
        if ($page < 1 || $page > $total_pages) {
            $page = 1;
        }

        // Calculate the offset
        $offset = ($page - 1) * $no_of_records_per_page;


        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {

            // get_Slider_banner
            $sliders = array();
            // Set up the prepared statement
            $slider_query = "SELECT ps.id, ps.playlistID, ps.imagepath FROM playlist_sliders ps WHERE status = 1 ORDER BY date_created DESC LIMIT 8;";
            $stmt = mysqli_prepare($this->conn, $slider_query);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $playlistID, $imagepath);
            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['playlistID'] = $playlistID;
                $temp['imagepath'] = $imagepath;
                array_push($sliders, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $slider_temps = array();
            $slider_temps['heading'] = "Discover";
            $slider_temps['type'] = "slider";
            $slider_temps['featured_sliderBanners'] = $sliders;
            array_push($menuCategory, $slider_temps);
            // end get_Slider_banner

            // weekly Now
            $featured_weekly = array();
            $tracks_weekly = array();
            $weekly_now_sql = "SELECT `id`, `song_id`, `rank`, `weeks_on_chart`, `last_week_rank`, `peak_rank`, `entry_date` FROM `weeklytop10` ORDER BY rank ASC LIMIT 10";
            $stmt = $this->conn->prepare($weekly_now_sql);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $song = new Song($this->conn, $row['song_id']);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();
                $temp['position'] = $row['rank'];
                $temp['trend'] = ($row['rank'] % 3 === 0) ? false : true;

                array_push($tracks_weekly, $temp);
            }
            mysqli_stmt_close($stmt);


            // Close the prepared statement
            $feat_weekly = array();
            $feat_weekly['heading'] = "Mwonya Top 10";
            $feat_weekly['subheading'] = "THE WEEK’S MOST POPULAR CURRENT SONGS ACROSS ALL GENRES.";
            $feat_weekly['type'] = "timely";
            $feat_weekly['Tracks'] = $tracks_weekly;
            array_push($menuCategory, $feat_weekly);

            // end weekly


            // recently played array
            $recently_played = array();
            $recently_played['heading'] = "Recently Played";
            $recently_played['type'] = "recently";
            $recently_played['subheading'] = "Tracks Last Listened to";
            array_push($menuCategory, $recently_played);


            // Trending Now
            $featured_trending = array();
            $tracks_trending = array();
            $trending_now_sql = "SELECT songid as song_id, COUNT(*) AS play_count FROM frequency WHERE lastPlayed BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() GROUP BY songid ORDER BY play_count DESC LIMIT 10";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $trending_now_sql);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $song_id, $play_count);
            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                array_push($featured_trending, $song_id);
            }
            mysqli_stmt_close($stmt);

            foreach ($featured_trending as $track) {
                $song = new Song($this->conn, $track);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();
                array_push($tracks_trending, $temp);

            }

            // Close the prepared statement
            $feat_trend = array();
            $feat_trend['heading'] = "Trending Now";
            $feat_trend['type'] = "trend";
            $feat_trend['Tracks'] = $tracks_trending;
            array_push($menuCategory, $feat_trend);

            $image_temp = array();
            $image_temp['ad_title'] = "Vote is on";
            $image_temp['type'] = "image_ad";
            $image_temp['ad_description'] = "Time to vote";
            $image_temp['ad_link'] = "http://urbanflow256.com/home/index.html";
            $image_temp['ad_image'] = "http://urbanflow256.com/ad_images/fakher.png";
            array_push($menuCategory, $image_temp);


            // Recommended
            $recommendedSongs = array();

            // Query to fetch recommended songs for the given user ID
            $recommendation_table_Query = "SELECT `id`, `user_id`, `recommended_songs`, `created_at` FROM `recommendations` WHERE `user_id` =  '$userID'";
            $table_data = mysqli_query($this->conn, $recommendation_table_Query);

            while ($row = mysqli_fetch_array($table_data)) {
                $songs = explode(',', $row['recommended_songs']);
                $recommendedSongs = array_merge($recommendedSongs, $songs);
            }

            // Pagination
            $itemsPerPage = 10; // Number of items to display per page
            $totalItems = count($recommendedSongs); // Total number of recommended songs


            // Shuffle the array for the first page
            shuffle($recommendedSongs);

            // Calculate the starting and ending indexes for the current page
            $startIndex = ($page - 1) * $itemsPerPage;
            $endIndex = min($startIndex + $itemsPerPage - 1, $totalItems - 1);

            // Get the recommended songs for the current page
            $songsForPage = array_slice($recommendedSongs, $startIndex, $endIndex - $startIndex + 1);

            //trackList
            $R_trackListArray = array();


            foreach ($songsForPage as $track) {
                $song = new Song($this->conn, $track);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();
                array_push($R_trackListArray, $temp);

            }

            // Close the prepared statement
            $feat_recommended = array();
            $feat_recommended['heading'] = "Recommended";
            $feat_recommended['type'] = "trend";
            $feat_recommended['Tracks'] = $R_trackListArray;
            array_push($menuCategory, $feat_recommended);

            //get genres
            $featured_genres = array();
            $top_genre_stmt = "SELECT DISTINCT(genre),g.name,s.tag FROM songs s INNER JOIN genres g on s.genre = g.id WHERE s.tag IN ('music') ORDER BY s.plays DESC LIMIT 8";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $top_genre_stmt);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $genre, $name, $tag);
            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $genre;
                $temp['name'] = $name;
                $temp['tag'] = $tag;
                array_push($featured_genres, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
            $feat_genres = array();
            $feat_genres['heading'] = "Featured genres";
            $feat_genres['type'] = "genre";
            $feat_genres['featuredGenres'] = $featured_genres;
            array_push($menuCategory, $feat_genres);

            // recommemded


            //get Featured Artist
            $featuredCategory = array();
            $musicartistQuery = "SELECT id, profilephoto, name FROM artists WHERE tag='music' AND featured = 1 ORDER BY RAND () LIMIT 20";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $musicartistQuery);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $profilephoto, $name);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['profilephoto'] = $profilephoto;
                $temp['name'] = $name;
                array_push($featuredCategory, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_Cat_temps = array();
            $feat_Cat_temps['heading'] = "Featured Artists";
            $feat_Cat_temps['type'] = "artist";
            $feat_Cat_temps['featuredArtists'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end featuredArtist
            ///
            ///
            $text_temp = array();
            $text_temp['ad_title'] = "Mwonya Artist Program";
            $text_temp['type'] = "text_ad";
            $text_temp['ad_description'] = "Empowering Ugandan Music: Creating Opportunities for Aspiring Artists";
            $text_temp['ad_link'] = "https://artist.mwonya.com/";
            $text_temp['ad_image'] = "http://urbanflow256.com/home/images/rectangleimage.png";
            array_push($menuCategory, $text_temp);


            //get the latest album Release less than 14 days old
            $featured_albums = array();
            $featuredAlbums = array();
            $featured_album_Query = "SELECT a.id as id FROM albums a INNER JOIN songs s ON a.id = s.album WHERE a.datecreated > DATE_SUB(NOW(), INTERVAL 14 DAY) GROUP BY a.id ORDER BY a.datecreated DESC LIMIT 8";
            $featured_album_Query_result = mysqli_query($this->conn, $featured_album_Query);
            while ($row = mysqli_fetch_array($featured_album_Query_result)) {
                array_push($featured_albums, $row['id']);
            }

            foreach ($featured_albums as $row) {
                $al = new Album($this->conn, $row);
                $temp = array();
                $temp['id'] = $al->getId();
                $temp['heading'] = "New Release From";
                $temp['title'] = $al->getTitle();
                $temp['artworkPath'] = $al->getArtworkPath();
                $temp['tag'] = $al->getDatecreated() . ' - ' . $al->getTag();
                $temp['artistId'] = $al->getArtistId();
                $temp['artist'] = $al->getArtist()->getName();
                $temp['artistArtwork'] = $al->getArtist()->getProfilePath();
                $temp['Tracks'] = $al->getTracks();
                array_push($featuredAlbums, $temp);
            }

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "New Release on Mwonya";
            $feat_albums_temps['type'] = "newRelease";
            $feat_albums_temps['HomeRelease'] = $featuredAlbums;
            array_push($menuCategory, $feat_albums_temps);
            ///end latest Release 14 days


            //get Featured Playlist
            $featuredPlaylist = array();
            $featured_playlist_Query = "SELECT id,name, owner, coverurl FROM playlists where status = 1 AND featuredplaylist ='yes' ORDER BY RAND () LIMIT 20";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $featured_playlist_Query);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $name, $owner, $coverurl);
            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['name'] = $name;
                $temp['owner'] = $owner;
                $temp['coverurl'] = $coverurl;
                array_push($featuredPlaylist, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_playlist_temps = array();
            $feat_playlist_temps['heading'] = "Featured Playlists";
            $feat_playlist_temps['type'] = "playlist";
            $feat_playlist_temps['featuredPlaylists'] = $featuredPlaylist;
            array_push($menuCategory, $feat_playlist_temps);
            ///end featuredPlaylist


            //get featured Album
            $featured_Albums = array();

            $featured_album_Query = "SELECT id,title,artworkPath, tag FROM albums WHERE tag = \"music\" AND featured = 1 ORDER BY RAND() LIMIT 10";

            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $featured_album_Query);

            // Execute the query
            mysqli_stmt_execute($stmt);

            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $title, $artworkPath, $tag);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['title'] = $title;
                $temp['artworkPath'] = $artworkPath;
                $temp['tag'] = $tag;
                array_push($featured_Albums, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "Featured Albums";
            $feat_albums_temps['type'] = "albums";
            $feat_albums_temps['featuredAlbums'] = $featured_Albums;
            array_push($menuCategory, $feat_albums_temps);
            ///end featuredAlbums


            //get featured Dj mixes
            $featured_dj_mixes = array();

            $featured_album_Query = "SELECT id,title,artworkPath,tag FROM albums WHERE tag = \"dj\" GROUP BY artist  ORDER BY datecreated DESC LIMIT 8";

            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $featured_album_Query);

            // Execute the query
            mysqli_stmt_execute($stmt);

            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $title, $artworkPath, $tag);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['title'] = $title;
                $temp['artworkPath'] = $artworkPath;
                $temp['tag'] = $tag;
                array_push($featured_dj_mixes, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_dj_temps = array();
            $feat_dj_temps['heading'] = "DJs Vibuu++";
            $feat_dj_temps['type'] = "djs";
            $feat_dj_temps['FeaturedDjMixes'] = $featured_dj_mixes;
            array_push($menuCategory, $feat_dj_temps);
            ///end featuredAlbums


        }

//        // Use a prepared statement and a JOIN clause to get genre and song data in a single query
//        $stmt = $this->conn->prepare("SELECT g.id, g.name, g.tag, s.id as song_id, s.title, s.plays
//           FROM genres g
//           JOIN songs s ON s.genre = g.id
//           WHERE s.tag = 'music'
//           GROUP BY g.id
//           ORDER BY s.plays DESC
//           LIMIT ?, ?");
//
//        $stmt->bind_param("ii", $offset, $no_of_records_per_page);
//        $stmt->execute();
//        $result = $stmt->get_result();
//
//        while ($row = $result->fetch_assoc()) {
//            $genre = new Genre($this->conn, $row['id']);
//            $temp = array();
//            $temp['id'] = $row['id'];
//            $temp['name'] = $row['name'];
//            $temp['type'] = "directSongs";
//            $temp['tag'] = $row['tag'];
//
//            // Use a LIMIT clause in the inner query to get only the top 6 played songs for each genre
//            $temp['Tracks'] = $genre->getGenre_Songs(6);
//            array_push($menuCategory, $temp);
//        }

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["featured"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_genres;

        return $itemRecords;
    }


    function UserLibrary(): array
    {
        $page = isset($_GET['page']) ? intval(htmlspecialchars(strip_tags($_GET["page"]))) : 1;
        $libraryUserID = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET["id"])) : "mw603382d49906aPka";
        $total_pages = 1;

        // Validate the "page" parameter
        if ($page < 1 || $page > $total_pages) {
            $page = 1;
        }

        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {


            //get the latest album Release less than 14 days old
            $featured_albums = array();
            $featuredAlbums = array();
            $featured_album_Query = "SELECT DISTINCT a.id as id FROM albums a JOIN songs s ON a.id = s.album JOIN artistfollowing af ON s.artist = af.artistid WHERE af.userid = '$libraryUserID' AND a.datecreated > DATE_SUB(NOW(), INTERVAL 2 WEEK) ORDER BY a.datecreated DESC";
            $featured_album_Query_result = mysqli_query($this->conn, $featured_album_Query);
            while ($row = mysqli_fetch_array($featured_album_Query_result)) {
                array_push($featured_albums, $row['id']);
            }

            foreach ($featured_albums as $row) {
                $al = new Album($this->conn, $row);
                $temp = array();
                $temp['id'] = $al->getId();
                $temp['heading'] = "New Release For You";
                $temp['title'] = $al->getTitle();
                $temp['artworkPath'] = $al->getArtworkPath();
                $temp['tag'] = $al->getDatecreated() . ' - ' . $al->getTag();;
                $temp['artistId'] = $al->getArtistId();
                $temp['artist'] = $al->getArtist()->getName();
                $temp['artistArtwork'] = $al->getArtist()->getProfilePath();
                $temp['Tracks'] = $al->getTracks();
                array_push($featuredAlbums, $temp);
            }

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "New Releases From Artists You Follow.";
            $feat_albums_temps['HomeRelease'] = $featuredAlbums;
            array_push($menuCategory, $feat_albums_temps);
            ///end latest Release 14 days
            ///
            //get unfollowed artist based on followed artist genre
            $featuredCategory = array();
            $musicartistQuery = "SELECT a.id,a.profilephoto,a.name FROM artists a LEFT JOIN (SELECT genre, count(artistid) as follow_count FROM artists JOIN artistfollowing ON artists.id = artistfollowing.artistid WHERE artistfollowing.userid = '$libraryUserID' group by genre) as s on a.genre=s.genre WHERE (s.follow_count>0 and a.id NOT IN ( SELECT artistid FROM artistfollowing WHERE userid = '$libraryUserID' ) OR (s.follow_count is null and s.genre is null)) and a.status = 1 ORDER BY RAND() LIMIT 5;";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $musicartistQuery);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $profilephoto, $name);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['profilephoto'] = $profilephoto;
                $temp['name'] = $name;
                array_push($featuredCategory, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_Cat_temps = array();
            $feat_Cat_temps['heading'] = "Discover new Artists to listen and follow.";
            $feat_Cat_temps['featuredArtists'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end unfollowed


            //get Featured Playlist
            $featuredPlaylist = array();
            $featured_playlist_Query = "SELECT id,name, owner, coverurl FROM playlists where status = 1 AND featuredplaylist ='yes' ORDER BY RAND () LIMIT 20";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $featured_playlist_Query);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $name, $owner, $coverurl);
            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['name'] = $name;
                $temp['owner'] = $owner;
                $temp['coverurl'] = $coverurl;
                array_push($featuredPlaylist, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_playlist_temps = array();
            $feat_playlist_temps['heading'] = "Mwonya Playlists Recommended Just For You.";
            $feat_playlist_temps['featuredPlaylists'] = $featuredPlaylist;
            array_push($menuCategory, $feat_playlist_temps);
            ///end featuredPlaylist
            ///
            ///
            //get Featured Artist
            $featuredCategory = array();
            $musicartistQuery = "SELECT a.id,a.profilephoto,a.name FROM artists a JOIN artistfollowing af ON a.id = af.artistid WHERE status = 1 AND af.userid = '$libraryUserID' ORDER BY af.datefollowed DESC LIMIT 10";
            // Set up the prepared statement
            $stmt = mysqli_prepare($this->conn, $musicartistQuery);
            // Execute the query
            mysqli_stmt_execute($stmt);
            // Bind the result variables
            mysqli_stmt_bind_result($stmt, $id, $profilephoto, $name);

            // Fetch the results
            while (mysqli_stmt_fetch($stmt)) {
                $temp = array();
                $temp['id'] = $id;
                $temp['profilephoto'] = $profilephoto;
                $temp['name'] = $name;
                array_push($featuredCategory, $temp);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);

            $feat_Cat_temps = array();
            $feat_Cat_temps['heading'] = "Artists Followed by You.";
            $feat_Cat_temps['featuredArtists'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end featuredArtist
            ///


        }


        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["Library"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_pages;

        return $itemRecords;
    }

    function LiveShows(): array
    {

        // Set up the prepared statement to retrieve the number of genres
        $tag_music = "live";
        $genre_count_stmt = mysqli_prepare($this->conn, "SELECT COUNT(DISTINCT id) as total_live_shows FROM songs WHERE tag != 'ad' AND tag = ?");

        mysqli_stmt_bind_param($genre_count_stmt, "s", $tag_music);

        mysqli_stmt_execute($genre_count_stmt);

        mysqli_stmt_bind_result($genre_count_stmt, $total_live_shows);

        mysqli_stmt_fetch($genre_count_stmt);

        mysqli_stmt_close($genre_count_stmt);

        // Calculate the total number of pages
        $no_of_records_per_page = 15;
        $total_pages = ceil($total_live_shows / $no_of_records_per_page);

        // Retrieve the "page" parameter from the GET request
        $page = isset($_GET['page']) ? intval(htmlspecialchars(strip_tags($_GET["page"]))) : 1;

        // Validate the "page" parameter
        if ($page < 1 || $page > $total_pages) {
            $page = 1;
        }

        // Calculate the offset
        $offset = ($page - 1) * $no_of_records_per_page;


        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {

            //get live
            $song_ids = array();
            $home_genre_tracks = array();
            $genre_song_stmt = "SELECT id FROM songs  WHERE  tag != 'ad' AND tag = 'live' ORDER BY `songs`.`plays` DESC LIMIT 4";
            $genre_song_stmt_result = mysqli_query($this->conn, $genre_song_stmt);

            while ($row = mysqli_fetch_array($genre_song_stmt_result)) {
                array_push($song_ids, $row['id']);
            }

            foreach ($song_ids as $row) {
                $song = new Song($this->conn, $row);
                $temp = array();
                $temp['id'] = $song->getId();
                $temp['title'] = $song->getTitle();
                $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                $temp['artistID'] = $song->getArtistId();
                $temp['album'] = $song->getAlbum()->getTitle();
                $temp['description'] = $song->getAlbum()->getDescription();
                $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                $temp['genre'] = $song->getGenre()->getGenre();
                $temp['genreID'] = $song->getGenre()->getGenreid();
                $temp['duration'] = $song->getDuration();
                $temp['cover'] = $song->getCover();
                $temp['path'] = $song->getPath();
                $temp['totalplays'] = $song->getPlays();
                $temp['weeklyplays'] = $song->getWeeklyplays();
                $temp['tag'] = $song->getTag();


                array_push($home_genre_tracks, $temp);
            }

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "Listen Live Now";
            $feat_albums_temps['description'] = "Never miss a moment of the live audio action with Mwonya. Whether you're a music/Radio fan, talk show enthusiast, or simply looking for something new, you can now stream live audio events in real-time.";
            $feat_albums_temps['coverImage'] = "https://restream.io/blog/content/images/2020/10/broadcast-interviews-and-qas-online-tw-fb.png";
            $feat_albums_temps['liveshows'] = $home_genre_tracks;
            array_push($menuCategory, $feat_albums_temps);


        }

        // Use a prepared statement and a JOIN clause to get genre and song data in a single query
        $stmt = $this->conn->prepare("SELECT id FROM songs  WHERE  tag != 'ad' AND tag = 'live' ORDER BY title ASC  LIMIT ?, ?");

        $stmt->bind_param("ii", $offset, $no_of_records_per_page);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $song = new Song($this->conn, $row['id']);
            $temp = array();
            $temp['id'] = $song->getId();
            $temp['title'] = $song->getTitle();
            $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['description'] = $song->getAlbum()->getDescription();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
            $temp['cover'] = $song->getCover();
            $temp['path'] = $song->getPath();
            $temp['totalplays'] = $song->getPlays();
            $temp['weeklyplays'] = $song->getWeeklyplays();
            $temp['tag'] = $song->getTag();

            array_push($menuCategory, $temp);
        }

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["livepage"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_live_shows;

        return $itemRecords;
    }

    function readUserLikedSongs(): array
    {
        $itemRecords = array();

        $userID = htmlspecialchars(strip_tags($_GET["userID"]));
        $this->pageNO = htmlspecialchars(strip_tags($_GET["page"]));

        if ($userID) {
            $this->pageNO = floatval($this->pageNO);
            $no_of_records_per_page = 200;
            $offset = ($this->pageNO - 1) * $no_of_records_per_page;
            $likedSong = new LikedSong($this->conn, $userID);

            $total_rows = $likedSong->getNumberOfSongs();
            $total_pages = ceil($total_rows / $no_of_records_per_page);

            $itemRecords["page"] = $this->pageNO;
            $itemRecords["UserLikedTracks"] = array();
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;

            $user = new User($this->conn, $userID);

            if ($this->pageNO == 1) {

                if ($user) {
                    $temp = array();
                    $temp['title'] = "Your Favourites";
                    $temp['subtitle'] = "Featuring all tracks liked by you.";
                    $temp['userid'] = $user->getId();
                    $temp['user_name'] = $user->getFirstname();
                    $temp['user_profile'] = $user->getProfilePic();
                    array_push($itemRecords["UserLikedTracks"], $temp);

                }

            }

            // get products id from the same cat
            $likedSong_IDs = $likedSong->getLikedSongIds($offset, $no_of_records_per_page);
            $allProducts = array();

            foreach ($likedSong_IDs as $song) {
                $songLiked = new Song($this->conn, $song);
                if ($songLiked->getId() != null) {
                    $temp = array();
                    $temp['id'] = $songLiked->getId();
                    $temp['title'] = $songLiked->getTitle();
                    $temp['artist'] = $songLiked->getArtist()->getName();
                    $temp['artistID'] = $songLiked->getArtistId();
                    $temp['album'] = $songLiked->getAlbum()->getTitle();
                    $temp['artworkPath'] = $songLiked->getAlbum()->getArtworkPath();
                    $temp['genre'] = $songLiked->getGenre()->getGenre();
                    $temp['genreID'] = $songLiked->getGenre()->getGenreid();
                    $temp['duration'] = $songLiked->getDuration();
                    $temp['lyrics'] = $songLiked->getLyrics();
                    $temp['path'] = $songLiked->getPath();
                    $temp['totalplays'] = $songLiked->getPlays();
                    $temp['weeklyplays'] = $songLiked->getWeeklyplays();
                    array_push($allProducts, $temp);
                }

            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['UserLikedTracks'], $slider_temps);


        }

        return $itemRecords;
    }


    //get selected Album details and similar product
    function readSelectedAlbum(): array
    {

        $itemRecords = array();

        $this->albumID = htmlspecialchars(strip_tags($_GET["albumID"]));
        $this->pageNO = htmlspecialchars(strip_tags($_GET["page"]));

        if ($this->albumID) {
            $this->pageNO = floatval($this->pageNO);
            $no_of_records_per_page = 20;
            $offset = ($this->pageNO - 1) * $no_of_records_per_page;

            $sql = "SELECT COUNT(*) as count FROM songs WHERE album = '" . $this->albumID . "'  limit 1";
            $result = mysqli_query($this->conn, $sql);
            $data = mysqli_fetch_assoc($result);
            $total_rows = floatval($data['count']);
            $total_pages = ceil($total_rows / $no_of_records_per_page);

            $itemRecords["page"] = $this->pageNO;
            $itemRecords["Album"] = array();
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
            $album = new Album($this->conn, $this->albumID);


            if ($this->pageNO == 1) {

                if ($album) {
                    $temp = array();
                    $temp['id'] = $album->getId();
                    $temp['title'] = $album->getTitle();
                    $temp['artistName'] = $album->getArtist()->getName();
                    $temp['artistID'] = $album->getArtistId();
                    $temp['genreID'] = $album->getGenre()->getGenreid();
                    $temp['genreName'] = $album->getGenre()->getGenre();
                    $temp['tracks_count'] = $album->getNumberOfSongs();
                    $temp['artworkPath'] = $album->getArtworkPath();
                    $temp['description'] = $album->getDescription();
                    $temp['datecreated'] = $album->getDatecreated();
                    $temp['totaltrackplays'] = $album->getTotaltrackplays();
                    $temp['tag'] = $album->getTag();
                    $temp['trackPath'] = $album->getSongPaths();

                    array_push($itemRecords["Album"], $temp);

                }

            }


            // get products id from the same cat
            $same_cat_IDs = $album->getSongIds($offset, $no_of_records_per_page);
            $allProducts = array();

            foreach ($same_cat_IDs as $row) {
                $song = new Song($this->conn, $row);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['Album'], $slider_temps);


        }


        return $itemRecords;
    }

    public function searchHomePage(): array
    {


        $menuCategory = array();
        $itemRecords = array();


        // get_Slider_banner
        $slider_id = array();
        $sliders = array();

        $slider_query = "SELECT id FROM search_slider WHERE status=1 ORDER BY date_created DESC LIMIT 8";
        $slider_query_id_result = mysqli_query($this->conn, $slider_query);
        while ($row = mysqli_fetch_array($slider_query_id_result)) {
            array_push($slider_id, $row['id']);
        }


        foreach ($slider_id as $row) {
            $temp = array();
            $slider = new SearchSlider($this->conn, $row);
            $temp['id'] = $slider->getId();
            $temp['playlistID'] = $slider->getPlaylistID();
            $temp['imagepath'] = $slider->getImagepath();
            array_push($sliders, $temp);
        }

        $slider_temps = array();
        $slider_temps['heading'] = "Discover";
        $slider_temps['search_sliders'] = $sliders;
        array_push($menuCategory, $slider_temps);
        // end get_Slider_banner


        //  popular search Begin
        $bestSellingProducts = array();
        $top_artist = "SELECT artists.name, SUM(frequency.plays) as total_plays, artists.datecreated,artists.id FROM frequency INNER JOIN songs ON frequency.songid = songs.id INNER JOIN artists ON songs.artist = artists.id GROUP BY artists.name ORDER BY total_plays DESC LIMIT 40";
        $stmt = mysqli_prepare($this->conn, $top_artist);

        // Execute the query
        mysqli_stmt_execute($stmt);

        // Bind the result variables
        mysqli_stmt_bind_result($stmt, $name, $total_plays, $datecreated, $id);

        // Fetch the results
        while (mysqli_stmt_fetch($stmt)) {
            $temp = array();
            $temp['id'] = $id;
            $temp['query'] = $name;
            $temp['count'] = $total_plays;
            $temp['created_at'] = $datecreated;
            $temp['updated_at'] = $datecreated;
            array_push($bestSellingProducts, $temp);
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);


        $slider_temps = array();
        $slider_temps['heading'] = "Popular on Mwonya";
        $slider_temps['popularSearch'] = $bestSellingProducts;
        array_push($menuCategory, $slider_temps);

        // end popular search  Fetch


        //fetch other categories Begin
        $Search_genreIDs = array();
        $SearchGenreBody = array();
        $genre_stmt = "SELECT DISTINCT(genre) FROM songs  WHERE tag != 'ad' ORDER BY `songs`.`plays` DESC LIMIT 10";
        $genre_stmt_result = mysqli_query($this->conn, $genre_stmt);

        while ($row = mysqli_fetch_array($genre_stmt_result)) {

            array_push($Search_genreIDs, $row['genre']);
        }

        foreach ($Search_genreIDs as $row) {
            $genre = new Genre($this->conn, $row);
            $temp = array();
            $temp['id'] = $genre->getGenreid();
            $temp['name'] = $genre->getGenre();
            $temp['tag'] = $genre->getTag();
            $temp['cover_image'] = $genre->getGenreTopPic();
            array_push($SearchGenreBody, $temp);
        }

        $genreCategory = array();
        $genreCategory['heading'] = "Browse";
        $genreCategory['genreCategories'] = $SearchGenreBody;
        array_push($menuCategory, $genreCategory);

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = 1;
        $itemRecords["searchMain"] = $menuCategory;
        $itemRecords["total_pages"] = 1;
        $itemRecords["total_results"] = 1;

        return $itemRecords;
    }


    public function Notifications(): array
    {
        $page = (isset($_GET['page']) && $_GET['page']) ? htmlspecialchars(strip_tags($_GET["page"])) : '1';

        $noticeString = "
        (SELECT id,title,artist,path,plays,weekplays,'artworkPath', 'song' as type,tag,dateAdded,lyrics FROM songs WHERE dateAdded > DATE_SUB(NOW(), INTERVAL 7 DAY) ) UNION (SELECT id,name,'artist','path','plays','weekplays',profilephoto, 'artist' as type,tag,datecreated,'lyrics' FROM artists WHERE datecreated > DATE_SUB(NOW(), INTERVAL 7 DAY)) UNION (SELECT id,title,artist,'path','plays','weekplays',artworkPath, 'album' as type,tag,datecreated,'lyrics' FROM albums WHERE datecreated > DATE_SUB(NOW(), INTERVAL 7 DAY)) UNION (SELECT id,name,ownerID,'path','plays','weekplays',coverurl, 'playlist' as type,'tag',dateCreated,'lyrics' FROM playlists WHERE dateCreated > DATE_SUB(NOW(), INTERVAL 7 DAY)) ORDER BY `dateAdded` DESC
        ";

        // run the query in the db and search through each of the records returned
        $query = mysqli_query($this->conn, $noticeString);
        $result_count = mysqli_num_rows($query);
        $page = floatval($page);
        $no_of_records_per_page = 30;
        $offset = ($page - 1) * $no_of_records_per_page;
        $total_rows = floatval(number_format($result_count));
        $total_pages = ceil($total_rows / $no_of_records_per_page);

        $itemRecords = array();


        // check if the search query returned any results
        if ($result_count > 0) {

            $notice_result = array();
            $menuCategory = array();


            $category_stmt = $noticeString . " LIMIT " . $offset . "," . $no_of_records_per_page . "";

            $menu_type_id_result = mysqli_query($this->conn, $category_stmt);

            while ($row = mysqli_fetch_array($menu_type_id_result)) {
                array_push($notice_result, $row);
            }

            foreach ($notice_result as $row) {
                $temp = array();
                $name = "Track";
                if ($row['tag'] == "music") {
                    $name = "music";
                }
                if ($row['tag'] == "podcast") {
                    $name = "episode";
                }
                if ($row['tag'] == "dj") {
                    $name = "mix tape";
                }

                if ($row['type'] == "song") {
                    $temp['id'] = $row['id'];
                    $song = new Song($this->conn, $row['id']);
                    $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
//                    $temp['description'] = $song->getArtist()->getName() .$song->getFeaturing() . " added a new " . $name . " '" . $row['title'] . "'. give it a listen!";
                    $temp['description'] = "New " . $name . " alert! '" . $row['title'] . "' by " . $song->getArtist()->getName() .$song->getFeaturing() . " is now playing on Mwonya. Tap to listen!";
                    $temp['type'] = $row['type'];
                    $temp['tag'] = $row['tag'];
                    $temp['lyrics'] = $row['lyrics'];
                }
                if ($row['type'] == "album") {
                    $temp['id'] = $row['id'];
                    $album = new Album($this->conn, $row['id']);
                    $temp['artist'] = $album->getArtist()->getName();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['description'] = "New release alert! Discover the latest " . $row['tag'] . " from " . $album->getArtist()->getName() . " with their new '" . $row['title'] . "' collection now on our site";
                    $temp['type'] = $row['type'];
                    $temp['tag'] = $row['tag'];
                    $temp['lyrics'] = $row['lyrics'];


                }
                if ($row['type'] == "artist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = $row['title'] . ' has joined Mwonya!';
                    $temp['artistID'] = '';
                    $temp['title'] = '';
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['description'] = "Welcome " . $row['title'] . ", a new artist now available on Mwonya. Visit their profile to learn more and discover their content!";
                    $temp['type'] = $row['type'];
                    $temp['tag'] = $row['tag'];
                    $temp['lyrics'] = $row['lyrics'];


                }
                if ($row['type'] == "playlist") {
                    $temp['id'] = $row['id'];
                    $user = new User($this->conn, $row['artist']);
                    $temp['artist'] = $user->getFirstname();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['description'] = $user->getFirstname() . " created a new playlist '" . $row['title'] . "'. Stream it now on demand";
                    $temp['type'] = $row['type'];
                    $temp['tag'] = $row['tag'];
                    $temp['lyrics'] = $row['lyrics'];


                }

                array_push($menuCategory, $temp);
            }

            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["notice_home"] = $menuCategory;
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;


        } else {
            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["notice_home"] = [];
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
        }
        return $itemRecords;
    }


    public function UserPlaylistSelection(): array
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $userID = isset($_GET['userID']) ? htmlspecialchars(strip_tags($_GET['userID'])) : null;

        $query = "SELECT p.`id` AS playlist_id, p.name, COUNT(ps.`songId`) AS total_songs, p.coverurl 
              FROM `playlists` p 
              LEFT JOIN `playlistsongs` ps ON p.`id` = ps.`playlistId` 
              WHERE p.`ownerID` = ? 
              GROUP BY p.`id` 
              ORDER BY p.`dateCreated` DESC";

        // Prepare the statement
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $userID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $total_rows = mysqli_num_rows($result);
        $no_of_records_per_page = 20;
        $offset = ($page - 1) * $no_of_records_per_page;
        $total_pages = ceil($total_rows / $no_of_records_per_page);

        // Validate requested page
        if ($page > $total_pages && $total_pages > 0) {
            $page = $total_pages;
            $offset = ($page - 1) * $no_of_records_per_page;
        }

        $UserPlaylist_Parent = [
            'page' => $page,
            'version' => 1,
            'Playlist_Summary' => [],
            'total_pages' => $total_pages,
            'total_results' => $total_rows
        ];

        // Fetch the paginated results
        if ($total_rows > 0) {
            $user_playlist_stmt = $query . " LIMIT ?, ?";
            $stmt = mysqli_prepare($this->conn, $user_playlist_stmt);
            mysqli_stmt_bind_param($stmt, "sii", $userID, $offset, $no_of_records_per_page);
            mysqli_stmt_execute($stmt);
            $user_playlist_stmt_result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_array($user_playlist_stmt_result)) {
                $temp = [
                    'id' => $row['playlist_id'],
                    'name' => $row['name'],
                    'total_songs' => $row['total_songs'],
                    'coverurl' => $row['coverurl']
                ];
                $UserPlaylist_Parent['Playlist_Summary'][] = $temp;
            }
        }

        return $UserPlaylist_Parent;
    }


    function searchNormal(): array
    {
        $page = htmlspecialchars(strip_tags($_GET["page"]));
        $search_query = htmlspecialchars(strip_tags($_GET["key_query"]));
        $search_algorithm = "normal";
        // create the base variables for building the search query

        $page = floatval($page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;

        $itemRecords = array();

        $perform_query = true;
        // create the base variables for building the search query

        if (strlen($search_query) > 100 || strlen($search_query) < 3) {
            $perform_query = false;
        }

        if (empty($search_query)) {
            $perform_query = false;
        }

        if ($perform_query == true) {
            // echo Update Search Table;
            $sh_result = mysqli_query($this->conn, "SELECT * FROM `searches` WHERE `query`='" . $this->conn->real_escape_string($search_query) . "' LIMIT 1;");
            $sh_data = mysqli_fetch_assoc($sh_result);
            if ($sh_data != null) {
                $sh_id = floatval($sh_data['id']);
                $countQuery = mysqli_query($this->conn, "SELECT `count` FROM searches WHERE id = '$sh_id'");
                $shq_data = mysqli_fetch_assoc($countQuery);
                $shq_count = floatval($shq_data['count']);
                $shq_count += 1;
                mysqli_query($this->conn, "UPDATE `searches` SET `count`= '$shq_count' WHERE id = '$sh_id'");

            } else {
                //insert data
                mysqli_query($this->conn, "INSERT INTO `searches`(`query`, `count`) VALUES ('" . $this->conn->real_escape_string($search_query) . "',1)");
            }

        }
        $search = "%{$search_query}%";

        $search_query_top = "(SELECT id,title,artist,path,plays,weekplays,'artworkPath', 'song' as type,lyrics FROM songs WHERE title LIKE ? ) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',profilephoto, 'artist' as type,'lyrics' FROM artists  WHERE name LIKE ? ) 
           UNION
           (SELECT id,title,artist,'path','plays','weekplays',artworkPath, 'album' as type,'lyrics' FROM albums  WHERE title LIKE ? ) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',coverurl, 'playlist' as type,'lyrics' FROM playlists WHERE name LIKE ? )"; // SQL with parameters
        $stmt = $this->conn->prepare($search_query_top);
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result(); // get the mysqli result
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $total_results_got = count($data);
        $total_rows = floatval(number_format($total_results_got));
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        // check if the search query returned any results

        $menuCategory = array();


        $search_query_sql = $search_query_top . " ORDER BY `title` ASC LIMIT ?,?";
        $stmt = $this->conn->prepare($search_query_sql);
        $stmt->bind_param("ssssii", $search, $search, $search, $search, $offset, $no_of_records_per_page);
        $stmt->execute();
        $result = $stmt->get_result(); // get the mysqli result
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $total_results_got = count($data);


        if ($total_results_got > 0) {

            foreach ($data as $row) {
                $temp = array();

                if ($row['type'] == "song") {
                    $temp['id'] = $row['id'];
                    $song = new Song($this->conn, $row['id']);
                    $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];
                }
                if ($row['type'] == "album") {
                    $temp['id'] = $row['id'];
                    $album = new Album($this->conn, $row['id']);
                    $temp['artist'] = $album->getArtist()->getName();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }
                if ($row['type'] == "artist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = $row['title'];
                    $temp['artistID'] = '';
                    $temp['title'] = '';
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }
                if ($row['type'] == "playlist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = '';
                    $temp['artistID'] = '';
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }

                array_push($menuCategory, $temp);
            }

            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["searchTerm"] = $search_query;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["search_results"] = $menuCategory;


        } else {
            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["searchTerm"] = $search_query;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["search_results"] = [];
        }
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;


        return $itemRecords;
    }

    function searchAdvance(): array
    {
        $page = htmlspecialchars(strip_tags($_GET["page"]));
        $search_query = htmlspecialchars(strip_tags($_GET["key_query"]));
        $search_algorithm = "normal";
        $perform_query = true;
        // create the base variables for building the search query

        if (strlen($search_query) > 100 || strlen($search_query) < 3) {
            $perform_query = false;
        }

        if (empty($search_query)) {
            $perform_query = false;
        }

        if ($perform_query == true) {

//        echo $search_string;
            $search_string = "(SELECT id,title,artist,path,plays,weekplays,'artworkPath', 'song' as type FROM songs WHERE title LIKE'%" . $this->conn->real_escape_string($search_query) . "%' ) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',profilephoto, 'artist' as type FROM artists  WHERE name LIKE'%" . $this->conn->real_escape_string($search_query) . "%' ) 
           UNION
           (SELECT id,title,artist,'path','plays','weekplays',artworkPath, 'album' as type FROM albums  WHERE title LIKE'%" . $this->conn->real_escape_string($search_query) . "%' ) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',coverurl, 'playlist' as type FROM playlists WHERE name LIKE'%" . $this->conn->real_escape_string($search_query) . "%' )";


//        echo $search_string;


            // run the query in the db and search through each of the records returned
            $query = mysqli_query($this->conn, $search_string);
            $result_count = mysqli_num_rows($query);

            $page = floatval($page);
            $no_of_records_per_page = 10;
            $offset = ($page - 1) * $no_of_records_per_page;


            $total_rows = floatval(number_format($result_count));
            $total_pages = ceil($total_rows / $no_of_records_per_page);


            $itemRecords = array();

            // echo Update Search Table;
            $sh_result = mysqli_query($this->conn, "SELECT * FROM `searches` WHERE `query`='" . $search_query . "' LIMIT 1;");
            $sh_data = mysqli_fetch_assoc($sh_result);
            if ($sh_data != null) {
                $sh_id = floatval($sh_data['id']);
                $countQuery = mysqli_query($this->conn, "SELECT `count` FROM searches WHERE id = '$sh_id'");
                $shq_data = mysqli_fetch_assoc($countQuery);
                $shq_count = floatval($shq_data['count']);
                $shq_count += 1;
                mysqli_query($this->conn, "UPDATE `searches` SET `count`= '$shq_count' WHERE id = '$sh_id'");

            } else {
                //insert data
                mysqli_query($this->conn, "INSERT INTO `searches`(`query`, `count`) VALUES ('" . $search_query . "',1)");
            }


            // check if the search query returned any results
            if ($result_count > 0) {

                $categoryids = array();
                $menuCategory = array();


                $category_stmt = $search_string . " ORDER BY `title` ASC LIMIT " . $offset . "," . $no_of_records_per_page . "";


                $menu_type_id_result = mysqli_query($this->conn, $category_stmt);

                while ($row = mysqli_fetch_array($menu_type_id_result)) {
                    array_push($categoryids, $row);
                }

                foreach ($categoryids as $row) {
                    $temp = array();

                    if ($row['type'] == "song") {
                        $temp['id'] = $row['id'];
                        $song = new Song($this->conn, $row['id']);
                        $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                        $temp['artistID'] = $row['artist'];
                        $temp['title'] = $row['title'];
                        $temp['path'] = $row['path'];
                        $temp['plays'] = $row['plays'];
                        $temp['weekplays'] = $row['weekplays'];
                        $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                        $temp['type'] = $row['type'];
                    }
                    if ($row['type'] == "album") {
                        $temp['id'] = $row['id'];
                        $album = new Album($this->conn, $row['id']);
                        $temp['artist'] = $album->getArtist()->getName();
                        $temp['artistID'] = $row['artist'];
                        $temp['title'] = $row['title'];
                        $temp['path'] = $row['path'];
                        $temp['plays'] = $row['plays'];
                        $temp['weekplays'] = $row['weekplays'];
                        $temp['artworkPath'] = $row['artworkPath'];
                        $temp['type'] = $row['type'];
                    }
                    if ($row['type'] == "artist") {
                        $temp['id'] = $row['id'];
                        $temp['artist'] = $row['title'];
                        $temp['artistID'] = '';
                        $temp['title'] = '';
                        $temp['path'] = $row['path'];
                        $temp['plays'] = $row['plays'];
                        $temp['weekplays'] = $row['weekplays'];
                        $temp['artworkPath'] = $row['artworkPath'];
                        $temp['type'] = $row['type'];
                    }
                    if ($row['type'] == "playlist") {
                        $temp['id'] = $row['id'];
                        $temp['artist'] = '';
                        $temp['artistID'] = '';
                        $temp['title'] = $row['title'];
                        $temp['path'] = $row['path'];
                        $temp['plays'] = $row['plays'];
                        $temp['weekplays'] = $row['weekplays'];
                        $temp['artworkPath'] = $row['artworkPath'];
                        $temp['type'] = $row['type'];
                    }

                    array_push($menuCategory, $temp);
                }

                $itemRecords["page"] = $page;
                $itemRecords["version"] = 1;
                $itemRecords["searchTerm"] = $search_query;
                $itemRecords["algorithm"] = $search_algorithm;
                $itemRecords["search_results"] = $menuCategory;
                $itemRecords["total_pages"] = $total_pages;
                $itemRecords["total_results"] = $total_rows;


            } else {
                $itemRecords["page"] = $page;
                $itemRecords["version"] = 1;
                $itemRecords["searchTerm"] = $search_query;
                $itemRecords["algorithm"] = $search_algorithm;
                $itemRecords["search_results"] = [];
                $itemRecords["total_pages"] = $total_pages;
                $itemRecords["total_results"] = $total_rows;
            }
        } else {
            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["searchTerm"] = "Unknown";
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["search_results"] = [];
            $itemRecords["total_pages"] = null;
            $itemRecords["total_results"] = null;
        }

        return $itemRecords;
    }


    function searchFullText()
    {
        $page = htmlspecialchars(strip_tags($_GET["page"]));
        $search_query = htmlspecialchars(strip_tags($_GET["key_query"]));
        $search_algorithm = "fulltext";
        // create the base variables for building the search query

//        SELECT id,name,'path',profilephoto, 'artist' as type FROM artists  WHERE MATCH(name) AGAINST('oil phone' IN NATURAL LANGUAGE MODE)

//        echo $search_string;
        $search_string = "
            (SELECT id,title,artist,path,plays,weekplays,'artworkPath', 'song' as type,lyrics FROM songs WHERE MATCH (title) AGAINST('" . $this->conn->real_escape_string($search_query) . "' IN NATURAL LANGUAGE MODE) ) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',profilephoto, 'artist' as type,'lyrics' FROM artists  WHERE MATCH (name) AGAINST('" . $this->conn->real_escape_string($search_query) . "' IN NATURAL LANGUAGE MODE) ) 
           UNION
           (SELECT id,title,artist,'path','plays','weekplays',artworkPath, 'album' as type,'lyrics' FROM albums  WHERE  MATCH (title) AGAINST('" . $this->conn->real_escape_string($search_query) . "' IN NATURAL LANGUAGE MODE)) 
           UNION
           (SELECT id,name,'artist','path','plays','weekplays',coverurl, 'playlist' as type,'lyrics' FROM playlists WHERE  MATCH (name) AGAINST('" . $this->conn->real_escape_string($search_query) . "' IN NATURAL LANGUAGE MODE))";


//        echo $search_string;


        // run the query in the db and search through each of the records returned
        $query = mysqli_query($this->conn, $search_string);
        $result_count = mysqli_num_rows($query);

        $page = floatval($page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;


        $total_rows = floatval(number_format($result_count));
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $itemRecords = array();


        // check if the search query returned any results
        if ($result_count > 0) {

            $categoryids = array();
            $menuCategory = array();


            $category_stmt = $search_string . " ORDER BY `title` ASC LIMIT " . $offset . "," . $no_of_records_per_page . "";

//            echo $category_stmt;


            $menu_type_id_result = mysqli_query($this->conn, $category_stmt);

            while ($row = mysqli_fetch_array($menu_type_id_result)) {
                array_push($categoryids, $row);
            }

            foreach ($categoryids as $row) {
                $temp = array();

                if ($row['type'] == "song") {
                    $temp['id'] = $row['id'];
                    $song = new Song($this->conn, $row['id']);
                    $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }
                if ($row['type'] == "album") {
                    $temp['id'] = $row['id'];
                    $album = new Album($this->conn, $row['id']);
                    $temp['artist'] = $album->getArtist()->getName();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }
                if ($row['type'] == "artist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = $row['title'];
                    $temp['artistID'] = '';
                    $temp['title'] = '';
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }
                if ($row['type'] == "playlist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = '';
                    $temp['artistID'] = '';
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
                    $temp['plays'] = $row['plays'];
                    $temp['weekplays'] = $row['weekplays'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                    $temp['lyrics'] = $row['lyrics'];

                }

                array_push($menuCategory, $temp);
            }

            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["searchTerm"] = $search_query;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["search_results"] = $menuCategory;
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;


        } else {
            $itemRecords["page"] = $page;
            $itemRecords["version"] = 1;
            $itemRecords["searchTerm"] = $search_query;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["search_results"] = [];
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
        }
        return $itemRecords;
    }


    function readSelectedGenre(): array
    {

        $genreID = htmlspecialchars(strip_tags($_GET["genreID"]));
        $this->pageNO = htmlspecialchars(strip_tags($_GET["page"]));

        $menuCategory = array();
        $itemRecords = array();


        // get_Slider_banner
        $slider_id = array();
        $sliders = array();


        $slider_query = "SELECT id FROM search_slider WHERE status=1 ORDER BY date_created DESC LIMIT 8";
        $slider_query_id_result = mysqli_query($this->conn, $slider_query);
        while ($row = mysqli_fetch_array($slider_query_id_result)) {
            array_push($slider_id, $row['id']);
        }


        foreach ($slider_id as $row) {
            $temp = array();
            $slider = new SearchSlider($this->conn, $row);
            $temp['id'] = $slider->getId();
            $temp['playlistID'] = $slider->getPlaylistID();
            $temp['imagepath'] = $slider->getImagepath();
            array_push($sliders, $temp);
        }

        $slider_temps = array();
        $slider_temps['heading'] = "Discover";
        $slider_temps['search_sliders'] = $sliders;
        array_push($menuCategory, $slider_temps);
        // end get_Slider_banner


        // genre songs id
        $genre = new Genre($this->conn, $genreID);
        $temp = array();
        $temp['id'] = $genre->getGenreid();
        $temp['name'] = $genre->getGenre();
        $temp['tag'] = $genre->getTag();
        $temp['Tracks'] = $genre->getGenre_Songs(36);
        array_push($menuCategory, $temp);

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = 1;
        $itemRecords["genreMain"] = $menuCategory;
        $itemRecords["total_pages"] = 1;
        $itemRecords["total_results"] = 1;
        return $itemRecords;
    }

    function readSelectedPlaylist(): array
    {

        $itemRecords = array();

        $playlistID = htmlspecialchars(strip_tags($_GET["playlistID"]));
        $page = htmlspecialchars(strip_tags($_GET["page"]));

        if ($playlistID) {
            $page = floatval($page);
            $no_of_records_per_page = 50;
            $offset = ($this->pageNO - 1) * $no_of_records_per_page;

            $sql = "SELECT COUNT(id) as count FROM playlistsongs WHERE playlistId = '" . $playlistID . "'  limit 1";
            $result = mysqli_query($this->conn, $sql);
            $data = mysqli_fetch_assoc($result);
            $total_rows = floatval($data['count']);
            $total_pages = ceil($total_rows / $no_of_records_per_page);

            $itemRecords["page"] = $page;
            $itemRecords["Playlists"] = array();
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
            $playlist = new Playlist($this->conn, $playlistID);


            if ($page == 1) {

                if ($playlist) {
                    $temp = array();
                    $temp['id'] = $playlist->getId();
                    $temp['name'] = $playlist->getName();
                    $temp['owner'] = $playlist->getOwner();
                    $temp['cover'] = $playlist->getCoverurl();
                    $temp['description'] = $playlist->getDescription();
                    $temp['status'] = $playlist->getStatus();
                    $temp['total'] = $total_rows;
                    array_push($itemRecords["Playlists"], $temp);

                }

            }


            // get products id from the same cat
            $same_cat_IDs = $playlist->getSongIds($offset, $no_of_records_per_page);
            $allProducts = array();

            foreach ($same_cat_IDs as $row) {
                $song = new Song($this->conn, $row);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['Playlists'], $slider_temps);


        }


        return $itemRecords;
    }


    function readSelectedArtistPick(): array
    {

        $itemRecords = array();

        $playlistID = htmlspecialchars(strip_tags($_GET["playlistID"]));
        $page = htmlspecialchars(strip_tags($_GET["page"]));

        if ($playlistID) {
            $page = floatval($page);
            $no_of_records_per_page = 50;
            $offset = ($this->pageNO - 1) * $no_of_records_per_page;

            $sql = "SELECT COUNT(id) as count FROM artistpicksongs WHERE artistPickID = '" . $playlistID . "'  limit 1";
            $result = mysqli_query($this->conn, $sql);
            $data = mysqli_fetch_assoc($result);
            $total_rows = floatval($data['count']);
            $total_pages = ceil($total_rows / $no_of_records_per_page);

            $itemRecords["page"] = $page;
            $itemRecords["Playlists"] = array();
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
            $playlist = new ArtistPick($this->conn, $playlistID);


            if ($page == 1) {

                if ($playlist) {
                    $temp = array();
                    $temp['id'] = $playlist->getId();
                    $temp['name'] = $playlist->getTitle();
                    $temp['owner'] = $playlist->getArtist()->getName();
                    $temp['cover'] = $playlist->getCoverArt();
                    $temp['description'] = "Collection of Tracks Handpicked by the artist";
                    $temp['status'] = "2";
                    $temp['total'] = $total_rows;
                    array_push($itemRecords["Playlists"], $temp);

                }

            }


            // get products id from the same cat
            $same_cat_IDs = $playlist->getSongIds($offset, $no_of_records_per_page);
            $allProducts = array();

            foreach ($same_cat_IDs as $row) {
                $song = new Song($this->conn, $row);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['Playlists'], $slider_temps);


        }


        return $itemRecords;
    }

    function readSong(): array
    {

        $itemRecords = array();

        $songID = htmlspecialchars(strip_tags($_GET["songID"]));
        $page = htmlspecialchars(strip_tags($_GET["page"]));

        if ($songID) {
            $page = floatval($page);

            $itemRecords["page"] = $page;
            $itemRecords["Song"] = array();

            // Song
            $song = new Song($this->conn, $songID);
            $temp = array();
            $temp['id'] = $song->getId();
            $temp['title'] = $song->getTitle();
            $temp['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['albumID'] = $song->getAlbumId();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
            $temp['lyrics'] = $song->getLyrics();
            $temp['path'] = $song->getPath();
            $temp['totalplays'] = $song->getPlays();
            $temp['weeklyplays'] = $song->getWeeklyplays();

            array_push($itemRecords['Song'], $temp);


            // get products id from the same cat
            $related_song_ids = $song->getRelatedSongs();
            $all_Related_Songs = array();

            foreach ($related_song_ids as $row) {
                $song = new Song($this->conn, $row);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($all_Related_Songs, $temp);
            }

            $slider_temps = array();
            $slider_temps['Related Songs'] = "Recommended";
            $slider_temps['Tracks'] = $all_Related_Songs;
            array_push($itemRecords['Song'], $slider_temps);


            $itemRecords["total_pages"] = 1;
            $itemRecords["total_results"] = 1;


        }
        return $itemRecords;
    }

    function singleTrack(): array
    {

        $trackInfo = array();


        $songID = htmlspecialchars(strip_tags($_GET["trackID"]));

        if ($songID) {

            // Song
            $song = new Song($this->conn, $songID);
            $trackInfo['id'] = $song->getId();
            $trackInfo['title'] = $song->getTitle();
            $trackInfo['artist'] = $song->getArtist()->getName() .$song->getFeaturing();
            $trackInfo['artistID'] = $song->getArtistId();
            $trackInfo['album'] = $song->getAlbum()->getTitle();
            $trackInfo['albumID'] = $song->getAlbumId();
            $trackInfo['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $trackInfo['genre'] = $song->getGenre()->getGenre();
            $trackInfo['genreID'] = $song->getGenre()->getGenreid();
            $trackInfo['duration'] = $song->getDuration();
            $trackInfo['lyrics'] = $song->getLyrics();
            $trackInfo['path'] = $song->getPath();
            $trackInfo['totalplays'] = $song->getPlays();
            $trackInfo['weeklyplays'] = $song->getWeeklyplays();

        }
        return $trackInfo;
    }

    function podcastHome(): array
    {

        $home_page = (isset($_GET['page']) && $_GET['page']) ? htmlspecialchars(strip_tags($_GET["page"])) : '1';

        $page = floatval($home_page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;

        $sql = "SELECT COUNT(id) as count FROM albums WHERE tag = 'podcast' ORDER BY totalsongplays DESC LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $category_ids = array();
        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {

            // get_Slider_banner
            $song_ids = array();
            $home_genre_tracks = array();
            $genre_song_stmt = "SELECT id FROM songs  WHERE tag = 'podcast' ORDER BY `songs`.`plays` DESC LIMIT 8";
            $genre_song_stmt_result = mysqli_query($this->conn, $genre_song_stmt);

            while ($row = mysqli_fetch_array($genre_song_stmt_result)) {

                array_push($song_ids, $row['id']);
            }

            foreach ($song_ids as $row) {
                $song = new Song($this->conn, $row);
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
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($home_genre_tracks, $temp);
            }


            $podcast_temps = array();
            $podcast_temps['heading'] = "Podcast";
            $podcast_temps['subheading'] = "Exclusive podcasts and shows by creatives that make and celebrates Uganda's achievement in freedom of speech and expression";
            $podcast_temps['image_pick'] = "https://www.mwonyaa.com/assets/images/createdplaylist/podcastpodcast_mw603382d49906aPka_playlist_20221031091020_04294.jpg";
            $podcast_temps['tracks'] = $home_genre_tracks;
            array_push($menuCategory, $podcast_temps);
            // end get_Slider_banner


            //get Trending Artist

            $featuredartists = array();
            $featuredCategory = array();

            $musicartistQuery = "SELECT id, profilephoto, name FROM artists WHERE tag='podcast' ORDER BY overalplays DESC LIMIT 8";
            $feat_cat_id_result = mysqli_query($this->conn, $musicartistQuery);
            while ($row = mysqli_fetch_array($feat_cat_id_result)) {
                array_push($featuredartists, $row);
            }


            foreach ($featuredartists as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['profilephoto'] = $row['profilephoto'];
                $temp['name'] = $row['name'];
                array_push($featuredCategory, $temp);
            }

            $feat_Cat_temps = array();
            $feat_Cat_temps['heading'] = "Podcasters";
            $feat_Cat_temps['featuredArtists'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end featuredArtist

            // get_Slider_banner
            $slider_id = array();
            $sliders = array();


            $slider_query = "SELECT id FROM search_slider WHERE status=1 ORDER BY date_created DESC LIMIT 8";
            $slider_query_id_result = mysqli_query($this->conn, $slider_query);
            while ($row = mysqli_fetch_array($slider_query_id_result)) {
                array_push($slider_id, $row['id']);
            }


            foreach ($slider_id as $row) {
                $temp = array();
                $slider = new SearchSlider($this->conn, $row);
                $temp['id'] = $slider->getId();
                $temp['playlistID'] = $slider->getPlaylistID();
                $temp['imagepath'] = $slider->getImagepath();
                array_push($sliders, $temp);
            }

            $slider_temps = array();
            $slider_temps['heading'] = "Discover Exclusive Shows on Mwonyaa";
            $slider_temps['podcast_sliders'] = $sliders;
            array_push($menuCategory, $slider_temps);
            // end get_Slider_banner


        }


        //get featured Album
        $featured_albums = array();
        $featuredAlbums = array();

        $featured_album_Query = "SELECT id FROM albums WHERE tag = 'podcast' ORDER BY totalsongplays DESC LIMIT " . $offset . "," . $no_of_records_per_page . "";

        $featured_album_Query_result = mysqli_query($this->conn, $featured_album_Query);
        while ($row = mysqli_fetch_array($featured_album_Query_result)) {
            array_push($featured_albums, $row['id']);
        }

        foreach ($featured_albums as $row) {
            $pod = new Album($this->conn, $row);
            $temp = array();
            $temp['id'] = $pod->getId();
            $temp['title'] = $pod->getTitle();
            $temp['description'] = $pod->getDescription();
            $temp['artworkPath'] = $pod->getArtworkPath();
            $temp['artist'] = $pod->getArtist()->getName();
            $temp['artistImage'] = $pod->getArtist()->getProfilePath();
            $temp['genre'] = $pod->getGenre()->getGenre();
            $temp['tag'] = $pod->getTag();
//            array_push($featuredAlbums, $temp);
            array_push($menuCategory, $temp);
        }

//        $feat_albums_temps = array();
//        $feat_albums_temps['heading'] = "Featured Podcasts";
//        $feat_albums_temps['featuredPodcasts'] = $featuredAlbums;
//        array_push($menuCategory, $feat_albums_temps);

        ///end featuredAlbums


        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["podcastHome"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;

        return $itemRecords;
    }


    function EventsHome(): array
    {

        $event_page = (isset($_GET['page']) && $_GET['page']) ? htmlspecialchars(strip_tags($_GET["page"])) : '1';

        $page = floatval($event_page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;
        $date_now = date('Y-m-d');


        $sql = "SELECT COUNT(id) as count FROM events WHERE (endDate >= '$date_now') AND featured = '1' LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $category_ids = array();
        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {

            $event_ids = array();
            $today_s_event = array();
            $today_s_event_stmt = "SELECT id FROM events  WHERE (endDate >= '$date_now') AND featured = 1  ORDER BY `events`.`ranking` DESC LIMIT 8";
            $today_s_event_stmt_result = mysqli_query($this->conn, $today_s_event_stmt);

            while ($row = mysqli_fetch_array($today_s_event_stmt_result)) {

                array_push($event_ids, $row['id']);
            }

            foreach ($event_ids as $row) {
                $event = new Events($this->conn, $row);
                $temp = array();
                $temp['id'] = $event->getId();
                $temp['title'] = $event->getTitle();
                $temp['description'] = $event->getDescription();
                $temp['startDate'] = $event->getStartDate();
                $temp['startTime'] = $event->getStartTime();
                $temp['endDate'] = $event->getEndDate();
                $temp['endtime'] = $event->getEndtime();
                $temp['location'] = $event->getLocation();
                $temp['host_name'] = $event->getHostName();
                $temp['host_contact'] = $event->getHostContact();
                $temp['image'] = $event->getImage();
                $temp['ranking'] = $event->getRanking();
                $temp['featured'] = $event->getFeatured();
                $temp['date_created'] = $event->getDateCreated();
                array_push($today_s_event, $temp);
            }


            $podcast_temps = array();
            $podcast_temps['heading'] = "Events";
            $podcast_temps['subheading'] = "This is where you Happen! find out more and contact the hosts directly";
            $podcast_temps['TodayEvents'] = $today_s_event;
            array_push($menuCategory, $podcast_temps);
            // end get_Slider_banner


            // get_Slider_banner
            $slider_id = array();
            $sliders = array();


            $slider_query = "SELECT id FROM search_slider WHERE status=1 ORDER BY date_created DESC LIMIT 8";
            $slider_query_id_result = mysqli_query($this->conn, $slider_query);
            while ($row = mysqli_fetch_array($slider_query_id_result)) {
                array_push($slider_id, $row['id']);
            }


            foreach ($slider_id as $row) {
                $temp = array();
                $slider = new SearchSlider($this->conn, $row);
                $temp['id'] = $slider->getId();
                $temp['playlistID'] = $slider->getPlaylistID();
                $temp['imagepath'] = $slider->getImagepath();
                array_push($sliders, $temp);
            }

            $slider_temps = array();
            $slider_temps['heading'] = "Discover Exclusive Shows on Mwonyaa";
            $slider_temps['podcast_sliders'] = $sliders;
            array_push($menuCategory, $slider_temps);
            // end get_Slider_banner


        }


        //get featured Album
        $other_events = array();

        $other_events_Query = "SELECT id FROM events  WHERE (endDate >= '$date_now') AND featured = 1 ORDER BY `events`.`ranking` DESC LIMIT " . $offset . "," . $no_of_records_per_page . "";

        $other_events_Query_result = mysqli_query($this->conn, $other_events_Query);
        while ($row = mysqli_fetch_array($other_events_Query_result)) {
            array_push($other_events, $row['id']);
        }

        foreach ($other_events as $row) {
            $event = new Events($this->conn, $row);
            $temp = array();
            $temp['id'] = $event->getId();
            $temp['title'] = $event->getTitle();
            $temp['description'] = $event->getDescription();
            $temp['startDate'] = $event->getStartDate();
            $temp['startTime'] = $event->getStartTime();
            $temp['endDate'] = $event->getEndDate();
            $temp['endtime'] = $event->getEndtime();
            $temp['location'] = $event->getLocation();
            $temp['host_name'] = $event->getHostName();
            $temp['host_contact'] = $event->getHostContact();
            $temp['image'] = $event->getImage();
            $temp['ranking'] = $event->getRanking();
            $temp['featured'] = $event->getFeatured();
            $temp['date_created'] = $event->getDateCreated();
            array_push($menuCategory, $temp);
        }


        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["EventsHome"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;

        return $itemRecords;
    }


    function SelectedEvents(): array
    {

        $event_page = (isset($_GET['page']) && $_GET['page']) ? htmlspecialchars(strip_tags($_GET["page"])) : '1';
        $event_id = (isset($_GET['eventID']) && $_GET['eventID']) ? htmlspecialchars(strip_tags($_GET["eventID"])) : '1';

        $page = floatval($event_page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;
        $date_now = date('Y-m-d');

        $sql = "SELECT COUNT(id) as count FROM events WHERE id != $event_id AND (endDate >= '$date_now') AND featured = '1' LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {
            $event = new Events($this->conn, $event_id);
            $temp = array();
            $temp['id'] = $event->getId();
            $temp['title'] = $event->getTitle();
            $temp['description'] = $event->getDescription();
            $temp['startDate'] = $event->getStartDate();
            $temp['startTime'] = $event->getStartTime();
            $temp['endDate'] = $event->getEndDate();
            $temp['endtime'] = $event->getEndtime();
            $temp['location'] = $event->getLocation();
            $temp['host_name'] = $event->getHostName();
            $temp['host_contact'] = $event->getHostContact();
            $temp['image'] = $event->getImage();
            $temp['ranking'] = $event->getRanking();
            $temp['featured'] = $event->getFeatured();
            $temp['date_created'] = $event->getDateCreated();
            array_push($menuCategory, $temp);
            // end selected event

        }


        //get featured Album
        $other_events = array();

        $other_events_Query = "SELECT id FROM events  WHERE id != $event_id AND (endDate >= '$date_now') AND featured = 1 ORDER BY `events`.`ranking` DESC LIMIT " . $offset . "," . $no_of_records_per_page . "";

        $other_events_Query_result = mysqli_query($this->conn, $other_events_Query);
        while ($row = mysqli_fetch_array($other_events_Query_result)) {
            array_push($other_events, $row['id']);
        }

        foreach ($other_events as $row) {
            $event = new Events($this->conn, $row);
            $temp = array();
            $temp['id'] = $event->getId();
            $temp['title'] = $event->getTitle();
            $temp['description'] = $event->getDescription();
            $temp['startDate'] = $event->getStartDate();
            $temp['startTime'] = $event->getStartTime();
            $temp['endDate'] = $event->getEndDate();
            $temp['endtime'] = $event->getEndtime();
            $temp['location'] = $event->getLocation();
            $temp['host_name'] = $event->getHostName();
            $temp['host_contact'] = $event->getHostContact();
            $temp['image'] = $event->getImage();
            $temp['ranking'] = $event->getRanking();
            $temp['featured'] = $event->getFeatured();
            $temp['date_created'] = $event->getDateCreated();
            array_push($menuCategory, $temp);
        }


        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["Events"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;

        return $itemRecords;
    }


    function getSongRadio(): array
    {

        $songID = (isset($_GET['songID']) && $_GET['songID']) ? htmlspecialchars(strip_tags($_GET["songID"])) : '200';

        $date_now = date('d/M/Y');

        $menuCategory = array();
        $itemRecords = array();

        // Song
        $song = new Song($this->conn, $songID);

        $itemRecords['id'] = $song->getId();
        $itemRecords["artworkPath"] = $song->getAlbum()->getArtworkPath();;
        $itemRecords["title"] = $song->getTitle();
        $itemRecords["artist"] = $song->getArtist()->getName() .$song->getFeaturing();
        $itemRecords["artistID"] = $song->getArtistId();
        $itemRecords["genre"] = $song->getGenre()->getGenre();
        $itemRecords["heading"] = "Mwonyaa Mix Station: " . $song->getTitle();
        $itemRecords["subheading"] = "Selection of tracks based on " . $song->getTitle() . " by " . $song->getArtist()->getName() .$song->getFeaturing();
        $itemRecords["updated"] = $date_now;

        // get products id from the same cat
        $related_song_ids = $song->getSongRadio();

        foreach ($related_song_ids as $row) {
            $song = new Song($this->conn, $row);
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
            $temp['weeklyplays'] = $song->getWeeklyplays();


            array_push($menuCategory, $temp);
        }


        $itemRecords["Tracks"] = $menuCategory;


        return $itemRecords;
    }

    function saveAuthUser($data): array
    {

        //getting the values
//      $m_id = password_hash($data->id, PASSWORD_DEFAULT);
        $m_id = "mw" . $data->id;
        $m_username = $data->username;
        $m_full_name = $data->full_name;
        $m_email = $data->email;
        $m_phone = $data->phone;
        $m_password = md5($data->id);
        $m_signUpDate = date('Y-m-d H:i:s', time());
        $m_profilePic = $data->profilePic;
        $m_status = "registered";
        $m_mwRole = "mwuser";
        $m_accountOrigin = "googleAuth";

        //checking if the user is already exist with this username or email
        //as the email and username should be unique for every user
        $stmt = $this->conn->prepare("SELECT `id`, `username`, `firstName`, `email`,`phone`,`password`, `signUpDate`, `profilePic`, `status`, `mwRole` FROM users WHERE password = ? AND (email = ? OR id = ?)");
        $stmt->bind_param("sss", $m_password, $m_email, $m_id);
        $stmt->execute();
        $stmt->bind_result($m_id, $m_username, $m_full_name, $m_email, $m_phone, $m_password, $m_signUpDate, $m_profilePic, $m_status, $m_mwRole);
        $stmt->store_result();
        $stmt->fetch();
        $response = array();

        //if the user already exist in the database
        if ($stmt->num_rows > 0) {
            $response['id'] = $m_id;
            $response['username'] = $m_username;
            $response['full_name'] = $m_full_name;
            $response['email'] = $m_email;
            $response['phone'] = $m_phone;
            $response['password'] = $m_password;
            $response['signUpDate'] = $m_signUpDate;
            $response['profilePic'] = $m_profilePic;
            $response['status'] = $m_status;
            $response['mwRole'] = $m_mwRole;
            $response['error'] = false;
            $response['message'] = 'User already registered, Here are details';
            $stmt->close();
        } else {

            //if user is new creating an insert query
            $stmt = $this->conn->prepare("INSERT INTO users (`id`,`username`,`firstName`,`email`,`phone`,`Password`,`signUpDate`,`profilePic`,`status`,`accountOrigin`) VALUES (?, ?, ?, ?,?, ?, ?, ?, ?,?)");
            $stmt->bind_param("ssssssssss", $m_id, $m_username, $m_full_name, $m_email, $m_phone, $m_password, $m_signUpDate, $m_profilePic, $m_status, $m_accountOrigin);

            //if the user is successfully added to the database
            if ($stmt->execute()) {

                //fetching the user back
                $stmt = $this->conn->prepare("SELECT `id`, `username`, `firstName`, `email`,`phone`,`password`, `signUpDate`, `profilePic`, `status`, `mwRole` FROM users WHERE email = ? AND password = ?");
                $stmt->bind_param("ss", $m_email, $m_password);
                $stmt->execute();
                $stmt->bind_result($m_id, $m_username, $m_full_name, $m_email, $m_phone, $m_password, $m_signUpDate, $m_profilePic, $m_status, $m_mwRole);
                $stmt->store_result();
                $stmt->fetch();

                //if the user already exist in the database
                if ($stmt->num_rows > 0) {
                    $response['id'] = $m_id;
                    $response['username'] = $m_username;
                    $response['full_name'] = $m_full_name;
                    $response['email'] = $m_email;
                    $response['phone'] = $m_phone;
                    $response['password'] = $m_password;
                    $response['signUpDate'] = $m_signUpDate;
                    $response['profilePic'] = $m_profilePic;
                    $response['status'] = $m_status;
                    $response['mwRole'] = $m_mwRole;
                    $response['error'] = false;
                    $response['message'] = 'Registration Complete';
                    $stmt->close();
                } else {
                    $response['id'] = null;
                    $response['username'] = null;
                    $response['full_name'] = null;
                    $response['email'] = null;
                    $response['phone'] = null;
                    $response['password'] = null;
                    $response['signUpDate'] = null;
                    $response['profilePic'] = null;
                    $response['status'] = null;
                    $response['mwRole'] = null;
                    $response['error'] = true;
                    $response['message'] = 'User Registration Failed';
                }
            }
        }

        return $response;
    }


    function AddTrackToPlaylist($data): array
    {
        $current_Time_InSeconds = time();
        $date_added = date('Y-m-d H:i:s', $current_Time_InSeconds);

        $userID = $data->userID ?? null;
        $playlistID = $data->playlistID ?? null;
        $trackID = $data->trackID ?? null;;
        $playlistName = $data->playlistName ?? null;

        $itemRecords = array();
        $itemRecords['error'] = true;
        $itemRecords['message'] = "";
        $itemRecords['date'] = $date_added;


        if ($playlistID !== null && $trackID !== null && $userID !== null) {
            // Start the transaction
            mysqli_begin_transaction($this->conn);

            try {
                // Check if the playlist exists
                $playlistExistsQuery = "SELECT COUNT(*) as count FROM `playlists` WHERE `id` = ?";
                $playlistExistsStmt = mysqli_prepare($this->conn, $playlistExistsQuery);
                mysqli_stmt_bind_param($playlistExistsStmt, "s", $playlistID);
                mysqli_stmt_execute($playlistExistsStmt);
                mysqli_stmt_bind_result($playlistExistsStmt, $playlistCount);
                mysqli_stmt_fetch($playlistExistsStmt);
                mysqli_stmt_close($playlistExistsStmt);

                if ($playlistCount === 0) {
                    // Playlist does not exist
                    $itemRecords['error'] = true;
                    $itemRecords['message'] = "Playlist does not exist.";
                } else {
                    // Check if the track already exists in the playlist
                    $trackExistsQuery = "SELECT COUNT(*) as count FROM `playlistsongs` WHERE `playlistId` = ? AND `songId` = ?";
                    $trackExistsStmt = mysqli_prepare($this->conn, $trackExistsQuery);
                    mysqli_stmt_bind_param($trackExistsStmt, "ss", $playlistID, $trackID);
                    mysqli_stmt_execute($trackExistsStmt);
                    mysqli_stmt_bind_result($trackExistsStmt, $trackCount);
                    mysqli_stmt_fetch($trackExistsStmt);
                    mysqli_stmt_close($trackExistsStmt);

                    if ($trackCount > 0) {
                        // Track already exists in the playlist
                        $itemRecords['error'] = true;
                        $itemRecords['message'] = "Track already exists in the playlist.";
                    } else {
                        // Insert the track into the playlistsongs table
                        $insertQuery = "INSERT INTO `playlistsongs` (`songId`, `playlistId`, `dateAdded`) 
                SELECT ?, ?, ? 
                FROM DUAL 
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM `playlistsongs` 
                    WHERE `playlistId` = ? AND `songId` = ?
                )";
                        $insertStmt = mysqli_prepare($this->conn, $insertQuery);
                        mysqli_stmt_bind_param($insertStmt, "sssss", $trackID, $playlistID, $date_added, $playlistID, $trackID);
                        mysqli_stmt_execute($insertStmt);
                        $affectedRows = mysqli_stmt_affected_rows($insertStmt);
                        mysqli_stmt_close($insertStmt);

                        if ($affectedRows > 0) {
                            $itemRecords['error'] = false;
                            $itemRecords['message'] = "Track added successfully.";
                            $itemRecords['date'] = $date_added;
                        } else {
                            $itemRecords['error'] = true;
                            $itemRecords['message'] = "Track already exists in the playlist.";
                        }
                    }
                }

                // Commit the transaction
                mysqli_commit($this->conn);
            } catch (Exception $e) {
                // Rollback the transaction in case of any exception/error
                mysqli_rollback($this->conn);

                // Handle the exception/error
                $itemRecords['error'] = true;
                $itemRecords['message'] = "An error occurred during the transaction.";
            }

            return $itemRecords;


        } elseif ($playlistName !== null && $trackID !== null && $userID !== null) {
            // Generate a unique playlist ID
            $playlistID = "mwP_mobile" . uniqid();

// Check if the playlist already exists for the user
            $checkQuery = "SELECT 1 FROM `playlists` WHERE `name` = ? AND `ownerID` = ?";
            $checkStmt = mysqli_prepare($this->conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "ss", $playlistName, $userID);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            $playlistExists = mysqli_stmt_num_rows($checkStmt) > 0;
            mysqli_stmt_close($checkStmt);

            if ($playlistExists) {
                // Playlist already exists for the user
                $itemRecords['error'] = true;
                $itemRecords['message'] = "Playlist already exists with the same name";
            } else {
                // Begin a transaction
                mysqli_begin_transaction($this->conn);

                // Create a new playlist in the playlist table
                $insertPlaylistQuery = "
        INSERT INTO `playlists` (`id`, `name`, `ownerID`, `dateCreated`)
        VALUES (?, ?, ?, ?)
    ";
                $insertPlaylistStmt = mysqli_prepare($this->conn, $insertPlaylistQuery);
                mysqli_stmt_bind_param($insertPlaylistStmt, "ssss", $playlistID, $playlistName, $userID, $date_added);

                // Insert the track into the playlistsongs table
                $insertSongsQuery = "
        INSERT INTO `playlistsongs` (`songId`, `playlistId`, `dateAdded`)
        VALUES (?, ?, ?)
    ";
                $insertSongsStmt = mysqli_prepare($this->conn, $insertSongsQuery);
                mysqli_stmt_bind_param($insertSongsStmt, "sss", $trackID, $playlistID,$date_added);

                // Execute both queries within a transaction
                $transactionSuccessful = mysqli_stmt_execute($insertPlaylistStmt) && mysqli_stmt_execute($insertSongsStmt);

                if ($transactionSuccessful) {
                    // Commit the transaction
                    mysqli_commit($this->conn);

                    $itemRecords['error'] = false;
                    $itemRecords['message'] = "Playlist created and track added successfully.";
                    $itemRecords['date'] = $date_added;
                } else {
                    // Rollback the transaction
                    mysqli_rollback($this->conn);

                    $itemRecords['error'] = true;
                    $itemRecords['message'] = "Failed to create playlist.";
                }

                mysqli_stmt_close($insertPlaylistStmt);
                mysqli_stmt_close($insertSongsStmt);
            }


        } elseif ($playlistName !== null && $userID !== null) {
            // Generate a unique playlist ID
            $playlistID = "mwP_mobile" . uniqid();

// Check if the playlist already exists for the user
            $checkQuery = "SELECT 1 FROM `playlists` WHERE `name` = ? AND `ownerID` = ?";
            $checkStmt = mysqli_prepare($this->conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "ss", $playlistName, $userID);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            $playlistExists = mysqli_stmt_num_rows($checkStmt) > 0;
            mysqli_stmt_close($checkStmt);

            if ($playlistExists) {
                // Playlist already exists for the user
                $itemRecords['error'] = true;
                $itemRecords['message'] = "Playlist already exists with the same name";
            } else {
                // Begin a transaction
                mysqli_begin_transaction($this->conn);

                // Create a new playlist in the playlist table
                $insertPlaylistQuery = "
        INSERT INTO `playlists` (`id`, `name`, `ownerID`, `dateCreated`)
        VALUES (?, ?, ?, ?)
    ";
                $insertPlaylistStmt = mysqli_prepare($this->conn, $insertPlaylistQuery);
                mysqli_stmt_bind_param($insertPlaylistStmt, "ssss", $playlistID, $playlistName, $userID, $date_added);


                // Execute both queries within a transaction
                $transactionSuccessful = mysqli_stmt_execute($insertPlaylistStmt);

                if ($transactionSuccessful) {
                    // Commit the transaction
                    mysqli_commit($this->conn);

                    $itemRecords['error'] = false;
                    $itemRecords['message'] = "Playlist created  successfully.";
                    $itemRecords['date'] = $date_added;
                } else {
                    // Rollback the transaction
                    mysqli_rollback($this->conn);

                    $itemRecords['error'] = true;
                    $itemRecords['message'] = "Failed to create playlist.";
                }

                mysqli_stmt_close($insertPlaylistStmt);
            }

        } else {
            $itemRecords['message'] = "Invalid parameters provided";
        }

        return $itemRecords;
    }


    function updateTrackUserData(): array
    {

        $user_id = htmlspecialchars(strip_tags($this->user_id));
        $update_date = htmlspecialchars(strip_tags($this->update_date));

        $itemRecords = array();
        $updateIDs = array();


        if ($this->liteRecentTrackList != null) {
            foreach ($this->liteRecentTrackList as $i => $i_value) {
                $artist = htmlspecialchars(strip_tags($i_value->artist));
                $artistID = htmlspecialchars(strip_tags($i_value->artistID));
                $artworkPath = htmlspecialchars(strip_tags($i_value->artworkPath));
                $id = htmlspecialchars(strip_tags($i_value->id));
                $path = htmlspecialchars(strip_tags($i_value->path));
                $title = htmlspecialchars(strip_tags($i_value->title));
                $totalplays = htmlspecialchars(strip_tags($i_value->totalplays));
                $trackLastPlayed = htmlspecialchars(strip_tags($i_value->trackLastPlayed));
                $trackUserPlays = htmlspecialchars(strip_tags($i_value->trackUserPlays));

                $user_sql = "UPDATE users set songsplayed = songsplayed + $trackUserPlays WHERE id ='$user_id'";
                mysqli_query($this->conn, $user_sql);
                $song_sql = "UPDATE songs SET plays = plays + $trackUserPlays, weekplays = weekplays + $trackUserPlays, lastplayed='$trackLastPlayed'  WHERE id='$id'";
                mysqli_query($this->conn, $song_sql);

                //user favourites
                $fav_sql = "SELECT * FROM frequency where  userid='$user_id' AND songid='$id'";
                $sql = mysqli_query($this->conn, $fav_sql);


                if (mysqli_num_rows($sql) > 0) {
                    // echo "song and user Id Already Exists";
                    $stmt_RecentPlays = $this->conn->prepare("UPDATE frequency SET playsmonth = playsmonth + ?, plays = plays + ?, dateUpdated = ? , lastPlayed = ? WHERE userid= ? AND songid= ?");
                    $stmt_RecentPlays->bind_param("iisssi", $trackUserPlays, $trackUserPlays, $update_date, $trackLastPlayed, $user_id, $id);

                } else {
                    $stmt_RecentPlays = $this->conn->prepare("INSERT INTO frequency(songid,userid,plays,playsmonth,lastPlayed) VALUES (?,?,?,?,?)");
                    $stmt_RecentPlays->bind_param("isiis", $id, $user_id, $trackUserPlays, $trackUserPlays, $trackLastPlayed);

                }

                if ($stmt_RecentPlays->execute()) {
                    $this->exe_status = "success";
                    array_push($updateIDs, $id);
                } else {
                    $this->exe_status = "failure";
                }
            }
        }


        if ($this->liteLikedTrackList != null) {
            // LIKED SONGS
            foreach ($this->liteLikedTrackList as $i => $i_value) {
                $id = htmlspecialchars(strip_tags($i_value->id));
                $trackID = htmlspecialchars(strip_tags($i_value->trackID));
                $trackStatus = htmlspecialchars(strip_tags($i_value->trackStatus));


                $check = mysqli_query($this->conn, "SELECT songId FROM likedsongs WHERE songId = '$trackID' AND userID ='$user_id'");
                if (mysqli_num_rows($check) > 0) {
                    // echo "song and user Id Already Exists";
                    $stmt_LikedSongs = $this->conn->prepare("UPDATE likedsongs SET songId = ?, userID = ?, dateUpdated = ? WHERE songId= ? AND userID= ?");
                    $stmt_LikedSongs->bind_param("issis", $trackID, $user_id, $update_date, $trackID, $user_id);

                } else {

                    $stmt_LikedSongs = $this->conn->prepare("INSERT INTO likedsongs(`songId`,`userID`,`dateUpdated`) VALUES (?,?,?)");
                    $stmt_LikedSongs->bind_param("iss", $trackID, $user_id, $update_date);

                }

                if ($stmt_LikedSongs->execute()) {
                    $this->exe_status = "success";
                    array_push($updateIDs, $trackID);
                } else {
                    $this->exe_status = "failure";
                }


            }
        }


        if ($this->exe_status == "success") {
            $itemRecords['error'] = false;
            $itemRecords['message'] = "updated successfully";
            $itemRecords['trackIds'] = $updateIDs;

        } else {
            $itemRecords['error'] = true;
            $itemRecords['message'] = "update failed";
            $itemRecords['trackIds'] = $updateIDs;
        }
        return $itemRecords;
    }


    public function loginHandler(): array
    {
        $feedback = [];

        try {
            //login button was pressed
            $username = htmlspecialchars(strip_tags($_GET["loginUsername"]));
            $password = htmlspecialchars(strip_tags($_GET["loginPassword"]));

            $account = new Account($this->conn);
            $result = $account->login($username, $password);


            try {
                if ($result) {
                    if ($result == true) {
                        $usernameFromemail = $account->getEmailtousername($username);
                        $feedback['success'] = $usernameFromemail;

                    }
                }
            } catch (\Throwable $th) {
                $feedback['error'] = $this->getMessage();
            }
        } catch (\Throwable $th) {
            $feedback['success'] = false;
            $feedback['error'] = "Error With Login Button";
            $feedback['error'] = $th->getMessage();
        }

        return $feedback;
    }


    // generate daily trend
    function dailyTrend(): array
    {

        $itemRecords = array();
        // get products id from the same cat
        $dailyTrendsIDs = array();
        $dailyTrendsTracks = array();
        $itemRecords["Playlists"] = array();


        $sql = "SELECT songid,sum(plays) as totalplays from frequency WHERE lastPlayed > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY songid ORDER BY totalplays DESC limit 50";
        $other_events_Query_result = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_array($other_events_Query_result)) {
            array_push($dailyTrendsIDs, $row);
        }


        foreach ($dailyTrendsIDs as $id) {
            $song = new Song($this->conn, $id['songid']);
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
            $temp['weeklyplays'] = $id['totalplays'];


            array_push($dailyTrendsTracks, $temp);
        }

        $slider_temps = array();
        $slider_temps['Tracks'] = $dailyTrendsTracks;
        array_push($itemRecords['Playlists'], $slider_temps);


        return $itemRecords;
    }

    public function Versioning()
    {
        $itemRecords = array();
        $itemRecords["version"] = "9"; // build number should match
        $itemRecords["update"] = true; // update dialog dismissable
        $itemRecords["message"] = "We have new updates for you";
        return $itemRecords;
    }

    public function LibraryBanners(): array
    {

        // get_Slider_banner
        $sliders = array();
        // Set up the prepared statement
        $slider_query = "SELECT ps.id, ps.playlistID, ps.imagepath FROM playlist_sliders ps WHERE status = 1 ORDER BY date_created DESC LIMIT 8;";
        $featured_album_Query_result = mysqli_query($this->conn, $slider_query);
        while ($row = mysqli_fetch_array($featured_album_Query_result)) {
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['playlistID'] = $row['playlistID'];
            $temp['imagepath'] = $row['imagepath'];
            array_push($sliders, $temp);
        }


        return $sliders;
    }


}
