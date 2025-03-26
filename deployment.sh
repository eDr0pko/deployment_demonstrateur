#!/bin/bash
 
# Vérification si l'utilisateur est root
if [ "$(id -u)" -ne 0 ]; then
    echo "Ce script doit être exécuté avec des privilèges root."
    exit 1
fi
 
# Installation de Docker
echo "Installation de Docker..."

apt update
apt install apt-transport-https ca-certificates curl software-properties-common -y
 
# Ajout de la clé GPG de Docker
curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
 
# Ajout du dépôt Docker
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
 
# Mise à jour des dépôts et installation de Docker
apt update
apt install docker-ce -y
 
# Vérification de l'installation de Docker
systemctl status docker --no-pager
 
# Installation de Docker Compose
echo "Installation de Docker Compose..."
curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
 
# Attribution des permissions d'exécution
chmod +x /usr/local/bin/docker-compose
 
# Vérification de l'installation de Docker Compose
docker-compose --version
 
# Création du répertoire pour le projet
echo "Création du projet Docker..."
 
# Création du fichier docker-compose.yml
cat > /root/deployment_demonstrateur/docker-compose.yml <<EOL
version: '3.8'
 
services:
  mysql-db:
    image: mysql:latest
    container_name: mysql-db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: superpass
      MYSQL_DATABASE: mon_site_db
      MYSQL_USER: mon_user
      MYSQL_PASSWORD: pass_user
    volumes:
      - mysql_data:/var/lib/mysql
      - ./db-init:/docker-entrypoint-initdb.d
    networks:
      - monreseau
    ports:
      - "3306:3306"
 
  apache-web-defense:
    image: php:apache
    container_name: vulnerable-website
    restart: always
    volumes:
      - ./defense-website:/var/www/html
    networks:
      - monreseau
    ports:
      - "0.0.0.0:8080:80"  # http://IP_DU_SERVEUR:8080
    depends_on:
      - mysql-db
 
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: phpmyadmin
    restart: always
    environment:
      PMA_HOST: mysql-db
      PMA_PORT: 3306
    ports:
      - "0.0.0.0:8081:80"  # http://IP_DU_SERVEUR:8081
    networks:
      - monreseau

  apache-web-attacker:
    image: php:apache
    container_name: attacker-website
    restart: always
    volumes:
      - ./attack-website:/var/www/html
    ports:
      - "0.0.0.0:8082:80"  # http://IP_DU_SERVEUR:8082
 
networks:
  monreseau:
    driver: bridge
 
volumes:
  mysql_data:
EOL
 
# Lancer les conteneurs avec docker-compose
echo "Démarrage des conteneurs Docker avec docker-compose..."
docker-compose -f /root/deployment_demonstrateur/docker-compose.yml up -d

# Attendre quelques secondes pour s'assurer que le conteneur Apache est bien démarré
sleep 5  
 
# Installation de mysqli dans le conteneur Apache
echo "Installation de mysqli dans le conteneur Apache..."
docker exec -it vulnerable-website docker-php-ext-install mysqli
 
# Redémarrer le conteneur pour appliquer les changements
docker restart vulnerable-website
 
# Vérification de l'installation de mysqli
docker exec -it vulnerable-website php -m | grep mysqli
 
# Affichage des conteneurs en cours d'exécution
cd deployment_demonstrateur
docker ps
 
# Affichage des informations réseau
# Récupérer l'adresse IP de ens33
IP=$(ip addr show ens33 | grep 'inet ' | awk '{print $2}' | cut -d'/' -f1)

# Vérifier si une IP a été trouvée
if [ -z "$IP" ]; then
    echo "Erreur : Impossible de récupérer l'adresse IP de ens33."
    exit 1
fi


# Affichage stylisé
echo "____"
echo "      |"
echo "      |"
echo "     \\/"
echo -e "\e[1;32m$IP   <-- Votre IP globale\e[0m"
echo "      |__"
echo "          |"
echo "         \\/"
echo -e "\e[1;34m     $IP:8080   <-- IP site web vulnérable http://$IP:8080\e[0m"
echo -e "\e[1;34m     $IP:8081   <-- IP de la base de données http://$IP:8081\e[0m"
echo -e "\e[1;34m     $IP:8082   <-- IP site web contrôlé par l'attaquant http://$IP:8082\e[0m"