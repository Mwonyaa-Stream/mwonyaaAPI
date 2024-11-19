<?php
class PlaylistCoverGenerator {
    private $dbConnection;
    private $outputPath;

    public function __construct($dbConnection, $outputPath = 'playlist_covers/') {
        $this->dbConnection = $dbConnection;
        $this->outputPath = $outputPath;

        // Create output directory if it doesn't exist
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0755, true);
        }
    }

    public function generateCover($playlistId) {
        // Get tracks for this playlist
        $query = "SELECT DISTINCT a.artworkPath AS album_cover FROM  playlistsongs ps JOIN  songs s ON ps.songId = s.id JOIN  albums a ON s.album = a.id WHERE  ps.playlistId = ? limit 4";

        $stmt = $this->dbConnection->prepare($query);
        $stmt->bind_param('s', $playlistId);
        $stmt->execute();
        $result = $stmt->get_result();

        $covers = [];
        while ($row = $result->fetch_assoc()) {
            $covers[] = $row['album_cover'];
        }

        // If no tracks, return default cover
        if (empty($covers)) {
            return 'default_cover.jpg';
        }

        // Create new image based on number of covers
        $finalWidth = 500; // Final image width
        $finalHeight = 500; // Final image height
        $finalImage = imagecreatetruecolor($finalWidth, $finalHeight);

        if (count($covers) == 1) {
            // Single track - use full cover
            $sourcePath = $covers[0];
            $source = imagecreatefromjpeg($sourcePath);
            imagecopyresampled(
                $finalImage, $source,
                0, 0, 0, 0,
                $finalWidth, $finalHeight,
                imagesx($source), imagesy($source)
            );
            imagedestroy($source);
        } else {
            // Multiple tracks - create 2x2 grid
            $gridSize = $finalWidth / 2;
            foreach ($covers as $index => $coverPath) {
                if ($index >= 4) break; // Maximum 4 covers

                $row = floor($index / 2);
                $col = $index % 2;

                $source = imagecreatefromjpeg($coverPath);
                imagecopyresampled(
                    $finalImage, $source,
                    $col * $gridSize, $row * $gridSize, 0, 0,
                    $gridSize, $gridSize,
                    imagesx($source), imagesy($source)
                );
                imagedestroy($source);
            }
        }

        // Save the generated cover
        $outputFilename = $this->outputPath . 'playlist_' . $playlistId . '_cover.jpg';
        imagejpeg($finalImage, $outputFilename, 90);
        imagedestroy($finalImage);

        // Update playlist cover in database
        $updateQuery = "UPDATE playlists SET coverurl = ? WHERE id = ?";
        $stmt = $this->dbConnection->prepare($updateQuery);
        $stmt->bind_param('ss', $outputFilename, $playlistId);
        $stmt->execute();

        return $outputFilename;
    }
}
