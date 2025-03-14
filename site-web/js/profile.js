document.getElementById('updateProfileForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const profilePicture = document.getElementById('profile_picture').files[0];

    if (password !== confirmPassword) {
        document.getElementById('responseMessage').innerText = "Les mots de passe ne correspondent pas.";
        return;
    }

    const formData = new FormData();
    formData.append('action', 'updateProfile');
    formData.append('username', username);
    formData.append('password', password);
    if (profilePicture) {
        formData.append('profile_picture', profilePicture);
    }

    fetch('../lib/request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('responseMessage').innerText = data.message;
    })
    .catch(error => {
        document.getElementById('responseMessage').innerText = "Une erreur est survenue.";
    });
});

document.getElementById('deleteAccountBtn').addEventListener('click', function() {
    if (confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.")) {
        fetch('../lib/request.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'deleteAccount' })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.href = 'login.html';
            }
        })
        .catch(error => {
            alert("Une erreur est survenue.");
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le nom d'utilisateur et la photo de profil à partir des cookies ou d'une session
    const username = getCookie('username');  // Assurez-vous que le nom d'utilisateur est stocké dans un cookie
    const profilePicture = getCookie('profile_picture');  // Récupérer la photo de profil depuis un cookie

    // Préremplir le champ username
    const usernameInput = document.getElementById('usernameInput');
    usernameInput.value = username;

    // Afficher le nom d'utilisateur en haut à droite
    const usernameSpan = document.getElementById('username');
    usernameSpan.textContent = username;

    // Afficher la photo de profil (si elle existe)
    const profilePictureElement = document.getElementById('profilePicture');
    if (profilePicture) {
        profilePictureElement.src = profilePicture;
    }

    // Fonction pour récupérer une valeur de cookie
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([.$?*|{}\(\)\[\]\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
});