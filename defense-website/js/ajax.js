// Requête AJAX générique
function ajaxRequest(type, url, callback, data = null) {
    let xhr = new XMLHttpRequest();
    xhr.open(type, url);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = () => {
        if (xhr.status === 200) {
            try {
                let resp = JSON.parse(xhr.responseText);
                callback(resp);
            } catch (e) {
                console.error("Erreur de parsing JSON :", e, xhr.responseText);
            }
        } else {
            httpErrors(xhr.status);
        }
    };
    xhr.send(data);
}

// Gérer les erreurs HTTP
function httpErrors(errorCode) {
    let messages = {
        400: "Requête incorrecte",
        401: "Authentifiez-vous",
        403: "Accès refusé",
        404: "Page non trouvée",
        500: "Erreur interne du serveur",
        503: "Service indisponible"
    };
    let errorMsg = messages[errorCode] || "Erreur inconnue";
    console.error("Erreur HTTP :", errorMsg);
}


