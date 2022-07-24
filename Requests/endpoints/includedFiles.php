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

include_once '../../Includes/TableFunctions/Handler.php';

$database = new Database();
$db = $database->getConnection();
