<?php
class PlaylistCoverGenerator {
    private $dbConnection;
    private $outputPath;
    private $assetBaseUrl;

    public function __construct($dbConnection, $outputPath = '/var/www/mwonya_assets/assets/playlist_covers/', $assetBaseUrl = 'https://assets.mwonya.com/playlist_covers/') {
        // Check if GD library is installed
        if (!extension_loaded('gd')) {
            throw new Exception("GD Library is not installed. Please install php-gd extension.");
        }

        $this->dbConnection = $dbConnection;

        // Ensure output path exists and is writable
        $this->outputPath = rtrim($outputPath, '/') . '/';
        // Detailed directory check and creation
        try {
            if (!file_exists($this->outputPath)) {
                // Use recursive mkdir with proper permissions
                if (!mkdir($this->outputPath, 0755, true)) {
                    throw new Exception("Cannot create directory: " . $this->outputPath);
                }
            }

            // Verify directory is writable
            if (!is_writable($this->outputPath)) {
                throw new Exception("Directory is not writable: " . $this->outputPath);
            }
        } catch (Exception $e) {
            // Log the exact error
            error_log("Directory Creation Error: " . $e->getMessage());
            throw $e;
        }

        // Set base URL for accessing assets
        $this->assetBaseUrl = rtrim($assetBaseUrl, '/') . '/';
    }

    public function generateCover($playlistId, $playlistName = '') {

        // Generate a unique filename
        $uniqueId = substr(md5(uniqid($playlistName . '_' . $playlistId, true)), 0, 10);
        $filename = 'playlist_mwonya_' . $uniqueId . '_cover.jpg';
        $fullPath = $this->outputPath . $filename;
        $webPath = $this->assetBaseUrl . $filename;

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
            return $this->getDefaultCover($playlistId, $playlistName);
        }

        // Create new image based on number of covers
        $finalWidth = 500; // Final image width
        $finalHeight = 500; // Final image height

        try {
            $finalImage = imagecreatetruecolor($finalWidth, $finalHeight);

            // Set a white background
            $white = imagecolorallocate($finalImage, 255, 255, 255);
            imagefill($finalImage, 0, 0, $white);

            if (count($covers) == 1) {
                // Single track - use full cover
                $source = $this->loadImage($covers[0]);
                if ($source) {
                    imagecopyresampled(
                        $finalImage, $source,
                        0, 0, 0, 0,
                        $finalWidth, $finalHeight,
                        imagesx($source), imagesy($source)
                    );
                    imagedestroy($source);
                }
            } else {
                // Multiple tracks - create 2x2 grid
                $gridSize = $finalWidth / 2;
                foreach ($covers as $index => $coverPath) {
                    if ($index >= 4) break; // Maximum 4 covers

                    $row = floor($index / 2);
                    $col = $index % 2;

                    $source = $this->loadImage($coverPath);
                    if ($source) {
                        imagecopyresampled(
                            $finalImage, $source,
                            $col * $gridSize, $row * $gridSize, 0, 0,
                            $gridSize, $gridSize,
                            imagesx($source), imagesy($source)
                        );
                        imagedestroy($source);
                    }
                }
            }

            // Ensure directory exists
            if (!file_exists($this->outputPath)) {
                mkdir($this->outputPath, 0755, true);
            }

            // Save the generated cover
            imagejpeg($finalImage, $fullPath, 90);
            imagedestroy($finalImage);

            // Update playlist cover in database
            $this->updatePlaylistCover($playlistId, $webPath);

            return $webPath;
        } catch (Exception $e) {
            // Fallback to default cover if image generation fails
            error_log("Cover generation failed: " . $e->getMessage());
            return $this->getDefaultCover($playlistId, $playlistName);
        }
    }


    private function loadImage($imagePath) {
        // Determine image type and load accordingly
        $imageInfo = getimagesize($imagePath);

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($imagePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            default:
                return false;
        }
    }

    private function updatePlaylistCover($playlistId, $coverPath) {
        // Update playlist cover in database
        $updateQuery = "UPDATE playlists SET coverurl = ? WHERE id = ?";
        $stmt = $this->dbConnection->prepare($updateQuery);
        $stmt->bind_param('ss', $coverPath, $playlistId);
        $stmt->execute();
    }

    private function getDefaultCover($playlistId = null, $playlistName = '') {
        // Generate a unique default cover
        $uniqueId = $playlistId ? substr(md5($playlistId . $playlistName), 0, 10) : '';
        $filename = 'default_playlist_cover_' . $uniqueId . '.jpg';
        $fullPath = $this->outputPath . $filename;
        $webPath = $this->assetBaseUrl . $filename;

        // If default cover doesn't exist, create a simple one
        if (!file_exists($fullPath)) {
            $defaultImage = imagecreatetruecolor(500, 500);
            $bgColor = imagecolorallocate($defaultImage, 200, 200, 200);
            imagefill($defaultImage, 0, 0, $bgColor);

            // Add some text
            $textColor = imagecolorallocate($defaultImage, 0, 0, 0);
            $font = 5; // Built-in font
            imagestring($defaultImage, $font, 150, 240, "No Cover", $textColor);

            imagejpeg($defaultImage, $fullPath);
            imagedestroy($defaultImage);
        }

        return $webPath;
    }
}
