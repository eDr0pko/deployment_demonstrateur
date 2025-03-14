# Instalation de la vm debian 
https://youtu.be/pr54p_7nEHA?si=3T9XeOvmft6K17Sh (Choisir SSH et Server Web a 8min23)



# deployment_demonstrateur
Ce mettre dans le /root de la debian

apt update && apt install git -y

clone le projet

cd deployment_demonstrateur/

faire chmod +x deployment.sh

faire sed -i 's/\r$//' deployment.sh

executer le .sh grace a la commande ./deployment.sh
