$(document).ready(function () {
    // Get cookie by name
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([.$?*|{}\(\)\[\]\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    let userMail = getCookie("mail");

    // Check if user is connected
    if (!userMail) {
        alert("Utilisateur non authentifié !");
        window.location.href = "login.html";
    }

    // Update profile
    $("#updateProfileForm").on("submit", function (event) {
        event.preventDefault();
    
        let username = $("#username").val();
        let password = $("#password").val();
        let confirmPassword = $("#confirmPassword").val();
        let profilePicture = $("#profile_picture")[0].files[0];
        let userMail = getCookie("mail");
        let responseMessage = $("#responseMessage");
    
        if (!userMail) {
            responseMessage.text("Erreur : Impossible de récupérer l'email utilisateur.");
            return;
        }
    
        if (password !== confirmPassword) {
            responseMessage.text("Les mots de passe ne correspondent pas.");
            return;
        }
    
        let formData = new FormData();
        formData.append("action", "updateProfile");
        formData.append("mail", userMail);
        formData.append("username", username);
        if (password) {
            formData.append("password", password);
        }
        if (profilePicture) {
            formData.append("profile_picture", profilePicture);
        }
    
        $.ajax({
            url: "lib/request.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                response = JSON.parse(response);
                responseMessage.text(response.message);
            },
            error: function () {
                responseMessage.text("Une erreur est survenue.");
            }
        });

        // Clear form fields
        $("#password").val("");
        $("#confirmPassword").val("");
        $("#profile_picture").val("");
    });
    

    // Delete account
    $("#deleteAccountBtn").on("click", function () {
        if (confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.")) {
            let userMail = getCookie("mail");
    
            if (!userMail) {
                alert("Erreur : Impossible de récupérer l'email utilisateur.");
                return;
            }
    
            $.ajax({
                url: "lib/request.php",
                type: "POST",
                data: JSON.stringify({ action: "deleteAccount", mail: userMail }),
                contentType: "application/json",
                success: function (data) {
                    data = JSON.parse(data);
                    alert(data.message);
                    if (data.success) {
                        window.location.href = "login.html";
                    }
                },
                error: function () {
                    alert("Une erreur est survenue lors de la suppression du compte.");
                }
            });
        }
    });
    
    // Get user data
    let username = getCookie("username");
    let profilePicture = getCookie("profile_picture");

    $("#usernameInput").val(username);
    $("#username").text(username);
    
    if (profilePicture) {
        $("#profilePicture").attr("src", profilePicture);
    }
});


