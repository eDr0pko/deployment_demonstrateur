<?php
    include 'database.php';

    error_reporting(E_ALL);
    ini_set('display_errors', 1);


    $db = database::connexionBD();

    if (!isset($db) || $db === null){
        die(json_encode(["success" => false, "message" => "Connexion à la base de données échouée."]));
    }


    // Function to insert a new user
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])){
        if ($_POST["action"] === "register" && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["username"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            $username = $_POST["username"];
            $profile_picture = "images/default_user.png";
            $result = dbInsertNewUser($db, $email, $username, $password, $profile_picture);
            
            // Check if the email is already taken
            if ($result === "Already"){
                echo json_encode(["success" => false, "message" => "L'email est déjà pris."]);
            } elseif ($result === true){
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription."]);
            }
        }
    }
    
    // Function to login
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])){
        if ($_POST["action"] === "login" && isset($_POST["email"]) && isset($_POST["password"])){
            $email = $_POST["email"];
            $password = $_POST["password"];
            $result = dbGetUser($db, $email, $password);
            if ($result !== "error"){
                setcookie("username", $result['username'], time() + 86400, "/");
                setcookie("mail", $result['mail'], time() + 86400, "/");
                setcookie("profile_picture", $result['profile_picture'], time() + 86400, "/");
            
                echo json_encode(["success" => true, "user" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "E-mail ou mot de passe incorrect."]);
            }
        }
    }

    // Function for print all songs
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getSongs"){
            $result = dbGetSongs($db);
            if ($result !== false){
                echo json_encode(["success" => true, "songs" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des chansons."]);
            }
        }
    }

    // Function for print all playlists of the user
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getPlaylists"){
            if (isset($_COOKIE['mail'])){
                $result = dbGetUserPlaylists($db, $_COOKIE['mail']);
                if ($result !== false){
                    echo json_encode(["success" => true, "playlists" => $result]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des playlists."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
            }
        }
    }

    // Function for print all songs of the playlist
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getPlaylistSongs"){
            if (isset($_GET["id_playlist"])){
                $result = dbGetPlaylistSongs($db, $_GET["id_playlist"]);
                if ($result !== false){
                    echo json_encode(["success" => true, "songs" => $result]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des chansons de la playlist."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Identifiant de la playlist non fourni."]);
            }
            exit;
        }
    }



    // Function for print all liked songs of the user
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getLikedSongs"){
            if (isset($_COOKIE['mail'])){
                $result = dbGetLikedSongs($db, $_COOKIE['mail']);
                if ($result !== false && !empty($result)){
                    echo json_encode(["success" => true, "likedSongs" => $result]);
                } else {
                    echo json_encode(["success" => true, "likedSongs" => []]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
            }
            exit;
        }
    }

    // Function to get the album's name of the song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getAlbumName"){
            if (isset($_GET["id_song"])){
                $result = dbGetAlbum($db, $_GET["id_song"]);
                if ($result !== false){
                    $result2 = dbGetAlbumName($db, $result['id_album']);
                    if ($result2 !== false){
                        echo json_encode(["success" => true, "albumName" => $result2['name']]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Erreur lors de la récupération du nom de l'album."]);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération de l'id de l'album."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "id du son non fourni."]);
            }
            exit;
        }
    }

    // Function to print the artiste's name of the song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getArtistName"){
            if (isset($_GET["id_artist"])){
                $result = dbGetArtist($db, $_GET["id_artist"]);
                if ($result !== false){
                    $result2 = dbGetUserInfos($db, $result['mail']);
                    if ($result2 !== false){
                        echo json_encode(["success" => true, "artistName" => $result2['username']]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des infos utilisateur."]);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération du mail de l'artiste."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Identifiant de l'artiste non fourni."]);
            }
            exit;
        }
    }

    // Function to get user's liked song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])){
        if ($_GET["action"] === "getLikedSong"){
            if (isset($_GET["mail"])){
                $likedSongs = dbGetIdSong($db, $_GET["mail"]);
    
                if ($likedSongs !== false && count($likedSongs) > 0){
                    $songs = [];
    
                    foreach ($likedSongs as $song){
                        $songData = dbGetSong($db, $song['id_song']);
                        if ($songData !== false){
                            $songs[] = $songData;
                        }
                    }
    
                    if (!empty($songs)){
                        header('Content-Type: application/json');
                        echo json_encode(["success" => true, "songs" => $songs]);
                        exit;
                    } else {
                        echo json_encode(["success" => false, "message" => "Aucune chanson likée trouvée."]);
                        exit;
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des chansons likées."]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Mail non fourni."]);
                exit;
            }
        }
    }

    // Function to delete a liked song
    if (isset($_GET["action"]) && $_GET["action"] === "removeLikedSong"){
        if (isset($_GET["mail"]) && isset($_GET["id_song"])){
            $result = dbDeleteLikedSong($db, $_GET["mail"], $_GET["id_song"]);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la chanson likée."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Mail ou id de la chanson non fourni."]);
        }
    }
    
    // Function to add a song to a liked song
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])){
        if ($_POST["action"] === "addLike") {
            if (isset($_POST["mail"]) && isset($_POST["id_song"]) && isset($_POST["like_date"])) {
                $result = dbAddLikedSong($db, $_POST["mail"], $_POST["id_song"], $_POST["like_date"]);
                if ($result === true){
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout de la chanson likée."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Mail ou id de la chanson non fourni."]);
            }
        }
    }
    
    // Function to remove a song from a playlist
    if (isset($_POST['action']) && $_POST['action'] == 'deleteSongFromPlaylist'){
        if (isset($_POST['id_song']) && isset($_POST['id_playlist'])){
            $result = dbDeleteSongFromPlaylist($db, $_POST['id_playlist'], $_POST['id_song']);
            echo json_encode(["success" => $result]);
        } else {
            echo json_encode(["success" => false, "message" => "ID chanson ou playlist manquant."]);
        }
        exit();
    }    
    
    // Function to update a profile
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "updateProfile"){
        $mail = $_POST["mail"] ?? null;
        $username = $_POST["username"] ?? null;
        $password = $_POST["password"] ?? null;
        $profilePicture = $_FILES["profile_picture"] ?? null;

        error_log("mail: $mail, username: $username, password: $password");
        $success = false;
        if ($username){
            $success = dbUpdateUsername($db, $mail, $username);
            setcookie("username", $username, time() + 86400, "/");
        }
        if ($password){
            $success = dbUpdatePassword($db, $mail, $password);
        }
        if ($profilePicture){
            $target_dir = "images/";
            $targetFilePath = $target_dir . basename($profilePicture["name"]);
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array($fileType, $allowTypes)){
                $success = dbUpdateProfilePicture($db, $mail, $targetFilePath);
                setcookie("profile_picture", $targetFilePath, time() + 86400, "/");
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors du téléchargement de l'image."]);
                exit;
            }
        }
    
        if ($success){
            echo json_encode(["success" => true, "message" => "Profil mis à jour avec succès."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour du profil."]);
        }
    }

    // Function to delete a user
    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (isset($data["action"]) && $data["action"] === "deleteAccount"){
            if (isset($data["mail"])){
                $result = dbDeleteUser($db, $data["mail"]);
                if ($result === true) {
                    echo json_encode(["success" => true, "message" => "Compte supprimé avec succès."]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de l'utilisateur."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Mail non fourni."]);
            }
        }
    }

    // Function to create a new playlist
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "createPlaylist") {
        if (!empty($_POST["mail"]) && !empty(trim($_POST["playlist_name"]))) {  
            $playlistName = trim($_POST["playlist_name"]);
            $result = dbCreatePlaylist($db, $_POST["mail"], $playlistName);
            
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la création de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Mail ou nom de la playlist non fourni ou vide."]);
        }
        exit();
    }    

    // Function to delete a playlist
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "deletePlaylist"){
        if (isset($_GET["id_playlist"])){
            $id_playlist = $_GET["id_playlist"];
            $result = dbDeletePlaylist($db, $id_playlist);
            if ($result === true){
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la playlist non fourni."]);
        }
    }

    // Function to get comments of a song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "getComments"){
        if (isset($_GET["id_song"])){
            $result = dbGetComments($db, $_GET["id_song"]);
            if ($result !== false){
                echo json_encode(["success" => true, "comments" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des commentaires."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la chanson non fourni."]);
        }
    }
/*
    // Function to add a comment
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])){
        if (isset($_POST["id_song"]) && isset($_POST["comment"])){
            $result = dbAddComment($db, $_COOKIE['mail'], $_POST["id_song"], $_POST["comment"]);
            if ($result === true){
                echo json_encode(["success" => true]);
            } else {;
                echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout du commentaire V2."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Mail, id de la chanson ou commentaire non fourni."]);
        }
    }
*/
    // Function to check user's role
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "checkUserType"){
        if (isset($_COOKIE['mail'])){
            $role = dbCheckRole($db, $_COOKIE['mail']);
            if ($role !== false){
                echo json_encode(["success" => true, "role" => $role]);
            } else {
                echo json_encode(["success" => false, "message" => "Utilisateur non artiste ou admin."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
        }
    }
?>

<?php
    /*----Live Search----*/
    function live_search($db, $input){
        $queries = [
            "users" => "SELECT mail, username FROM users WHERE username LIKE '%$input%' OR mail LIKE '%$input%'",        
            "songs" => "SELECT id_song, name, song FROM songs WHERE id_song LIKE '%$input%' OR name LIKE '%$input%' OR song LIKE '%$input%'",
            "playlist" => "SELECT id_playlist, playlist_name, mail FROM playlist WHERE id_playlist LIKE '%$input%' OR playlist_name LIKE '%$input%' OR mail LIKE '%$input%'",
            "likes" => "SELECT id_song, mail FROM likes WHERE id_song LIKE '%$input%' OR mail LIKE '%$input%'",
            "comment" => "SELECT mail, id_song, comment FROM comment WHERE mail LIKE '%$input%' OR id_song LIKE '%$input%' OR comment LIKE '%$input%'",
            "artist" => "SELECT id_artist, mail FROM artist WHERE id_artist LIKE '%$input%' OR mail LIKE '%$input%'",
            "appartient" => "SELECT id_song, id_album FROM appartient WHERE id_song LIKE '%$input%' OR id_album LIKE '%$input%'",
            "album" => "SELECT id_album, name FROM album WHERE id_album LIKE '%$input%' OR name LIKE '%$input%'",
            "admin" => "SELECT id_admin, mail FROM admin WHERE id_admin LIKE '%$input%' OR mail LIKE '%$input%'"
        ];
    
        $results_found = false;
    
        foreach ($queries as $table => $sql){
            $result = mysqli_query($db, $sql);
            
            if (mysqli_num_rows($result) > 0){
                while ($row = mysqli_fetch_assoc($result)){
                    $email = $row['mail'] ?? "";
                    $id_song = $row['id_song'] ?? "";
                    $id_album = $row['id_album'] ?? "";
    
                    echo "<p class='search-result' data-table='$table' data-email='$email' data-id_song='$id_song' data-id_album='$id_album'>";
                    foreach ($row as $column => $value){
                        $highlighted = str_ireplace($input, "<span class='highlight'>$input</span>", $value);
                        echo "$highlighted ";
                    }
                    echo "</p>";
                }
                $results_found = true;
            }
        }
        if (!$results_found){
            echo "<p>Aucun résultat trouvé</p>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == "live_search") {
        $input = $_POST['search'];
        live_search($db, $input);
    }

    function add_details($db, $table, $email, $id_song, $id_album) {
        if($email != ''){
            //on recupere le id_song et id_album
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == "add_details") {
        $table = $_POST['table'];
        $email = $_POST['email'];
        $id_song = $_POST['id_song'];
        $id_album = $_POST['id_album'];

        add_details($db, $table, $email, $id_song, $id_album);

        echo "Table: " . htmlspecialchars($table) . "<br>";
        echo "Email: " . htmlspecialchars($email). "<br>";
        echo "Id song: " . htmlspecialchars($id_song) . "<br>";
        echo "Id album: " . htmlspecialchars($id_album);
    }


    /*----tab choose----*/

    function load_tab_data($db, $input){

        $sql = "SELECT * FROM $input ";
        $result = mysqli_query($db, $sql);

        if($input == "users"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Mail (PK)</th><th>username</th><th>password</th><th>profile_picture</th></tr></thead>"; 
                while ($row = mysqli_fetch_assoc($result)) { 
                    echo "<tr>";            
                    echo "<th>$row[mail]</th>";
                    echo "<td> $row[username] </td>";
                    echo "<td> $row[password] </td>";
                    echo "<td> $row[profile_picture] </td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        elseif($input == "songs"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_song</th><th>name</th><th>song</th><th>picture</th><th>Id_artist</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th scope=\"row\">$row[id_song]</th>";
                    echo "<td> $row[name] </td>";
                    echo "<td> $row[song] </td>";
                    echo "<td> $row[picture] </td>";
                    echo "<td> $row[id_artist] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "playlist_songs"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_song</th><th>Id_playlist</th><th>Add_date</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_song]</th>";
                    echo "<td> $row[id_playlist] </td>";
                    echo "<td> $row[add_date] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "playlist"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>id_playlist</th><th>playlist_name</th><th>mail</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_playlist]</th>";
                    echo "<td> $row[playlist_name] </td>";
                    echo "<td> $row[mail] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "likes"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_song</th><th>Mail</th><th>Like_date</th><th>Notice</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_song]</th>";
                    echo "<td> $row[mail] </td>";
                    echo "<td> $row[like_date] </td>";
                    echo "<td> $row[notice] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "comment"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Mail</th><th>Id_song</th><th>Comment</th><th>Comment_date</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[mail]</th>";
                    echo "<td> $row[id_song] </td>";
                    echo "<td> $row[comment] </td>";
                    echo "<td> $row[comment_date] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "artist"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_artist</th><th>Mail</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_artist]</th>";
                    echo "<td> $row[mail] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "appartient"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_song</th><th>Id_album</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_song]</th>";
                    echo "<td> $row[id_album] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "album"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_album</th><th>Name</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_album]</th>";
                    echo "<td> $row[name] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        elseif($input == "admin"){
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<thead><tr><th>Id_admin</th><th>Mail</th></tr></thead>";
                while ($row = mysqli_fetch_assoc($result)) {  
                    echo "<tr>";            
                    echo "<th>$row[id_admin]</th>";
                    echo "<td> $row[mail] </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == "load_tab_data") {
        $input = $_POST['tableau-option'];
        load_tab_data($db, $input);
    }
?>

