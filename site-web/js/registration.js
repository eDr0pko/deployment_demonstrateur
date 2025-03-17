$(document).ready(function () {
    $("#registerForm").on("submit", function (event) {
        event.preventDefault();

        // Get the form data
        let username = $("#username").val().trim();
        let email = $("#email").val().trim();
        let password = $("#password").val().trim();
        let errorMessage = $("#error-message");

        // Check if all fields are filled
        if (username === "" || email === "" || password === "") {
            errorMessage.text("Veuillez remplir tous les champs.").css("color", "red");
            return;
        }

        // Check if email is valid
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            errorMessage.text("Veuillez entrer un e-mail valide.").css("color", "red");
            return;
        }

        // Check if password is at least 2 characters long
        if (password.length < 2) {
            errorMessage.text("Le mot de passe doit contenir au moins 2 caractères.").css("color", "red");
            return;
        }

        // Create a FormData object
        let formData = new FormData();
        formData.append("action", "register");
        formData.append("username", username);
        formData.append("email", email);
        formData.append("password", password);

        // Send the form data to the server
        $.ajax({
            url: "lib/request.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log("Réponse brute du serveur :", response);
                try {
                    let data = JSON.parse(response);
                    if (data.success) {
                        window.location.href = "login.html";
                    } else {
                        errorMessage.text(data.message).css("color", "red");
                    }
                } catch (e) {
                    errorMessage.text("Réponse invalide du serveur.").css("color", "red");
                    console.error("Erreur de parsing JSON:", e);
                }
            },
            error: function (xhr, status, error) {
                errorMessage.text("Erreur de connexion au serveur.").css("color", "red");
                console.error("Erreur AJAX :", error);
            }
        });
    });
});


