// Fonction pour charger les artistes
function loadArtists() {
    fetch('request.php?type=artists')
      .then(response => response.json())
      .then(data => {
        const list = document.getElementById('artists-list');
        list.innerHTML = '';
        data.forEach(artist => {
          const li = document.createElement('li');
          li.textContent = artist.username;
          list.appendChild(li);
        });
      })
      .catch(error => console.error('Erreur:', error));
  }
  
  // Fonction pour charger les chansons
  function loadSongs() {
    fetch('request.php?type=songs')
      .then(response => response.json())
      .then(data => {
        const list = document.getElementById('songs-list');
        list.innerHTML = '';
        data.forEach(song => {
          const li = document.createElement('li');
          li.textContent = `${song.name} (${song.time})`;
          list.appendChild(li);
        });
      })
      .catch(error => console.error('Erreur:', error));
  }
  