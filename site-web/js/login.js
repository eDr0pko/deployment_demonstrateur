document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", function (event) {
        event.preventDefault();

        let email = document.getElementById("email").value.trim();
        let password = document.getElementById("password").value.trim();
        let errorMessage = document.getElementById("error-message");

        // Check if fields are empty
        if (email === "" || password === "") {
            errorMessage.innerText = "Veuillez remplir tous les champs.";
            errorMessage.style.color = "red";
            return;
        }

        // Check if email is valid
        let formData = new FormData();
        formData.append("action", "login");
        formData.append("email", email);
        formData.append("password", password);

        
        fetch("../lib/request.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(responseText => {
            console.log("Réponse brute :", responseText);
            try {                
                let data = JSON.parse(responseText);
                if (data.success) {
                    window.location.href = "user.html";
                } else {
                    errorMessage.innerText = data.message;
                    errorMessage.style.color = "red";
                }
            } catch (error) {
                errorMessage.innerText = "Erreur de connexion au serveur.";
                errorMessage.style.color = "red";
                console.error("Erreur de parsing JSON:", error);
            }
        })
        .catch(error => {
            errorMessage.innerText = "Erreur de connexion au serveur.";
            errorMessage.style.color = "red";
            console.error("Erreur:", error);
        });        
    });
});


