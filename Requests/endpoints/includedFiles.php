<?php
require '../../Includes/config/Database.php';
require "../../Includes/TableClasses/User.php";
require "../../Includes/TableClasses/Artist.php";
require "../../Includes/TableClasses/Album.php";
require "../../Includes/TableClasses/Genre.php";
require "../../Includes/TableClasses/Song.php";
require "../../Includes/TableClasses/Playlist.php";
require "../../Includes/TableClasses/SharedPlaylist.php";
require "../../Includes/TableClasses/Shared.php";
require "../../Includes/TableClasses/LikedSong.php";
require "../../Includes/TableClasses/ArtistPick.php";
require "../../Includes/TableClasses/ArtistEvents.php";
require "../../Includes/TableClasses/PlaylistSlider.php";
require "../../Includes/TableClasses/SearchSlider.php";
require "../../Includes/TableClasses/Events.php";
require "../../Includes/TableClasses/Constants.php";
require "../../Includes/TableClasses/Account.php";
require "../../Includes/TableClasses/TrackTotalPlay.php";

include_once '../../Includes/TableFunctions/Handler.php';
include_once '../../Includes/TableFunctions/FilterGateway.php';

$database = new Database();
$db = $database->getConnection();
