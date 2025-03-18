<?php
    include 'database.php';


    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $db = database::connexionBD();

    if (!isset($db) || $db === null) {
        die(json_encode(["success" => false, "message" => "Connexion à la base de données échouée."]));
    }


    // function to insert a new user
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
        if ($_POST["action"] === "register" && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["username"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            $username = $_POST["username"];
            $profile_picture = "images/default_user.png";
            $result = dbInsertNewUser($db, $email, $username, $password, $profile_picture);
            
            // Check if the email is already taken
            if ($result === "Already") {
                echo json_encode(["success" => false, "message" => "L'email est déjà pris."]);
            } elseif ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription."]);
            }
        }
    }
    
    // function to login
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
        if ($_POST["action"] === "login" && isset($_POST["email"]) && isset($_POST["password"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            $result = dbGetUser($db, $email, $password);
            if ($result !== "error") {
                // Définition des cookies pour stocker le nom et prénom (valide pour 1 jour)
                setcookie("username", $result['username'], time() + 86400, "/");
                setcookie("mail", $result['mail'], time() + 86400, "/");
                setcookie("profile_picture", $result['profile_picture'], time() + 86400, "/");
            
                echo json_encode(["success" => true, "user" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "E-mail ou mot de passe incorrect."]);
            }
        }
    }

    // function for print all songs
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getSongs") {
            $result = dbGetSongs($db);
            if ($result !== false) {
                echo json_encode(["success" => true, "songs" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des chansons."]);
            }
        }
    }

    // function for print all playlists of the user
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getPlaylists") {
            if (isset($_COOKIE['mail'])) {
                $result = dbGetUserPlaylists($db, $_COOKIE['mail']);
                if ($result !== false) {
                    echo json_encode(["success" => true, "playlists" => $result]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des playlists."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
            }
        }
    }

    // Action pour récupérer les chansons d'une playlist
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getPlaylistSongs") {
            if (isset($_GET["id_playlist"])) {
                $result = dbGetPlaylistSongs($db, $_GET["id_playlist"]);
                if ($result !== false) {
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



    // function for print all liked songs of the user
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getLikedSongs") {
            // On ne se base plus sur la session, mais sur le cookie (ici, user_id)
            if (isset($_COOKIE['mail'])) {
                $result = dbGetLikedSongs($db, $_COOKIE['mail']);
                // Si $result est false ou vide, on renvoie quand même un tableau vide
                if ($result !== false && !empty($result)) {
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

    // function to get the album's name of the song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getAlbumName") {
            if (isset($_GET["id_song"])) {
                $result = dbGetAlbum($db, $_GET["id_song"]);
                if ($result !== false) {
                    $result2 = dbGetAlbumName($db, $result['id_album']);
                    if ($result2 !== false) {
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

    // function to print the artiste's name of the song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getArtistName") {
            if (isset($_GET["id_artist"])) {
                $result = dbGetArtist($db, $_GET["id_artist"]);
                if ($result !== false) {
                    $result2 = dbGetUserInfos($db, $result['mail']);
                    if ($result2 !== false) {
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

    // function to get user's liked song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        if ($_GET["action"] === "getLikedSong") {
            if (isset($_GET["mail"])) {
                $likedSongs = dbGetIdSong($db, $_GET["mail"]);
    
                if ($likedSongs !== false && count($likedSongs) > 0) {
                    $songs = [];
    
                    foreach ($likedSongs as $song) {
                        $songData = dbGetSong($db, $song['id_song']);
                        if ($songData !== false) {
                            $songs[] = $songData;
                        }
                    }
    
                    if (!empty($songs)) {
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

    // function to delete a liked song
    if (isset($_GET["action"]) && $_GET["action"] === "removeLikedSong") {
        if (isset($_GET["mail"]) && isset($_GET["id_song"])) {
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
    
    // function to add a song to a liked song
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
        if ($_POST["action"] === "addLike") {
            if (isset($_POST["mail"]) && isset($_POST["id_song"]) && isset($_POST["like_date"])) {
                $result = dbAddLikedSong($db, $_POST["mail"], $_POST["id_song"], $_POST["like_date"]);
                if ($result === true) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout de la chanson likée."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Mail ou id de la chanson non fourni."]);
            }
        }
    }
    /*
    // funnction to remove a song from a playlist
    if ($_POST['action'] == 'deleteSongFromPlaylist') {
        if (isset($_POST['id_song']) && isset($_POST['id_playlist'])) {
            $result = dbDeleteSongFromPlaylist($db, $_POST['id_song'], $_POST['id_playlist']);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la chanson de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la chanson ou de la playlist non fourni."]);
        }
    }
    */
    // Update user's profile
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "updateProfile") {
        $mail = $_POST["mail"] ?? null;
        $username = $_POST["username"] ?? null;
        $password = $_POST["password"] ?? null;
        $profilePicture = $_FILES["profile_picture"] ?? null; // Pour gérer un fichier
    
        if (!$mail) {
            echo json_encode(["success" => false, "message" => "Adresse mail requise."]);
            exit;
        }
    
        $success = false;
    
        if ($username) {
            $success = dbUpdateUsername($db, $mail, $username);
        }
    
        if ($password) {
            $success = dbUpdatePassword($db, $mail, $password);
        }
    
        if ($profilePicture) {
            // Gestion de l'upload
            $targetDir = "../uploads/";
            $fileName = basename($profilePicture["name"]);
            $targetFilePath = $targetDir . $fileName;
    
            if (move_uploaded_file($profilePicture["tmp_name"], $targetFilePath)) {
                $success = dbUpdateProfilePicture($db, $mail, $targetFilePath);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors du téléchargement de l'image."]);
                exit;
            }
        }
    
        if ($success) {
            echo json_encode(["success" => true, "message" => "Profil mis à jour avec succès."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour du profil."]);
        }
    }

    // function to delete a user
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (isset($data["action"]) && $data["action"] === "deleteAccount") {
            if (isset($data["mail"])) {
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

    // function to create a new playlist
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"]) && $_GET["action"] === "createPlaylist") {
        if (isset($_POST["mail"]) && isset($_POST["playlist_name"])) {  
            if (isset($_POST["playlist_name"]) && !empty(trim($_POST["playlist_name"]))) {  
                $playlistName = trim($_POST["playlist_name"]);
            } else {
                echo json_encode(["success" => false, "message" => "Le nom de la playlist ne peut pas être vide."]);
                exit;
            }
            
            $result = dbCreatePlaylist($db, $_POST["mail"], $_POST["playlist_name"]);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la création de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Mail ou nom de la playlist non fourni."]);
        }
    } 

    // function to delete a playlist
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "deletePlaylist") {
        if (isset($_GET["id_playlist"])) {
            $id_playlist = $_GET["id_playlist"];
            $result = dbDeletePlaylist($db, $id_playlist);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la playlist non fourni."]);
        }
    }
    
    // function to get comments of a song
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "getComments") {
        if (isset($_GET["id_song"])) {
            $result = dbGetComments($db, $_GET["id_song"]);
            if ($result !== false) {
                echo json_encode(["success" => true, "comments" => $result]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la récupération des commentaires."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la chanson non fourni."]);
        }
    }

    // function to add a comment
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "addComment") {
        if (isset($_POST["mail"]) && isset($_POST["id_song"]) && isset($_POST["comment"]) && isset($_POST["comment_date"])) {
            $result = dbAddComment($db, $_POST["mail"], $_POST["id_song"], $_POST["comment"], $_POST["comment_date"]);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout du commentaire."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Mail, id de la chanson ou commentaire non fourni."]);
        }
    }

    // function to check user's role
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "checkUserType") {
        if (isset($_COOKIE['mail'])) {
            $role = dbCheckRole($db, $_COOKIE['mail']);
            if ($role !== false) {
                echo json_encode(["success" => true, "role" => $role]);
            } else {
                echo json_encode(["success" => false, "message" => "Utilisateur non artiste ou admin."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
        }
    }
?>


