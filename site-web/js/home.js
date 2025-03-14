const cookie = document.getElementById('cookie');
const plus = document.getElementById('plus');
const cookieExplanation = document.getElementById('cookie-explanation');

// Ajoute un écouteur d'événement pour le clic
cookie.addEventListener('click', () => {
    cookie.classList.toggle('active'); // Active/désactive la classe CSS "active"
    cookieExplanation.classList.toggle('hidden'); 
    cookieExplanation.classList.toggle('visible');

    if (plus.textContent === '+') {
        plus.textContent = '-';
    } else {
        plus.textContent = '+'; // Retour au texte initial
    }
});


/*----------------------------*/ 

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Empêche l'envoi du formulaire

    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let errorMessage = document.getElementById("error-message");

    if (email === "" || password === "") {
        errorMessage.textContent = "Veuillez remplir tous les champs.";
    } else {
        errorMessage.textContent = "";
        alert("Connexion réussie !");
        // Redirection ou autre action ici
    }
});


