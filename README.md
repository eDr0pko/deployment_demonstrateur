# Instalation de la vm debian sous VMWare Workstation
https://youtu.be/pr54p_7nEHA?si=3T9XeOvmft6K17Sh (Choisir SSH a 8min23)

mettre le reseaux en bridge et si ip addr ne vous donne pas l'ip alors https://youtu.be/VVa1Q1wYgEY?si=tLW7U8-SnbD2_Vju



# deployment_demonstrateur
Ce mettre dans le /root de la debian

apt update && apt install git -y

git clone https://github.com/eDr0pko/deployment_demonstrateur.git

cd deployment_demonstrateur/

chmod -R 755 site-web

chown -R www-data:www-data site-web

chmod +x deployment.sh

sed -i 's/\r$//' deployment.sh

executer le .sh grace a la commande ./deployment.sh
