<?php

class Handler
{

    private $ImageBasepath = "https://mwonyaa.com/";
    public $pageNO;
    public $albumID;
    private $conn;
    private $version;

    public function __construct($con)
    {
        $this->conn = $con;
        $this->version = 1;
    }


    function readArtistProfile()
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
            array_push($itemRecords["Artist"], $artistIntro);

            // latest release
            $arry = $artist_instance->getLatestRelease();
            $lR = array();
            $temp = array();
            $temp['id'] = $arry->getId();
            $temp['name'] = $arry->getTitle();
            $temp['Date'] = $arry->getDatecreated();
            $temp['artwork'] = $arry->getArtworkPath();
            array_push($lR, $temp);


            $artist_latest_release = array();
            $artist_latest_release['heading'] = "Latest Release";
            $artist_latest_release['ArtistLatestRelease'] = $lR;
            array_push($itemRecords["Artist"], $artist_latest_release);

            // popular tracks
            $populartracks = $artist_instance->getSongIds();
            $popular = array();
            foreach ($populartracks as $songId) {
                $song = new Song($this->conn, $songId);
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

                array_push($popular, $temp);
            }


            $popular_temps = array();
            $popular_temps['heading'] = "Popular Tracks";
            $popular_temps['Tracks'] = $popular;
            array_push($itemRecords["Artist"], $popular_temps);


            // Artist Pick - Top playlist created by the Artist
            $ArtistPick = array();
            $artistiPick = new ArtistPick($this->conn, $artistID);

            if ($artistiPick->getId() != null) {
                $temp = array();
                $temp['id'] = $artistiPick->getId();
                $temp['type'] = "Playlist";
                $temp['out_now'] = $artistiPick->getTitle() . " - out now";;
                $temp['coverimage'] = $artistiPick->getCoverArt();
                $temp['song_title'] = $artistiPick->getArtist()->getName() . " - " . $artistiPick->getSong()->getTitle();
                $temp['song_cover'] = $artistiPick->getSong()->getAlbum()->getArtworkPath();
                array_push($ArtistPick, $temp);
            } else {
                // latest release
                $arry = $artist_instance->getLatestRelease();
                $temp = array();
                $temp['id'] = $arry->getId();
                $temp['type'] = "Album";
                $temp['out_now'] = $arry->getTitle() . " - out now";;
                $temp['coverimage'] = $arry->getArtworkPath();
                $temp['song_title'] = $arry->getArtist()->getName() . " - " . $arry->getTitle();
                $temp['song_cover'] = $arry->getArtworkPath();
                array_push($ArtistPick, $temp);
            }


            $artistpick_array = array();
            $artistpick_array['heading'] = "Artist Pick";
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
            $popular_temps['RelatedArtist'] = $popular_release;
            array_push($itemRecords["Artist"], $popular_temps);


            // Event
            $ArtistEvent = array();
            $artist_event = new ArtistEvents($this->conn, $artistID);
            $temp = array();
            $temp['id'] = $artist_event->getId();
            $temp['name'] = $artist_event->getName();
            $temp['title'] = $artist_event->getTitle();
            $temp['description'] = $artist_event->getDescription();
            $temp['venue'] = $artist_event->getVenu();
            $temp['date'] = $artist_event->getDate();
            $temp['time'] = $artist_event->getTime();
            array_push($ArtistEvent, $temp);

            $events_array = array();
            $events_array['heading'] = "Artist Events";
            $events_array['Events'] = $ArtistEvent;
            array_push($itemRecords["Artist"], $events_array);

            // Artist Bio
            $bio_array = array();
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

            $events_array = array();
            $events_array['heading'] = "Artist Bio";
            $events_array['Bio'] = $bio_array;
            array_push($itemRecords["Artist"], $events_array);


            $itemRecords["total_pages"] = 1;
            $itemRecords["total_results"] = 1;


        }
        return $itemRecords;
    }


    function allCombined()
    {

        $home_page = (isset($_GET['page']) && $_GET['page']) ? htmlspecialchars(strip_tags($_GET["page"])) : '1';

        $page = floatval($home_page);
        $no_of_records_per_page = 10;
        $offset = ($page - 1) * $no_of_records_per_page;

        $sql = "SELECT DISTINCT(genre) as count FROM songs WHERE tag IN ('music') ORDER BY `songs`.`plays` DESC LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $category_ids = array();
        $menuCategory = array();
        $itemRecords = array();


        if ($page == 1) {

            // get_Slider_banner
            $slider_id = array();
            $sliders = array();


            $slider_query = "SELECT id FROM playlist_sliders WHERE status=1 ORDER BY date_created DESC LIMIT 8";
            $slider_query_id_result = mysqli_query($this->conn, $slider_query);
            while ($row = mysqli_fetch_array($slider_query_id_result)) {
                array_push($slider_id, $row['id']);
            }


            foreach ($slider_id as $row) {
                $temp = array();
                $slider = new PlaylistSlider($this->conn, $row);
                $temp['id'] = $slider->getId();
                $temp['playlistID'] = $slider->getPlaylistID();
                $temp['imagepath'] = $slider->getImagepath();
                array_push($sliders, $temp);
            }

            $slider_temps = array();
            $slider_temps['heading'] = "Discover";
            $slider_temps['featured_sliderBanners'] = $sliders;
            array_push($menuCategory, $slider_temps);
            // end get_Slider_banner


            //get genres
            $top_home_genreIDs = array();
            $featured_genres = array();
            $top_genre_stmt = "SELECT DISTINCT(genre) FROM songs WHERE tag IN ('music') ORDER BY `songs`.`plays` DESC LIMIT 8;";
            $top_genre_stmt_result = mysqli_query($this->conn, $top_genre_stmt);

            while ($row = mysqli_fetch_array($top_genre_stmt_result)) {
                array_push($top_home_genreIDs, $row['genre']);
            }

            foreach ($top_home_genreIDs as $row) {
                $genre = new Genre($this->conn, $row);
                $temp = array();
                $temp['id'] = $genre->getGenreid();
                $temp['name'] = $genre->getGenre();
                $temp['tag'] = $genre->getTag();
                array_push($featured_genres, $temp);
            }

            $feat_genres = array();
            $feat_genres['heading'] = "Featured genres";
            $feat_genres['featuredGenres'] = $featured_genres;
            array_push($menuCategory, $feat_genres);

            // end genres


            //get Trending Artist

            $featuredartists = array();
            $featuredCategory = array();

            $musicartistQuery = "SELECT id, profilephoto, name FROM artists WHERE tag='music' ORDER BY overalplays DESC LIMIT 8";
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
            $feat_Cat_temps['heading'] = "Featured Artists";
            $feat_Cat_temps['featuredArtists'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end featuredArtist


            //get Featured Playlist
            $featured_playlist = array();
            $featuredPlaylist = array();

            $featured_playlist_Query = "SELECT id,name, owner, coverurl FROM playlists where status = 1 AND featuredplaylist ='yes' ORDER BY RAND () LIMIT 8";
            $featured_playlist_Query_result = mysqli_query($this->conn, $featured_playlist_Query);
            while ($row = mysqli_fetch_array($featured_playlist_Query_result)) {
                array_push($featured_playlist, $row);
            }


            foreach ($featured_playlist as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['name'] = $row['name'];
                $temp['owner'] = $row['owner'];
                $temp['coverurl'] = $row['coverurl'];
                array_push($featuredPlaylist, $temp);
            }

            $feat_playlist_temps = array();
            $feat_playlist_temps['heading'] = "Featured Playlists";
            $feat_playlist_temps['featuredPlaylists'] = $featuredPlaylist;
            array_push($menuCategory, $feat_playlist_temps);
            ///end featuredArtist


            //get featured Album
            $featured_albums = array();
            $featuredAlbums = array();

            $featured_album_Query = "SELECT * FROM albums WHERE tag = \"music\" ORDER BY totalsongplays DESC LIMIT  8";
            $featured_album_Query_result = mysqli_query($this->conn, $featured_album_Query);
            while ($row = mysqli_fetch_array($featured_album_Query_result)) {
                array_push($featured_albums, $row);
            }


            foreach ($featured_albums as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['title'] = $row['title'];
                $temp['artworkPath'] = $row['artworkPath'];
                $temp['tag'] = $row['tag'];
                array_push($featuredAlbums, $temp);
            }

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "Featured Albums";
            $feat_albums_temps['featuredAlbums'] = $featuredAlbums;
            array_push($menuCategory, $feat_albums_temps);
            ///end featuredAlbums


            //get featured Dj mixes
            $featured_dj_mixes = array();
            $featuredDJMIXES = array();

            $featured_mixes_Query = "SELECT * FROM albums WHERE tag = \"dj\" ORDER BY datecreated DESC LIMIT 8";
            $featured_mixes_Query_result = mysqli_query($this->conn, $featured_mixes_Query);
            while ($row = mysqli_fetch_array($featured_mixes_Query_result)) {
                array_push($featured_dj_mixes, $row);
            }


            foreach ($featured_dj_mixes as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['title'] = $row['title'];
                $temp['artworkPath'] = $row['artworkPath'];
                $temp['tag'] = $row['tag'];
                array_push($featuredDJMIXES, $temp);
            }

            $feat_albums_temps = array();
            $feat_albums_temps['heading'] = "Featured Mixes";
            $feat_albums_temps['FeaturedDjMixes'] = $featuredDJMIXES;
            array_push($menuCategory, $feat_albums_temps);
            ///end featuredAlbums


        }


        //fetch other categories Begin
        $home_genreIDs = array();
        $genre_stmt = "SELECT DISTINCT(genre) FROM songs WHERE tag IN ('music') ORDER BY `songs`.`plays` DESC LIMIT " . $offset . "," . $no_of_records_per_page . "";
        $genre_stmt_result = mysqli_query($this->conn, $genre_stmt);

        while ($row = mysqli_fetch_array($genre_stmt_result)) {

            array_push($home_genreIDs, $row['genre']);
        }

        foreach ($home_genreIDs as $row) {
            $genre = new Genre($this->conn, $row);
            $temp = array();
            $temp['id'] = $genre->getGenreid();
            $temp['name'] = $genre->getGenre();
            $temp['tag'] = $genre->getTag();
            $temp['Tracks'] = $genre->getGenre_Songs(6);
            array_push($menuCategory, $temp);
        }

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $page;
        $itemRecords["featured"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;

        return $itemRecords;
    }


    function readUserLikedSongs()
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
                    $temp['title'] = "Liked Tracks";
                    $temp['subtitle'] = "Tracks Liked by you";
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
    function readSelectedAlbum()
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


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['Album'], $slider_temps);


        }


        return $itemRecords;
    }

    public function searchHomePage()
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


        //get genres
        //  popular search Begin
        $bestsellingProductsID = array();
        $bestSellingProducts = array();
        $category_stmts = "SELECT `id`, `query`, `count`, `created_at`, `updated_at` FROM `searches` ORDER BY count DESC LIMIT 30";
        $menu_type_id_results = mysqli_query($this->conn, $category_stmts);

        while ($row = mysqli_fetch_array($menu_type_id_results)) {

            array_push($bestsellingProductsID, $row);
        }

        foreach ($bestsellingProductsID as $row) {
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['query'] = $row['query'];
            $temp['count'] = $row['count'];
            $temp['created_at'] = $row['created_at'];
            $temp['updated_at'] = $row['updated_at'];
            array_push($bestSellingProducts, $temp);
        }


        $slider_temps = array();
        $slider_temps['heading'] = "Popular Search";
        $slider_temps['popularSearch'] = $bestSellingProducts;
        array_push($menuCategory, $slider_temps);

        // end popular search  Fetch

        // end genres


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


    function searchNormal()
    {
        $page = htmlspecialchars(strip_tags($_GET["page"]));
        $search_query = htmlspecialchars(strip_tags($_GET["key_query"]));
        $search_algorithm = "normal";
        // create the base variables for building the search query

//        echo $search_string;
        $search_string = "(SELECT id,title,artist,path,'artworkPath', 'song' as type FROM songs WHERE title LIKE'%" . $search_query . "%' ) 
           UNION
           (SELECT id,name,'artist','path',profilephoto, 'artist' as type FROM artists  WHERE name LIKE'%" . $search_query . "%' ) 
           UNION
           (SELECT id,title,artist,'path',artworkPath, 'album' as type FROM albums  WHERE title LIKE'%" . $search_query . "%' ) 
           UNION
           (SELECT id,name,'artist','path',coverurl, 'playlist' as type FROM playlists WHERE name LIKE'%" . $search_query . "%' )";


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
                    $temp['artist'] = $song->getArtist()->getName();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
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
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                }
                if ($row['type'] == "artist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = $row['title'];
                    $temp['artistID'] = '';
                    $temp['title'] = '';
                    $temp['path'] = $row['path'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                }
                if ($row['type'] == "playlist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = '';
                    $temp['artistID'] = '';
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
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
            (SELECT id,title,artist,path,'artworkPath', 'song' as type FROM songs WHERE MATCH (title) AGAINST('" . $search_query . "' IN NATURAL LANGUAGE MODE) ) 
           UNION
           (SELECT id,name,'artist','path',profilephoto, 'artist' as type FROM artists  WHERE MATCH (name) AGAINST('" . $search_query . "' IN NATURAL LANGUAGE MODE) ) 
           UNION
           (SELECT id,title,artist,'path',artworkPath, 'album' as type FROM albums  WHERE  MATCH (title) AGAINST('" . $search_query . "' IN NATURAL LANGUAGE MODE)) 
           UNION
           (SELECT id,name,'artist','path',coverurl, 'playlist' as type FROM playlists WHERE  MATCH (name) AGAINST('" . $search_query . "' IN NATURAL LANGUAGE MODE))";


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
                    $temp['artist'] = $song->getArtist()->getName();
                    $temp['artistID'] = $row['artist'];
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
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
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                }
                if ($row['type'] == "artist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = $row['title'];
                    $temp['artistID'] = '';
                    $temp['title'] = '';
                    $temp['path'] = $row['path'];
                    $temp['artworkPath'] = $row['artworkPath'];
                    $temp['type'] = $row['type'];
                }
                if ($row['type'] == "playlist") {
                    $temp['id'] = $row['id'];
                    $temp['artist'] = '';
                    $temp['artistID'] = '';
                    $temp['title'] = $row['title'];
                    $temp['path'] = $row['path'];
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
        return $itemRecords;
    }


    function readSelectedGenre(){

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

    function readSelectedPlaylist()
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


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['Playlists'], $slider_temps);


        }


        return $itemRecords;
    }

    function readSong()
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
            $temp['artist'] = $song->getArtist()->getName();
            $temp['artistID'] = $song->getArtistId();
            $temp['album'] = $song->getAlbum()->getTitle();
            $temp['albumID'] = $song->getAlbumId();
            $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
            $temp['genre'] = $song->getGenre()->getGenre();
            $temp['genreID'] = $song->getGenre()->getGenreid();
            $temp['duration'] = $song->getDuration();
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


                array_push($all_Related_Songs, $temp);
            }

            $slider_temps = array();
            $slider_temps['Related Songs'] = "Related Songs";
            $slider_temps['Tracks'] = $all_Related_Songs;
            array_push($itemRecords['Song'], $slider_temps);



            $itemRecords["total_pages"] = 1;
            $itemRecords["total_results"] = 1;


        }
        return $itemRecords;
    }
}
