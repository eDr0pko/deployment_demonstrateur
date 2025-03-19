$(document).ready(function (){
    // Funcion to get the cookie
    function getCookie(name) {
        let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    let username = getCookie('username');
    let profilePicture = getCookie('profile_picture');
    let usermail = getCookie('mail');

    // Check if the user is connected
    if (!usermail){
        window.location.href = "login.html";
    }

    // Display the user name
    if (username){
        $('#user-name').text(username);
    }

    // Display the user's linked songs
    if (usermail){
        $.getJSON(`lib/request.php?action=getLikedSong&mail=${usermail}`, function(data){
            if (data.success){
                let favoriteSongsContainer = $('#favorite-songs-container').empty();
                $.each(data.songs, function(index, song){
                    let songElement = $(`
                        <div class="card-favorite-song" data-song-id="${song.id_song}">
                            <img src="${song.picture}" alt="${song.name}" class="card-img">
                            <p>${song.name}</p>
                            <img src="images/heart2.png" alt="Retirer des favoris" class="remove-favorite" data-song-id="${song.id_song}">
                        </div>
                    `);
                    favoriteSongsContainer.append(songElement);

                    // Check if the song is clicked
                    songElement.click(function(){
                        playSongById(song.id_song);
                    });

                    // Delete the song from the favorites
                    songElement.find('.remove-favorite').click(function(event){
                        event.stopPropagation();
                        let songId = $(this).data('song-id');
                        $.get(`lib/request.php?action=removeLikedSong&mail=${usermail}&id_song=${songId}`, function (){
                            $(`.card-favorite-song[data-song-id="${songId}"]`).remove();
                        });
                    });

                });
            }
        });
    }

    // Display the user's playlists
    if (usermail){
        $.getJSON('lib/request.php?action=getPlaylists', function(data){
            if (data.success){
                let playlistsElement = $('.playlists');
                data.playlists.forEach(playlist => {
                    let playlistElement = $(`
                        <div class="card-playlist">
                            <div class="card-content">
                                <h3 class="card-title">${playlist.playlist_name}</h3>
                                <div class="delete-container">
                                    <button class="delete-playlist-button"></button>
                                </div>
                            </div>
                        </div>
                    `);
                    playlistElement.click(function(){
                        showPlaylistSongs(playlist.id_playlist, playlist.playlist_name);
                    });

                    // Check if the playlist is clicked
                    playlistElement.find('.delete-playlist-button').on('click', function (e){
                        e.stopPropagation();
                        if (confirm('Êtes-vous sûr de vouloir supprimer cette playlist ?')){
                            deletePlaylist(playlist.id_playlist, playlistElement);
                        }
                    });

                    playlistsElement.append(playlistElement);
                });

                // card to create a new playlist
                let createPlaylistElement = $(`
                    <div class="card-playlist">
                        <div class="card-content">
                            <h3 class="card-title">➕</h3>
                        </div>
                    </div>
                `);

                // Creation of a new playlist
                createPlaylistElement.click(() => {
                    let playlistName = prompt("Nom de la playlist :");
                    if (playlistName) {
                        let postData = {
                            action: "createPlaylist",
                            mail: usermail,
                            playlist_name: playlistName
                        };
                
                        $.ajax({
                            url: "lib/request.php",
                            type: "POST",
                            data: postData,
                            dataType: "json",
                            success: function (response) {
                                if (response.success) {
                                    let newPlaylistElement = $(`
                                        <div class="card-playlist">
                                            <div class="card-content">
                                                <h3 class="card-title">${playlistName}</h3>
                                                <div class="delete-container">
                                                    <button class="delete-playlist-button"></button>
                                                </div>
                                            </div>
                                        </div>
                                    `);
                                    $(".playlists").append(newPlaylistElement);
                                } else {
                                    alert("Erreur lors de la création de la playlist: " + response.message);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("Erreur AJAX :", xhr.responseText);
                            }
                        });
                    }
                });
                playlistsElement.append(createPlaylistElement);
            } else {
                console.error('Erreur lors de la récupération des playlists');
            }
        }).fail(error => console.error("Erreur lors de la récupération des playlists :", error));
    }

    // Function to delete a playlist
    function deletePlaylist(id_playlist, playlistElement){
        $.get(`lib/request.php?action=deletePlaylist&id_playlist=${id_playlist}`, function(){
            playlistElement.remove();
        });
    }

    // Function to show the playlist songs
    function showPlaylistSongs(id_playlist, playlistName){
        $.ajax({
            url: `lib/request.php?action=getPlaylistSongs&id_playlist=${id_playlist}`,
            type: 'GET',
            dataType: 'json',
            success: async function(data){
                if (data.success){
                    let oldModal = $('.modal');
                    if (oldModal.length) oldModal.remove();

                    let modal = $(`
                        <div class="modal">
                            <div class="modal-content">
                                <span class="close-button">&times;</span>
                                <h2>${playlistName}</h2>
                                <div class="playlist-songs"></div>
                            </div>
                        </div>
                    `);
                    $('body').append(modal);
                    let songsContainer = modal.find('.playlist-songs');

                    if (data.songs.length > 0) {
                        for (let song of data.songs){
                            
                            // Get the artist name
                            if (song.id_artist){
                                let artistData = await $.getJSON(`lib/request.php?action=getArtistName&id_artist=${song.id_artist}`);
                                song.artist = artistData.success ? artistData.artistName : "Artiste inconnu";
                            } else {
                                song.artist = "Artiste inconnu";
                            }

                            let songDiv = $(`
                                <div class="playlist-song">
                                    <p>${song.name} - <span class="artist-name">${song.artist}</span></p>
                                    <button class="delete-button" data-song-id="${song.id_song}">Supprimer</button>
                                </div>
                            `);

                            // Delete the song from the playlist
                            songDiv.find('.delete-button').on('click', function(){
                                deleteSongFromPlaylist(song.id_song, id_playlist, songDiv);
                            });

                            // Play the song when clicked
                            songDiv.on('click', function(){
                                if (!$(event.target).hasClass('delete-button')){
                                    playSongById(song.id_song);
                                }
                            });
                            songsContainer.append(songDiv);
                        }
                    } else {
                        songsContainer.html("<p>Aucune chanson dans cette playlist.</p>");
                    }

                    modal.find('.close-button').on('click', function(){
                        modal.remove();
                    });
                }
            },
        });
    }

    // function to play a song by id
    function playSongById(songId){
        let song = songsList.find(s => s.id_song === songId);
        if (song){
            playSong(songsList.indexOf(song));
        }
    }

   // Get all songs
    $.getJSON('lib/request.php?action=getSongs', function(data){
        if (data.success){
            let songsElement = $('#songs');
            songsList = data.songs;
            
            // Display each song
            songsList.forEach(async (song, index) => {
                // Get the album name
                if (song.id_song){
                    let albumData = await $.getJSON(`lib/request.php?action=getAlbumName&id_song=${song.id_song}`);
                    song.album = albumData.success ? albumData.albumName : "Aucun album";
                } else {
                    song.album = "Aucun album";
                }

                // Get the artist name
                if (song.id_artist){
                    let artistData = await $.getJSON(`lib/request.php?action=getArtistName&id_artist=${song.id_artist}`);
                    song.artist = artistData.success ? artistData.artistName : "Artiste inconnu";
                } else {
                    song.artist = "Artiste inconnu";
                }

                // Create the song element
                let songElement = $(`
                    <div class="card-musique" data-song-id="${song.id_song}">
                        <img src="${song.picture}" alt="${song.name}" class="card-img">
                        <h3 class="card-title">${song.name}</h3>
                        <h3 class="card-album">${song.album}</h3>
                        <h3 class="card-singer">${song.artist}</h3>
                        <h3 class="card-play">
                            <button class="play-button" data-index="${index}">▶️</button>
                        </h3>
                    </div>
                `);
                songsElement.append(songElement);

                // Play the song when clicked
                songElement.find('.play-button').click(function() {
                    let songIndex = $(this).data('index');
                    playSong(songIndex);
                });

                // Show the song comments when clicked
                songElement.click(function (e){
                    if (!$(e.target).hasClass('play-button')){
                        showSongComments(song.id_song);
                    }
                });
            });
        }
    });

    function showSongComments(id_song) {
        // Créer dynamiquement le modal
        const modal = $('<div id="commentModal" class="modal"></div>');
        const modalContent = $('<div class="modal-content"></div>');
        const closeBtn = $('<span class="close-button">&times;</span>');
        const title = $('<h2>Commentaires</h2>');
        const commentsList = $('<div id="commentsList"></div>');
        const commentForm = $('<div id="commentForm">\
                                <textarea id="commentText" placeholder="Ajouter un commentaire" rows="4" cols="50"></textarea>\
                                <button id="submitComment">Ajouter</button>\
                              </div>');
    
        // Ajouter les éléments dans le modal
        modalContent.append(closeBtn);
        modalContent.append(title);
        modalContent.append(commentsList);
        modalContent.append(commentForm);
        modal.append(modalContent);
        $('body').append(modal);
    
        // Fermer le modal lorsque la croix est cliquée
        modal.find('.close-button').on('click', function() {
            modal.remove();
        });
    
        // Récupérer les commentaires du son
        $.getJSON(`lib/request.php?action=getComments&id_song=${id_song}`, function(data) {
            if (data.success) {
                commentsList.empty();  // Nettoyer les anciens commentaires
    
                if (data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        let commentElement = $(`
                            <div class="comment">
                                <strong>${comment.mail} :</strong> ${comment.comment}
                                <br><span style="font-size: 12px; color: gray;">Posté le ${comment.comment_date}</span>
                            </div>
                            <hr>
                        `);
                        commentsList.append(commentElement);
                    });
                } else {
                    commentsList.append("<p>Aucun commentaire pour ce son.</p>");
                }
    
                // Afficher le modal
                modal.show();
            } else {
                alert("Erreur lors de la récupération des commentaires.");
            }
        }).fail(error => {
            console.error("Erreur AJAX :", error);
        });
    
        // Ajouter un commentaire au clic sur le bouton "Ajouter"
        modal.find('#submitComment').on('click', function() {
            const commentText = modal.find('#commentText').val();
    
            if (commentText.trim() !== "") {
                // Envoi du commentaire via AJAX
                $.post('lib/request.php',{
                    action: 'addComment',
                    id_song: id_song,
                    comment: commentText
                }, function(response){
                    // Ajouter le commentaire à la liste
                    let commentElement = $(`
                        <div class="comment">
                            <strong>${response.mail} :</strong> ${commentText}
                            <br><span style="font-size: 12px; color: gray;">Posté le ${response.comment_date}</span>
                        </div>
                        <hr>
                    `);
                    commentsList.append(commentElement);
                    modal.find('#commentText').val('');
                }).fail(function() {
                    alert("Erreur lors de l'envoi du commentaire.");
                });
            } else {
                alert("Le commentaire ne peut pas être vide.");
            }
        });
    }
    
    // Exemple d'utilisation avec un clic sur un son
    $('#songs').on('click', '.card-musique', function (e) {
        let songId = $(this).data('song-id');
        showSongComments(songId);
    });    
    

    // ------ -----   ----- -----     Music player     ----- -----   ----- ----- //

    let musicFooter = $('.music-footer');
    let likeButton = musicFooter.find('.like-button');
    let audio = new Audio();
    let currentSongIndex = 0;
    let songsList = [];

   // Function to play a song by index
   function playSong(index) {
        let song = songsList[index];
        let musicFooter = $('.music-footer');
        let musicImage = musicFooter.find('img');
        let musicTitle = musicFooter.find('span');
        let playButton = musicFooter.find('button:nth-child(2)');
        let AddPlaylist = musicFooter.find('add-to-playlist-button');
        
        // Check if the song is already playing or not
        if (audio.src === `songs/${song.song}` && !audio.paused){
            audio.pause();
            playButton.text('▶️');
        } else {
            audio.src = `songs/${song.song}`;
            audio.play();
            playButton.text('⏸️');
            musicTitle.text(song.name);
            musicImage.attr('src', song.picture);
        }

        // Update the current song index
        currentSongIndex = index;

        // Play the next song when the current song ends
        audio.onended = function(){
            currentSongIndex = (currentSongIndex + 1) % songsList.length;
            playSong(currentSongIndex);
        };
    }

    // Function to play the previous song
    $(document).on('click', '.prev-button', function(){
        currentSongIndex = (currentSongIndex - 1 + songsList.length) % songsList.length;
        playSong(currentSongIndex);
    });

    // Function to play the next song
    $(document).on('click', '.next-button', function(){
        currentSongIndex = (currentSongIndex + 1) % songsList.length;
        playSong(currentSongIndex);
    });

    // Change the play button or pause button
    let playButton = $('.music-footer').find('button:nth-child(2)');
    playButton.click(function(){
        if (audio.paused){
            audio.play();
            playButton.text('⏸️');
        } else {
            audio.pause();
            playButton.text('▶️');
        }
    });

    // Update the progress bar
    audio.addEventListener('timeupdate', function(){
        let progress = (audio.currentTime / audio.duration) * 100;
        $('.progress-fill').css('width', progress + '%');
    });    

    // Add a like to the song
    likeButton.click(() => {
        let songId = songsList[currentSongIndex]?.id_song;
        if (usermail && songId) {
            let like_date = new Date().toISOString().slice(0, 19).replace('T', ' ');
            $.post('lib/request.php?action=addLike', { mail: usermail, id_song: songId, like_date: like_date }, function(data){
                if (data.success) {
                    alert('Ajouté aux favoris');
                } else {
                    alert('Erreur, essai plus tard');
                }
            });
        }
    });


    // ------ -----   ----- -----     Other Functions     ----- -----   ----- ----- //

    // Function to delete a song from the playlist
    function deleteSongFromPlaylist(songId, playlistId, songElement){
        $.ajax({
            url: "lib/request.php",
            type: "POST",
            data: {
                action: "deleteSongFromPlaylist",
                id_song: songId,
                id_playlist: playlistId
            },
            dataType: "json",
            success: function (response){
                if (response.success){
                    songElement.remove();
                } else {
                    alert("Erreur : " + response.message);
                }
            },
            error: function (xhr, status, error){
                console.error("Erreur AJAX :", error);
            }
        });
    }    
});

// function to show the artist button or admin button
$(document).ready(function(){
    $.getJSON("lib/request.php?action=checkUserType", function(data){
        if (data.success){
            if (data.role === "artist"){
                $("#artiste-button").show();
            }
            if (data.role === "admin"){
                $("#admin-button").show();
            }
        }
    });
});


