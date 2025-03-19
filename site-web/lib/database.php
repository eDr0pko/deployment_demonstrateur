<?php
    include 'config.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


    // Connection to the mysql database
    class database {
        static $db = null;

        static function connexionBD(){
            if (self::$db != null){
                return self::$db;
            }
            require_once("config.php");

            try {
                self::$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
                if (self::$db->connect_error){
                    throw new Exception("Erreur de connexion à la base de données : " . self::$db->connect_error);
                }
            } catch (Exception $exception){
                die("Erreur de connexion à la base de données : " . $exception->getMessage());
            }

            return self::$db;
        }
    }

    // Get user from the database
    function dbGetUser($db, $mail, $pwd){
        try {
            $stmt = $db->prepare('SELECT * FROM users WHERE mail=?');
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!empty($result) && password_verify($pwd, $result['password'])) {
                return $result;
            } else {
                return "error";
            }
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }
    
    // Check if the email is already taken
    function AlreadyUser($db, $mail){
        try {
            $stmt = $db->prepare('SELECT * FROM users where mail=?');
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            return !empty($user);
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }
    
    // Insert a new user in the database
    function dbInsertNewUser($db, $mail, $username, $pwd, $profile_picture){
        try {
            // Check if the email is already taken
            if (AlreadyUser($db, $mail)){
                return "Already";
            }
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (mail, username, password, profile_picture) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $mail, $username, $hash, $profile_picture);
            $stmt->execute();
            return true;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get the user's username
    function dbGetUserInfos($db, $mail){
        try {
            $stmt = $db->prepare('SELECT username FROM users WHERE mail = ?');
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }

    // Get all the songs from the database
    function dbGetSongs($db){
        try {
            $stmt = $db->prepare('SELECT * FROM songs');
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get all playlists of the user
    function dbGetUserPlaylists($db, $mail){
        try {
            $stmt = $db->prepare('SELECT * FROM playlist WHERE mail=?');
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get all songs liked by the user
    function dbGetLikedSongs($db, $mail){
        try {
            $stmt = $db->prepare('SELECT * FROM likes WHERE mail=?');
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Ajouter un log pour vérifier le contenu de $result
            if (empty($result)){
                error_log("Aucune chanson likée trouvée pour l'email : $mail");
            } else {
                error_log("Chansons likées trouvées pour l'email : $mail");
            }

            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return "Error: " . $exception->getMessage();
        }
    }

    // Get album's name via song id
    function dbGetAlbum($db, $id_song){
        try {
            $stmt = $db->prepare('SELECT id_album FROM appartient WHERE id_song = ?');
            $stmt->bind_param('i', $id_song);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error (dbGetAlbum): ' . $exception->getMessage());
            return false;
        }
    }

    function dbGetAlbumName($db, $id_album){
        try {
            $stmt = $db->prepare('SELECT name FROM album WHERE id_album = ?');
            $stmt->bind_param('i', $id_album);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error (dbGetAlbumName): ' . $exception->getMessage());
            return false;
        }
    }

    // Get artist's email via artist id
    function dbGetArtist($db, $id_artist){
        try {
            $stmt = $db->prepare('SELECT mail FROM artist WHERE id_artist = ?');
            $stmt->bind_param('i', $id_artist);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result;
        } catch (mysqli_sql_exception $exception){
            error_log('Request error: ' . $exception->getMessage());
            return false;
        }
    }

    // Function to remove a song from a playlist
    function removeSongFromPlaylist($db, $id_song, $id_playlist){
        try {
            $stmt = $db->prepare("DELETE FROM playlist_songs WHERE id_song = ? AND id_playlist = ?");
            $stmt->bind_param('ii', $id_song, $id_playlist);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $e){
            error_log("Erreur removeSongFromPlaylist: " . $e->getMessage());
            return false;
        }
    }

    // Get liked songs of the user
    function dbGetIdSong($db, $mail){
        try {
            $stmt = $db->prepare("SELECT id_song FROM likes WHERE mail = ?");
            $stmt->bind_param('s', $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbGetIdSong: " . $e->getMessage());
            return false;
        }
    }

    // Function to get song from id
    function dbGetSong($db, $id_song){
        try {
            $stmt = $db->prepare("SELECT * FROM songs WHERE id_song = ?");
            $stmt->bind_param('i', $id_song);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbGetSong: " . $e->getMessage());
            return false;
        }
    }

    // Function to delete a liked song
    function dbDeleteLikedSong($db, $mail, $id_song){
        try {
            $stmt = $db->prepare("DELETE FROM likes WHERE mail = ? AND id_song = ?");
            $stmt->bind_param('si', $mail, $id_song);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbDeleteLikedSong: " . $e->getMessage());
            return false;
        }
    }

    // Function to add a song to the liked songs
    function dbAddLikedSong($db, $mail, $id_song, $like_date){
        try {
            $stmt = $db->prepare("INSERT INTO likes (mail, id_song, like_date) VALUES (?, ?, ?)");
            $stmt->bind_param('sis', $mail, $id_song, $like_date);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbAddLikedSong: " . $e->getMessage());
            return false;
        }
    }

    // Function to get the songs of a playlist
    function dbGetPlaylistSongs($db, $id_playlist){
        try {
            $stmt = $db->prepare("
                SELECT s.id_song, s.name, s.song, s.picture, s.time, s.id_artist
                FROM playlist_songs ps
                JOIN songs s ON ps.id_song = s.id_song
                WHERE ps.id_playlist = ?
            ");
            $stmt->bind_param('i', $id_playlist);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbGetPlaylistSongs: " . $e->getMessage());
            return false;
        }
    }

    // Update the profile picture of a user
    function dbUpdateProfilePicture($db, $mail, $profile_picture){
        try {
            $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE mail = ?");
            $stmt->bind_param('ss', $profile_picture, $mail);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbUpdateProfilePicture: " . $e->getMessage());
            return false;
        }
    }

    // Update the username of a user
    function dbUpdateUsername($db, $mail, $username){
        try {
            $stmt = $db->prepare("UPDATE users SET username = ? WHERE mail = ?");
            $stmt->bind_param('ss', $username, $mail);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbUpdateUsername: " . $e->getMessage());
            return false;
        }
    }

    // Update the password of a user
    function dbUpdatePassword($db, $mail, $password){
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE mail = ?");
            $stmt->bind_param('ss', $hash, $mail);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbUpdatePassword: " . $e->getMessage());
            return false;
        }
    }

    // Delete a user
    function dbDeleteUser($db, $mail){
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE mail = ?");
            $stmt->bind_param('s', $mail);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbDeleteUser: " . $e->getMessage());
            return false;
        }
    }

    // Create a new playlist
    function dbCreatePlaylist($db, $mail, $name){
        try {
            $stmt = $db->prepare("INSERT INTO playlist (mail, playlist_name) VALUES (?, ?)");
            $stmt->bind_param('ss', $mail, $name);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbCreatePlaylist: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete a playlist
    function dbDeletePlaylist($db, $id_playlist){
        try {
            $stmt = $db->prepare("DELETE FROM playlist WHERE id_playlist = ?");
            $stmt->bind_param('i', $id_playlist);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbDeletePlaylist: " . $e->getMessage());
            return false;
        }
    }

    // Delete a song from a playlist
    function dbDeleteSongFromPlaylist($db, $id_playlist, $id_song){
        try {
            $stmt = $db->prepare("DELETE FROM playlist_songs WHERE id_playlist = ? AND id_song = ?");
            $stmt->bind_param("ii", $id_playlist, $id_song);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbRemoveSongFromPlaylist: " . $e->getMessage());
            return false;
        }
    }

    // Get comments of a song
    function dbGetComments($db, $id_song){
        try {
            $stmt = $db->prepare("SELECT * FROM comment WHERE id_song = ?");
            $stmt->bind_param("i", $id_song);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbGetComments: " . $e->getMessage());
            return false;
        }
    }    

    // Add a comment
    function dbAddComment($db, $mail, $id_song, $comment){
        try {
            $stmt = $db->prepare("INSERT INTO comment (mail, id_song, comment, comment_date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sis", $mail, $id_song, $comment);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbAddComment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get the user's role
    function dbCheckRole($db, $mail){
        try {
            // Check if the user is an artist
            $stmt = $db->prepare("SELECT id_artist FROM artist WHERE mail = ?");
            $stmt->bind_param("s", $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if (!empty($result)){
                return "artist";
            }

            // Check if the user is an admin
            $stmt = $db->prepare("SELECT id_admin FROM admin WHERE mail = ?");
            $stmt->bind_param("s", $mail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if (!empty($result)){
                return "admin";
            }

            return false;
        } catch (mysqli_sql_exception $e){
            error_log("Erreur dbCheckRole: " . $e->getMessage());
            return false;
        }
    }


    /*
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

    // function to add a song to the liked songs
    function dbAddLikedSong($db, $mail, $id_song, $like_date) {
        try {
            $stmt = $db->prepare("INSERT INTO likes (mail, id_song, like_date) VALUES (:mail, :id_song, :like_date)");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':id_song', $id_song);
            $stmt->bindParam(':like_date', $like_date);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbAddLikedSong: " . $e->getMessage());
            return false;
        }
    }    

    // function to get the songs of a playlist
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
    
    // function to update the profile picture of a user
    function dbUpdateProfilePicture($db, $mail, $profile_picture) {
        try {
            $stmt = $db->prepare("UPDATE users SET profile_picture = :profile_picture WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':profile_picture', $profile_picture);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbUpdateProfilePicture: " . $e->getMessage());
            return false;
        }
    }

    // function to update the username of a user
    function dbUpdateUsername($db, $mail, $username) {
        try {
            $stmt = $db->prepare("UPDATE users SET username = :username WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbUpdateUsername: " . $e->getMessage());
            return false;
        }
    }

    // function to update the password of a user
    function dbUpdatePassword($db, $mail, $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':password', $hash);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbUpdatePassword: " . $e->getMessage());
            return false;
        }
    }

    // function to delete a user
    function dbDeleteUser($db, $mail) {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbDeleteUser: " . $e->getMessage());
            return false;
        }
    }

    // function to create a new playlist
    function dbCreatePlaylist($db, $mail, $name) {
        try {
            $decodedMail = urldecode($mail); 
            $checkUserStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE mail = :mail");
            $checkUserStmt->bindParam(':mail', $decodedMail);
            $checkUserStmt->execute();
            $userExists = $checkUserStmt->fetchColumn();
            $stmt = $db->prepare("INSERT INTO playlist (mail, playlist_name) VALUES (:mail, :name)");
            $stmt->bindParam(':mail', $decodedMail);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbCreatePlaylist: " . $e->getMessage());
            return false;
        }
    }

    // function to delete a playlist
    function dbDeletePlaylist($db, $id_playlist) {
        try {
            $stmt = $db->prepare("DELETE FROM playlist WHERE id_playlist = :id_playlist");
            $stmt->bindParam(':id_playlist', $id_playlist);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbDeletePlaylist: " . $e->getMessage());
            return false;
        }
    }

    // function to delete a song from a playlist
    function dbDeleteSongFromPlaylist($db, $id_playlist, $id_song) {
        try {
            $stmt = $db->prepare("DELETE FROM playlist_songs WHERE id_playlist = :id_playlist AND id_song = :id_song");
            $stmt->bindParam(':id_playlist', $id_playlist);
            $stmt->bindParam(':id_song', $id_song);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbRemoveSongFromPlaylist: " . $e->getMessage());
            return false;
        }
    }

    // function to get comments of a song
    function dbGetComments($db, $id_song) {
        try {
            $stmt = $db->prepare("SELECT * FROM comment WHERE id_song = :id_song");
            $stmt->bindParam(':id_song', $id_song);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dbGetComments: " . $e->getMessage());
            return false;
        }
    }

    // function to add a comment
    function dbAddComment($db, $mail, $id_song, $comment, $comment_date) {
        try {
            $stmt = $db->prepare("INSERT INTO comment (mail, id_song, comment, comment_date) VALUES (:mail, :id_song, :comment, :comment_date)");
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':id_song', $id_song);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':comment_date', $comment_date);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur dbAddComment: " . $e->getMessage());
            return false;
        }
    }

    // function to get the user's role
    function dbCheckRole($db, $mail) {
        try {
            // Vérifie si l'utilisateur est un artiste
            $stmt = $db->prepare("SELECT id_artist FROM artist WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return "artist";
            }
    
            // Vérifie si l'utilisateur est un admin
            $stmt = $db->prepare("SELECT id_admin FROM admin WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return "admin";
            }
    
            return false;
        } catch (PDOException $e) {
            error_log("Erreur dbCheckRole: " . $e->getMessage());
            return false;
        }
    }
    */


    



    
?>


