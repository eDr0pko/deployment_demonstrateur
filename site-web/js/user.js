document.addEventListener('DOMContentLoaded', function(){
    // Get the users' information from the cookies
    let username = getCookie('username');
    const profilePicture = getCookie('profile_picture');
    let usermail = getCookie('mail');

    // Print the username
    if (username) {
        let userNameElement = document.getElementById('user-name');
        userNameElement.textContent = `${username}`;
    } else{
        console.log("Nom d'utilisateur introuvable.");
    }

    // Print the profile picture partir depuis la racine du site et non depuis le dossier pages
    if (profilePicture) {
        //let profilePictureElement = document.getElementById('profile-picture');
        //profilePictureElement.src = profilePicture.replace('../', '');
    } else{
        console.log("Photo de profil introuvable.");
    }

    // Get the user's playlists
    if (usermail){
        fetch(`../lib/request.php?action=getPlaylists`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let playlistsElement = document.querySelector('.playlists');
                data.playlists.forEach(playlist => {
                    let playlistElement = document.createElement('div');
                    playlistElement.classList.add('card-playlist');
                    playlistElement.innerHTML = `
                        <div class="card-content">
                            <h3 class="card-title">${playlist.playlist_name}</h3>
                        </div>
                    `;

                    playlistElement.addEventListener('click', function(){
                        showPlaylistSongs(playlist.id_playlist, playlist.playlist_name);
                    });
                    playlistsElement.appendChild(playlistElement);
                });
            } else{
                console.error('Erreur lors de la récupération des playlists');
            }
        })
        .catch(error => console.error("Erreur lors de la récupération des playlists :", error));
    } else{
        console.log("Utilisateur non connecté.");
    }

    // Function to show the songs of a playlist
    function showPlaylistSongs(id_playlist, playlistName) {
        console.log("Affichage de la playlist :", playlistName, "ID:", id_playlist);
    
        fetch(`../lib/request.php?action=getPlaylistSongs&id_playlist=${id_playlist}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.text())
        .then(text => {
            console.log("Réponse brute du serveur :", text);
            return JSON.parse(text);
        })
        .then(async (data) => {
            console.log("Données JSON parsées :", data);
            if (data.success) {
                console.log("Chansons trouvées :", data.songs.length);
    
                let oldModal = document.querySelector('.modal');
                if (oldModal) {
                    oldModal.remove();
                }
    
                let modal = document.createElement('div');
                modal.classList.add('modal');
                modal.innerHTML = `
                    <div class="modal-content">
                        <span class="close-button">&times;</span>
                        <h2>${playlistName}</h2>
                        <div class="playlist-songs"></div>
                    </div>
                `;
                document.body.appendChild(modal);
    
                let songsContainer = modal.querySelector('.playlist-songs');
    
                if (!songsContainer) {
                    console.error("Impossible de trouver '.playlist-songs' dans la modal");
                    return;
                }
    
                if (data.songs.length > 0) {
                    
    
                    // Affichage des chansons avec le bon nom d'artiste
                    data.songs.forEach(async song => {
                        // Récupérer le nom de l'artiste
                        if (song.id_artist){
                            try{
                                let artistResponse = await fetch(`../lib/request.php?action=getArtistName&id_artist=${song.id_artist}`);
                                let artistData = await artistResponse.json();
                                song.artist = artistData.success ? artistData.artistName : "Artiste inconnu";
                            } catch (error){
                                console.error("Erreur lors de la récupération de l'artiste :", error);
                                song.artist = "Artiste inconnu";
                            }
                        } else{
                            song.artist = "Artiste inconnu";
                        }
    
                        let songDiv = document.createElement('div');
                        songDiv.classList.add('playlist-song');
                        songDiv.innerHTML = `
                            <p>${song.name} - <span class="artist-name">${song.artist}</span></p>
                            <button class="delete-button" data-song-id="${song.id_song}">Supprimer</button>
                        `;
                        songsContainer.appendChild(songDiv);
    
                        songDiv.querySelector('.delete-button').addEventListener('click', function() {
                            deleteSongFromPlaylist(song.id_song, id_playlist);
                        });
    
                        songDiv.addEventListener('click', function() {
                            playSong(song.id_song);
                        });
                    });
                } else {
                    console.warn("Aucune chanson trouvée pour cette playlist.");
                    songsContainer.innerHTML = "<p>Aucune chanson dans cette playlist.</p>";
                }
    
                modal.querySelector('.close-button').addEventListener('click', function() {
                    modal.remove();
                });
            } else {
                console.error("Erreur :", data.message);
            }
        })
        .catch(error => console.error("Erreur lors de la récupération des chansons de la playlist :", error));
    }
    
    // Function to play a song
    let songsElement = document.getElementById('songs');
    let musicFooter = document.querySelector('.music-footer');
    let musicImage = musicFooter.querySelector('img');
    let musicTitle = musicFooter.querySelector('span');
    let playButton = musicFooter.querySelector('button:nth-child(2)')
    let prevButton = musicFooter.querySelector('button:nth-child(1)');
    let nextButton = musicFooter.querySelector('button:nth-child(3)');
    let likeButton = musicFooter.querySelector('.like-button');
    let addToPlaylistButton = musicFooter.querySelector('.add-to-playlist-button');
    let audio = new Audio();
    let currentSongIndex = 0;
    let songsList = [];

    // Get the songs from the server
    fetch('../lib/request.php?action=getSongs')
    .then(response => response.text())
    .then(text => {
        console.log('Réponse brute du serveur :', text);
        return JSON.parse(text);
    })
    .then(async (data) => {
        if (data.success){
            let songsElement = document.getElementById('songs');
            songsList = data.songs;

            for (let song of songsList){
                // Get the album name
                if (song.id_song){
                    try{
                        let albumResponse = await fetch(`../lib/request.php?action=getAlbumName&id_song=${song.id_song}`);
                        let albumData = await albumResponse.json();
                        song.album = albumData.success ? albumData.albumName : "Aucun album";
                    } catch (error){
                        console.error("Erreur lors de la récupération de l'album :", error);
                        song.album = "Aucun album";
                    }
                } else{
                    song.album = "Aucun album";
                }

                // Get the artist name
                if (song.id_artist){
                    try{
                        let artistResponse = await fetch(`../lib/request.php?action=getArtistName&id_artist=${song.id_artist}`);
                        let artistData = await artistResponse.json();
                        song.artist = artistData.success ? artistData.artistName : "Artiste inconnu";
                    } catch (error){
                        console.error("Erreur lors de la récupération de l'artiste :", error);
                        song.artist = "Artiste inconnu";
                    }
                } else{
                    song.artist = "Artiste inconnu";
                }

                // Create the song element
                let songElement = document.createElement('div');
                songElement.classList.add('card-musique');
                songElement.innerHTML = `
                    <img src="${song.picture.replace('../', '')}" alt="${song.name}" class="card-img">
                    <h3 class="card-title">${song.name}</h3>
                    <h3 class="card-album">${song.album}</h3>
                    <h3 class="card-singer">${song.artist}</h3>
                    <h3 class="card-play">
                        <button class="play-button" data-index="${songsList.indexOf(song)}">▶️</button>
                    </h3>
                `;
                songsElement.appendChild(songElement);
            }

            // Add event listeners to the play buttons
            document.querySelectorAll('.play-button').forEach(button => {
                button.addEventListener('click', function(){
                    let index = button.getAttribute('data-index');
                    playSong(index);
                });
            });

        } else {
            console.error(data.message);
        }
    })
    .catch(error => console.error("Erreur lors de la récupération des chansons :", error));


    // Function to play a song
    function playSong(index) {
        if (audio.src === songsList[index].song && !audio.paused){
            audio.pause();
            playButton.textContent = '▶️';
        } else{
            audio.src = `../songs/${songsList[index].song.replace('../', '')}`;
            audio.play();
            playButton.textContent = '⏸️';
            musicTitle.textContent = songsList[index].name;
            musicImage.src = songsList[index].picture.replace('../', '');
            currentSongIndex = index;
        }
    }

    // button 'previous'
    prevButton.addEventListener('click', function(){
        if (currentSongIndex > 0){
            currentSongIndex--;
            playSong(currentSongIndex);
        } else{
            currentSongIndex = songsList.length - 1;
            playSong(currentSongIndex);
        }
    });

    // button 'next'
    nextButton.addEventListener('click', function(){
        if (currentSongIndex < songsList.length - 1){
            currentSongIndex++;
            playSong(currentSongIndex);
        } else{
            currentSongIndex = 0;
            playSong(currentSongIndex);
        }
    });

    // button 'like'
    likeButton.addEventListener('click', function(){
        alert('Chanson ajoutée aux favoris !');
    });

    // button 'add to playlist'
    addToPlaylistButton.addEventListener('click', function(){
        let playlistMenu = document.createElement('select');
        playlistMenu.innerHTML = `
            <option value="new">Créer une nouvelle playlist</option>
            <option value="1">Playlist 1</option>
            <option value="2">Playlist 2</option>
        `;
        document.body.appendChild(playlistMenu);

        let submitButton = document.createElement('button');
        submitButton.textContent = 'Ajouter à la Playlist';
        document.body.appendChild(submitButton);

        submitButton.addEventListener('click', function(){
            let selectedPlaylist = playlistMenu.value;
            if (selectedPlaylist === 'new'){
                let newPlaylistName = prompt('Nom de la nouvelle playlist :');
                console.log(`Playlist ${newPlaylistName} créée et ajoutée.`);
            } else{
                console.log(`Chanson ajoutée à la playlist ${selectedPlaylist}.`);
            }

            playlistMenu.remove();
            submitButton.remove();
        });
    });



    function deleteSongFromPlaylist(songId, playlistId){
        fetch('../lib/request.php?action=removeSongFromPlaylist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `song_id=${songId}&playlist_id=${playlistId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success){
                alert('Chanson supprimée de la playlist');
                showPlaylistSongs(playlistId, data.playlistName);
            } else{
                console.error("Erreur lors de la suppression de la chanson :", data.message);
            }
        })
        .catch(error => console.error("Erreur lors de la suppression de la chanson :", error));
    }
     
});

// Function to get a cookie value
function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

// button 'like'
document.addEventListener('DOMContentLoaded', function(){
    let musicFooter = document.querySelector('.music-footer');
    if (!musicFooter){
        console.error("music-footer introuvable.");
        return;
    }

    let likeButton = musicFooter.querySelector('.like-button');
    if (!likeButton){
        console.error("like-button introuvable.");
        return;
    }

    likeButton.addEventListener('click', function(){
        let songId = songsList[currentSongIndex]?.id_song;
        let userMail = getCookie('email');

        if (userMail && songId) {
            fetch('../lib/request.php?action=addLike', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `song_id=${songId}&email=${userMail}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success){
                    alert('Chanson ajoutée aux favoris !');
                } else{
                    console.error("Erreur lors de l'ajout de la chanson aux favoris :", data.message);
                }
            })
            .catch(error => console.error("Erreur lors de l'ajout de la chanson aux favoris :", error));
        }
    });
});

// button 'add to playlist'
document.addEventListener('DOMContentLoaded', function (){
    let usermail = getCookie('mail');

    if (usermail){
        fetch(`../lib/request.php?action=getLikedSong&mail=${usermail}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.songs.length > 0){
                let favoriteSongsContainer = document.getElementById('favorite-songs-container');
                favoriteSongsContainer.innerHTML = '';

                // Display the favorite songs
                if (data.songs.length > 0) {
                    data.songs.forEach(song => {
                        let songElement = document.createElement('div');
                        songElement.classList.add('card-favorite-song');
                        songElement.innerHTML = `
                            <img src="${song.picture.replace('../', '')}" alt="${song.name}" class="card-img">
                            <p>${song.name}</p>
                            <img src="../images/heart2.png" alt="Retirer des favoris" class="remove-favorite" data-song-id="${song.id_song}">
                        `;
                        favoriteSongsContainer.appendChild(songElement);
                    });
    
                    // Add event listeners to the remove favorite buttons
                    document.querySelectorAll('.remove-favorite').forEach(button => {
                        button.addEventListener('click', function(event) {
                            event.stopPropagation();
                            let songId = this.getAttribute('data-song-id');
                            removeFavoriteSong(songId, usermail);
                        });
                    });
                }
            } else{
                console.error('Erreur lors de la récupération des musiques favorites:', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des musiques favorites:', error);
        });
    } else{
        console.log("Utilisateur non connecté.");
    }
});

// Function to remove a song from the favorites
function removeFavoriteSong(id_song, usermail) {
    fetch(`../lib/request.php?action=removeLikedSong&mail=${usermail}&id_song=${id_song}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Chanson retirée des favoris.');
            location.reload();
        } else {
            console.error('Erreur lors de la suppression de la chanson des favoris:', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la suppression de la chanson des favoris:', error);
    });
}


