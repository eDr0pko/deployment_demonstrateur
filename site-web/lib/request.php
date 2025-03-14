<?php
    //session_start();

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
            $profile_picture = "../images/default_user.png";
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
    
    
    
    








    // funnction to remove a song from a playlist
    /*
    if ($_POST['action'] == 'removeSongFromPlaylist') {
        if (isset($_POST['id_playlist']) && isset($_POST['id_song'])) {
            $result = dbRemoveSongFromPlaylist($db, $_POST['id_playlist'], $_POST['id_song']);
            if ($result === true) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la chanson de la playlist."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Identifiant de la playlist ou de la chanson non fourni."]);
        }
    }*/

    









    
    if (isset($_POST['action']) && $_POST['action'] == 'addLike') {
        // Récupérer les données envoyées
        $songId = $_POST['songId'];
        $userMail = $_POST['userMail'];
        
        // Vérifier si l'utilisateur a déjà liké cette chanson
        $checkQuery = "SELECT * FROM likes WHERE id_song = ? AND mail = ?";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$songId, $userMail]);
        
        if ($stmt->rowCount() == 0) {
            // Ajouter la chanson dans la table likes
            $insertQuery = "INSERT INTO likes (id_song, mail) VALUES (?, ?)";
            $stmt = $pdo->prepare($insertQuery);
            $stmt->execute([$songId, $userMail]);
            
            // Répondre avec succès
            echo json_encode(['success' => true]);
        } else {
            // L'utilisateur a déjà liké cette chanson
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà liké cette chanson.']);
        }
    }
    
    

    /*
    // Mise à jour du profil
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'updateProfile') {
        if ($userMail) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $profilePicture = isset($_FILES['profile_picture']) ? $_FILES['profile_picture'] : null;

            // Hash du mot de passe si un nouveau mot de passe est fourni
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            }

            // Mise à jour du nom d'utilisateur et du mot de passe
            $result = updateProfile($userMail, $username, isset($passwordHash) ? $passwordHash : null, $profilePicture);

            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
        }
    }

    // Suppression du compte
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'deleteAccount') {
        if ($userMail) {
            $result = deleteAccount($userMail);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
        }
    }
    */

?>


