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
            fetch('reset_db.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('result').textContent = data;
                })
                .catch(error => {
                    document.getElementById('result').textContent = 'Erreur lors de la réinitialisation de la base de données';
                    console.error('Erreur:', error);
                });
        }
    });
    
});


