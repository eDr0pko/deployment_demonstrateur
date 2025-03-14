document.addEventListener("DOMContentLoaded", function () {
    const registerForm = document.getElementById("registerForm");

    registerForm.addEventListener("submit", function (event) {
        event.preventDefault();

        let username = document.getElementById("username").value.trim();
        let email = document.getElementById("email").value.trim();
        let password = document.getElementById("password").value.trim();
        let errorMessage = document.getElementById("error-message");

        // Check if fields are empty
        if (username === "" || email === "" || password === "") {
            errorMessage.innerText = "Veuillez remplir tous les champs.";
            errorMessage.style.color = "red";
            return;
        }

        // Check if email is valid
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            errorMessage.innerText = "Veuillez entrer un e-mail valide.";
            errorMessage.style.color = "red";
            return;
        }

        // Check if password is at least 2 characters long
        if (password.length < 2) {
            errorMessage.innerText = "Le mot de passe doit contenir au moins 2 caractères.";
            errorMessage.style.color = "red";
            return;
        }

        // Send data via AJAX
        let formData = new FormData();
        formData.append("action", "register");
        formData.append("username", username);
        formData.append("email", email);
        formData.append("password", password);


        fetch("../lib/request.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(text => {
            console.log("Réponse brute du serveur :", text);
            return JSON.parse(text);
        })
        .then(data => {
            if (data.success) {
                window.location.href = "login.html";
            } else {
                errorMessage.innerText = data.message;
                errorMessage.style.color = "red";
            }
        })
        .catch(error => {
            errorMessage.innerText = "Erreur de connexion au serveur.";
            errorMessage.style.color = "red";
            console.error("Erreur:", error);
        });
        
    });
});


