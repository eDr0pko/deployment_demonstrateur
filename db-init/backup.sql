CREATE DATABASE IF NOT EXISTS musicDB;
USE musicDB;

DROP TABLE IF EXISTS playlist_songs;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS playlist;
DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS appartient;
DROP TABLE IF EXISTS songs;
DROP TABLE IF EXISTS artist;
DROP TABLE IF EXISTS album;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS users;


-- Création de la table users (avec username au lieu de firstname/lastname)
CREATE TABLE users (
    mail            VARCHAR(100) NOT NULL,
    username        VARCHAR(50) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255),
    CONSTRAINT users_PK PRIMARY KEY (mail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table album
CREATE TABLE album (
    id_album INT AUTO_INCREMENT NOT NULL,
    name     VARCHAR(100) NOT NULL,
    CONSTRAINT album_PK PRIMARY KEY (id_album)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table playlist
CREATE TABLE playlist (
    id_playlist   INT AUTO_INCREMENT NOT NULL,
    playlist_name VARCHAR(100) NOT NULL, 
    mail          VARCHAR(100) NOT NULL,
    CONSTRAINT playlist_PK PRIMARY KEY (id_playlist),
    CONSTRAINT playlist_users_FK FOREIGN KEY (mail) REFERENCES users(mail) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table artist
CREATE TABLE artist (
    id_artist INT AUTO_INCREMENT NOT NULL,
    mail      VARCHAR(100) NOT NULL,
    CONSTRAINT artist_PK PRIMARY KEY (id_artist),
    CONSTRAINT artist_users_FK FOREIGN KEY (mail) REFERENCES users(mail) ON DELETE CASCADE,
    CONSTRAINT artist_users_AK UNIQUE (mail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table songs avec la colonne time
CREATE TABLE songs (
    id_song   INT AUTO_INCREMENT NOT NULL,
    name      VARCHAR(100) NOT NULL,
    song      VARCHAR(255) NOT NULL,
    time      TIME NOT NULL,
    picture   VARCHAR(255) NOT NULL,
    id_artist INT NOT NULL,
    CONSTRAINT songs_PK PRIMARY KEY (id_song),
    CONSTRAINT songs_artist_FK FOREIGN KEY (id_artist) REFERENCES artist(id_artist) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table admin
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT NOT NULL,
    mail     VARCHAR(100) NOT NULL,
    CONSTRAINT admin_PK PRIMARY KEY (id_admin),
    CONSTRAINT admin_users_FK FOREIGN KEY (mail) REFERENCES users(mail) ON DELETE CASCADE,
    CONSTRAINT admin_users_AK UNIQUE (mail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table likes
CREATE TABLE likes (
    id_song   INT NOT NULL,
    mail      VARCHAR(100) NOT NULL,
    like_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    notice    VARCHAR(200),
    CONSTRAINT likes_PK PRIMARY KEY (id_song, mail),
    CONSTRAINT likes_songs_FK FOREIGN KEY (id_song) REFERENCES songs(id_song) ON DELETE CASCADE,
    CONSTRAINT likes_users0_FK FOREIGN KEY (mail) REFERENCES users(mail) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table comment
CREATE TABLE comment (
    mail         VARCHAR(100) NOT NULL,
    id_song      INT NOT NULL,
    comment      VARCHAR(500) NOT NULL,
    comment_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT comment_PK PRIMARY KEY (mail, id_song),
    CONSTRAINT comment_users_FK FOREIGN KEY (mail) REFERENCES users(mail) ON DELETE CASCADE,
    CONSTRAINT comment_song0_FK FOREIGN KEY (id_song) REFERENCES songs(id_song) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table appartient (associe chansons et albums)
CREATE TABLE appartient (
    id_song  INT NOT NULL,
    id_album INT NOT NULL,
    CONSTRAINT appartient_PK PRIMARY KEY (id_song, id_album),
    CONSTRAINT appartient_songs_FK FOREIGN KEY (id_song) REFERENCES songs(id_song) ON DELETE CASCADE,
    CONSTRAINT appartient_album0_FK FOREIGN KEY (id_album) REFERENCES album(id_album) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table playlist_songs (associe chansons et playlists)
CREATE TABLE playlist_songs (
    id_song     INT NOT NULL,
    id_playlist INT NOT NULL,
    add_date    DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT playlist_songs_PK PRIMARY KEY (id_song, id_playlist),
    CONSTRAINT playlist_songs_songs_FK FOREIGN KEY (id_song) REFERENCES songs(id_song) ON DELETE CASCADE,
    CONSTRAINT playlist_songs_playlist0_FK FOREIGN KEY (id_playlist) REFERENCES playlist(id_playlist) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des utilisateurs (y compris l'admin et utilisateurs simples)
INSERT INTO users (mail, username, password, profile_picture) VALUES
('admin@admin.com', 'admin', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_user.png'),
('user1@user.com', 'Franky', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/perceuse01.jpg'),
('user2@user.com', 'Lerat', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_user.png'),
('user3@user.com', 'Jacky', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_user.png');

-- Insertion de l'administrateur
INSERT INTO admin (mail) VALUES
('admin@admin.com');

-- Insertion des artistes EDM (les artistes sont également des utilisateurs)
INSERT INTO users (mail, username, password, profile_picture) VALUES
('avicii@artist.com', 'Avicii', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_artist.png'),
('martingarrix@artist.com', 'Martin Garrix', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_artist.png'),
('calvinharris@artist.com', 'Calvin Harris', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_artist.png'),
('davidguetta@artist.com', 'David Guetta', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_artist.png'),
('kygo@artist.com', 'Kygo', '$2y$10$ZrzU4mTG7GtqsY8LKKg0.uZfpCgo1X2.SromWlOK8iEvp..2v/OES', '../images/default_artist.png');

-- Déclaration des artistes dans la table artist
INSERT INTO artist (mail) VALUES
('avicii@artist.com'),
('martingarrix@artist.com'),
('calvinharris@artist.com'),
('davidguetta@artist.com'),
('kygo@artist.com');

-- Insertion des albums
INSERT INTO album (name) VALUES
('EDM Classics'),
('Summer Hits'),
('Festival Anthems');

-- Insertion des chansons
-- La colonne "time" indique la durée de la chanson au format HH:MM:SS
INSERT INTO songs (name, song, time, picture, id_artist) VALUES
('Wake Me Up', 'avicci_wake_me_up.mp3', '00:04:10', '../../images/wake_me_up.jpg', 1),
('Levels', 'avicci_levels.mp3', '00:03:30', '../../images/levels.jpg', 1),
('Scared to Be Lonely', 'scared_to_be_lonely.mp3', '00:03:45', '../../images/scared_to_be_lonely.jpg', 2),
('Animals', 'animals.mp3', '00:04:00', '../../images/animals.jpg', 2),
('Summer', 'summer.mp3', '00:03:20', '../../images/summer.png', 3),
('Feel So Close', 'feel_so_close.mp3', '00:03:15', '../../images/feel_so_close.jpg', 3),
('Titanium', 'titanium.mp3', '00:04:05', '../../images/titanium.jpg', 4),
('Play Hard', 'play_hard.mp3', '00:03:50', '../../images/play_hard.jpg', 4),
('Firestone', 'firestone.mp3', '00:03:40', '../../images/firestone.jpg', 5),
('Stole the Show', 'stole_the_show.mp3', '00:04:00', '../../images/stole_the_show.jpg', 5);

-- Associer des chansons aux albums
INSERT INTO appartient (id_song, id_album) VALUES
(1, 1), (2, 1), (3, 2), (4, 2), (5, 3), (6, 3), (7, 1), (8, 2), (9, 3), (10, 1);

-- Insertion des playlists
-- user1 a deux playlists, user2 en a une et user3 n'en a aucune
INSERT INTO playlist (playlist_name, mail) VALUES
('Best of Avicii', 'user1@user.com'),
('Party Mix', 'user1@user.com'),
('Chill EDM', 'user2@user.com');

-- Associer des chansons aux playlists
INSERT INTO playlist_songs (id_song, id_playlist) VALUES
(1, 1), (2, 1), (3, 2), (5, 2), (7, 3), (9, 3);

-- Ajout de likes pour les utilisateurs
INSERT INTO likes (id_song, mail, notice) VALUES
(1, 'user1@user.com', 'Une pure merveille !'),
(2, 'user1@user.com', 'Énorme classique.'),
(5, 'user2@user.com', 'Parfait pour l\'été !');

-- Ajout de commentaires
INSERT INTO comment (mail, id_song, comment) VALUES
('user1@user.com', 1, 'Toujours aussi bon, même après des années !'),
('user2@user.com', 5, 'Calvin Harris sait comment faire vibrer !');