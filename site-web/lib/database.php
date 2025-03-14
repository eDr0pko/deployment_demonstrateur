<?php
    include 'config.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


    // Connection to the mysql database
    class database{
        static $db = null;
        static function connexionBD() {
            if (self::$db != null) {
                return self::$db;
            }
            require_once ("config.php");
            try {
                self::$db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (PDOException $exception) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
            return self::$db;
        }
    }


    // Get user from the database
    function dbGetUser($db, $mail, $pwd) {
        try {
            $request = 'SELECT * FROM users WHERE mail=:mail';
            $statement = $db->prepare($request);
            $statement->bindParam(':mail', $mail);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            
            if (!empty($result) && password_verify($pwd, $result['password'])) {
                return $result;
            } else {
                return "error";
            }
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }
    
    
    // Check if the email is already taken
    function AlreadyUser($db, $mail) {
        try {
            $request = 'SELECT * FROM users where mail=:mail';
            $statement = $db->prepare($request);
            $statement->bindParam(':mail', $mail);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            if (empty($user)) {
                return false;
            } else {
                return true;
            }
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }
    
    // Insert a new user in the database
    function dbInsertNewUser($db, $mail, $username, $pwd, $profile_picture) {
        try {
            // Check if the email is already taken
            if (AlreadyUser($db, $mail)) {
                return "Already";
            }
            
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (mail, username, password, profile_picture) VALUES (:mail, :username, :password, :profile_picture)");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':profile_picture', $profile_picture);
            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get the user's username
function dbGetUserInfos($db, $mail) {
    try {
        $request = 'SELECT username FROM users WHERE mail = :mail';
        $statement = $db->prepare($request);
        $statement->bindParam(':mail', $mail);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $exception) {
        error_log('Request error: ' . $exception->getMessage());
        return false;
    }
}


    // Get all the songs from the database
    function dbGetSongs($db) {
        try {
            $request = 'SELECT * FROM songs';
            $statement = $db->prepare($request);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }
    
    // Get all playlists of the user
    function dbGetUserPlaylists($db, $mail) {
        try {
            $request = 'SELECT * FROM playlist WHERE mail=:mail';
            $statement = $db->prepare($request);
            $statement->bindParam(':mail', $mail);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get all songs liked by the user
function dbGetLikedSongs($db, $mail) {
    try {
        $request = 'SELECT * FROM likes WHERE mail=:mail';
        $statement = $db->prepare($request);
        $statement->bindParam(':mail', $mail);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Ajouter un log pour vérifier le contenu de $result
        if (empty($result)) {
            error_log("Aucune chanson likée trouvée pour l'email : $mail");
        } else {
            error_log("Chansons likées trouvées pour l'email : $mail");
        }

        return $result;
    } catch (PDOException $exception) {
        error_log('Request error: ' . $exception->getMessage());
        return "Error: " . $exception->getMessage();
    }
}
    

    // Get album's name via song id
    function dbGetAlbum($db, $id_song) {
        try {
            $request = 'SELECT id_album FROM appartient WHERE id_song = :id_song';
            $statement = $db->prepare($request);
            $statement->bindParam(':id_song', $id_song, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $exception) {
            error_log('Request error (dbGetAlbum): ' . $exception->getMessage());
            return false;
        }
    }

    function dbGetAlbumName($db, $id_album) {
        try {
            $request = 'SELECT name FROM album WHERE id_album = :id_album';
            $statement = $db->prepare($request);
            $statement->bindParam(':id_album', $id_album, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $exception) {
            error_log('Request error (dbGetAlbumName): ' . $exception->getMessage());
            return false;
        }
    }

    // Get artist's email via artist id
    function dbGetArtist($db, $id_artist) {
        try {
            $request = 'SELECT mail FROM artist WHERE id_artist = :id_artist';
            $statement = $db->prepare($request);
            $statement->bindParam(':id_artist', $id_artist, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }

    // Function to remove a song from a playlist
    function removeSongFromPlaylist($id_song, $id_playlist) {
        try {
            $stmt = $db->prepare("DELETE FROM playlist_songs WHERE id_song = :id_song AND id_playlist = :id_playlist");
            $stmt->bindParam(':id_song', $id_song, PDO::PARAM_INT);
            $stmt->bindParam(':id_playlist', $id_playlist, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur removeSongFromPlaylist: " . $e->getMessage());
            return false;
        }
    }

    // Get liked songs of the user
    function dbGetIdSong($db, $mail) {
        try {
            $stmt = $db->prepare("SELECT id_song FROM likes WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            error_log("dbGetIdSong retourne : " . json_encode($result)); // DEBUG
    
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur dbGetIdSong: " . $e->getMessage());
            return false;
        }
    }   

    // function to get song from id
    function dbGetSong($db, $id_song) {
        try {
            $stmt = $db->prepare("SELECT * FROM songs WHERE id_song = :id_song");
            $stmt->bindParam(':id_song', $id_song);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dbGetSong: " . $e->getMessage());
            return false;
        }
    }

    // function to delete a liked song
    function dbDeleteLikedSong($db, $mail, $id_song) {
        try {
            $stmt = $db->prepare("DELETE FROM likes WHERE mail = :mail AND id_song = :id_song");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':id_song', $id_song);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbDeleteLikedSong: " . $e->getMessage());
            return false;
        }
    }








    function dbGetPlaylistSongs($db, $id_playlist) {
        try {
            $stmt = $db->prepare("
                SELECT s.id_song, s.name, s.song, s.picture, s.time, s.id_artist
                FROM playlist_songs ps
                JOIN songs s ON ps.id_song = s.id_song
                WHERE ps.id_playlist = :id_playlist
            ");
            $stmt->bindParam(':id_playlist', $id_playlist, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dbGetPlaylistSongs: " . $e->getMessage());
            return false;
        }
    }
    
    
    













    /*
    function updateProfile($mail, $username, $password = null, $profilePicture = null) {
        $conn = dbConnect();
        $sql = "UPDATE users SET username = ?";
        
        if ($password) {
            $sql .= ", password = ?";
        }
    
        if ($profilePicture) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($profilePicture['name']);
            move_uploaded_file($profilePicture['tmp_name'], $targetFile);
            $sql .= ", profile_picture = ?";
        }
    
        $sql .= " WHERE mail = ?";
    
        $stmt = $conn->prepare($sql);
    
        if ($password && $profilePicture) {
            $stmt->bind_param("ssss", $username, $password, $targetFile, $mail);
        } elseif ($password) {
            $stmt->bind_param("sss", $username, $password, $mail);
        } elseif ($profilePicture) {
            $stmt->bind_param("sss", $username, $targetFile, $mail);
        } else {
            $stmt->bind_param("ss", $username, $mail);
        }
    
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Profil mis à jour avec succès.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil.'];
        }
    }
    
    // Supprimer un compte utilisateur
    function deleteAccount($mail) {
        $conn = dbConnect();
        $sql = "DELETE FROM users WHERE mail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mail);
    
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Compte supprimé avec succès.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du compte.'];
        }
    }
        */
?>


