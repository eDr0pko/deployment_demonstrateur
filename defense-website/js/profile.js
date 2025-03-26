let usermail = null;
let username = null;

$(document).ready(function(){
    $.ajax({
        url: 'lib/session.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response.loggedIn) {
                window.location.href = "login.html";
            } else {
                usermail = response.mail;
                username = response.username;
                
                // Update the user's name and email
                $('#user-name').text(username);
                $('#usermail').text(usermail);

                // display the user's profile picture
                if (response.profile_picture){
                    $('#profilePicture').attr('src', response.profile_picture);
                }

                // Call the function to initialize the user data
                initUserData();
            }
        },
        error: function() {
            alert('Erreur lors de la vérification de la session.');
        }
    });
});


function initUserData(){
    if (!usermail) {
        console.error("Erreur : usermail non défini lors de l'initialisation !");
        return;
    }

    // Mise à jour du formulaire de profil
    $("#updateProfileForm").on("submit", function(event){
        event.preventDefault();
    
        let newUsername = $("#usernameInput").val();
        let password = $("#password").val();
        let confirmPassword = $("#confirmPassword").val();
        let profilePicture = $("#profile_picture")[0].files[0];
        let responseMessage = $("#responseMessage");
    
        if (password !== confirmPassword){
            responseMessage.text("Les mots de passe ne correspondent pas.");
            return;
        }
    
        let formData = new FormData();
        formData.append("action", "updateProfile");
        formData.append("mail", usermail);
        formData.append("username", newUsername);
        if (password){
            formData.append("password", password);
        }
        if (profilePicture){
            formData.append("profile_picture", profilePicture);
        }
    
        $.ajax({
            url: "lib/request.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response){
                responseMessage.text("Profil mis à jour avec succès.");

                $("#profilePicture").attr("src", response.profile_picture);
                $("#username").text(newUsername);     
            }
        });

        // Nettoyage des champs après soumission
        $("#password").val("");
        $("#confirmPassword").val("");
        $("#profile_picture").val("");
    });

    // Suppression du compte
    $("#deleteAccountBtn").on("click", function () {
        if (confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.")) {
            $.ajax({
                url: "lib/request.php",
                type: "POST",
                data: JSON.stringify({ action: "deleteAccount", mail: usermail}),
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

    // Mise à jour du profil avec les données récupérées
    $("#usernameInput").val(username);
    $("#username").text(username);
}


