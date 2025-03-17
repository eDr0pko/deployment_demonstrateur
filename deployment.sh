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
mkdir -p /root/deployment_demonstrateur

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

  apache-web:
    build:
      context: .
      dockerfile: Dockerfile  # Utilise Dockerfile pour construire l'image
    container_name: apache-web
    restart: always
    volumes:
      - ./site-web:/var/www/html
    networks:
      - monreseau
    ports:
      - "8080:80"  # HTTP seulement
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
      - "8081:80"  # Accès à phpMyAdmin via http://IP_DU_SERVEUR:8081
    networks:
      - monreseau

networks:
  monreseau:
    driver: bridge

volumes:
  mysql_data:
EOL

# Création du fichier Dockerfile dans le répertoire de l'application
cat > /root/deployment_demonstrateur/Dockerfile <<EOL
FROM php:apache

# Installer PDO MySQL et redémarrer Apache
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Activer les logs pour voir les erreurs PHP
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error_reporting.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/error_reporting.ini

# Activer le module rewrite (si besoin pour des URL propres)
RUN a2enmod rewrite
EOL

# Démarrer les conteneurs avec docker-compose
echo "Démarrage des conteneurs Docker avec docker-compose..."
docker-compose -f /root/deployment_demonstrateur/docker-compose.yml up -d

# Attendre quelques secondes pour s'assurer que le conteneur Apache est bien démarré
sleep 5  

# Vérification de l'installation de mysqli dans le conteneur Apache
echo "Vérification de l'installation de mysqli dans le conteneur Apache..."
docker exec -it apache-web php -m | grep mysqli

# Affichage des conteneurs en cours d'exécution
echo "Affichage des conteneurs en cours d'exécution..."
docker ps

# Affichage des informations réseau
echo "Affichage des informations réseau..."
ip addr show
