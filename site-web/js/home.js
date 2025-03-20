// Funnction to toggle the visibility of the explanation of the labels
$(document).ready(function () {
    $('.label').click(function () {
        const explanation = $(this).next('.explanation');
        const icon = $(this).find('.toggle-icon');

        explanation.toggleClass('visible');

        if (explanation.hasClass('visible')) {
            icon.text('−');
        } else {
            icon.text('+');
        }
    });

    document.getElementById("reset-db").addEventListener("click", function() {
        if (confirm("Êtes-vous sûr de vouloir réinitialiser la base de données ? Cette action est irréversible.")) {
            $.ajax({
                url: "lib/request.php",
                data: {
                    action: "reset"
                },
                success: function(data) {
                    alert(data);
                },
                error: function(error) {
                    alert("Erreur : " + error);
                }
            });
        }
    });
});


