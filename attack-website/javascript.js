function chargerFichier() {
    fetch("cookies.txt")  // On récupère directement cookies.txt
        .then(response => response.text())
        .then(data => {
            document.getElementById("contenu").textContent = data;
        })
        .catch(error => {
            document.getElementById("contenu").textContent = "Erreur lors du chargement.";
            console.error("Erreur :", error);
        });
}
    
// Charger le fichier immédiatement au démarrage
chargerFichier();
    
// Actualiser toutes les 1 secondes
setInterval(chargerFichier, 1000);
    


