$(document).ready(function () {
    $("button").click(function () {
        $.post("../lib/request.php", { action: "load_users_data" }, function (response) {
            try {
                let data = JSON.parse(response);
                let output = "";

                if (data.error) {
                    output = `<p style="color:red">${data.error}</p>`;
                } else {
                    data.forEach(user => {
                        output += `
                            <div>
                                <p><strong>Mail :</strong> ${user.mail}</p>
                                <p><strong>Nom :</strong> ${user.lastname}</p>
                                <p><strong>Prénom :</strong> ${user.firstname}</p>
                                <img src="${user.profile_picture}" alt="Photo de profil" width="50">
                                <hr>
                            </div>
                        `;
                    });
                }

                $("#comments").html(output);
            } catch (e) {
                console.error("Erreur de parsing JSON :", e, response);
            }
        });
    });
});


