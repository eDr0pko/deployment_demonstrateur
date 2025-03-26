# Deployment du Démonstrateur

Ce guide vous accompagne dans le déploiement d'un démonstrateur sur une machine virtuelle Debian, utilisant Docker pour orchestrer plusieurs conteneurs.

## Prérequis

- **VM Debian** : Installez une machine virtuelle Debian en utilisant VMWare Workstation. Une vidéo d'installation est disponible ici : [Installation de la VM Debian](https://youtu.be/pr54p_7nEHA?si=3T9XeOvmft6K17Sh) (choisissez SSH à 8min23).

- **Configuration réseau** : Assurez-vous que la VM est configurée en mode **NAT**.

## Schéma des Conteneurs Docker

Le déploiement comprend 4 conteneurs Docker dont 3 accesible :

```
 _________________________________________________________________________
|                                                                         |                           
|                              RÉSEAU DOCKER                              |   
|                                                                         |
|   _____________________                                                 |
|  |                     |                                                | 
|  |       Apache        |                                                |  
|  |  Site Web Défensif  |                                                |
|  |    (Port : 8080)    |                                                |
|  |_____________________|                                                |
|           |                                                             |
|           |                                                             |
|           |                                                             |                          
|   ________|____________                        _____________________    |
|  |                     |    Base de Données   |                     |   |
|  |     phpMyAdmin      |______________________|        MySQL        |   |
|  |    (Port : 8081)    |                      |    (Port : 3306)    |   |
|  |_____________________|                      |_____________________|   |
|                                                                         |
|   _____________________                                                 |
|  |                     |                                                |
|  |       Apache        |                                                | 
|  |  Site Web Attaquant |                                                |
|  |    (Port : 8082)    |                                                |
|  |_____________________|                                                |
|                                                                         |
|_________________________________________________________________________|
```

```mermaid
graph TD;
    subgraph "Réseau Docker"
        A[Apache - Site Web Défensif (8080)]
        B[phpMyAdmin (8081)]
        C[MySQL (3306)]
        D[Apache - Site Web Attaquant (8082)]
        
        A --> B
        B --> C
        A --> C
        D --> A
    end
```

- **Site Web Défensif** : Accessible via `http://[votre_ip]:8080`, ce conteneur représente l'application cible à protéger.

- **Base de Données** : Accessible via `http://[votre_ip]:8081`, ce conteneur stocke les données de l'application.

- **Site Web Attaquant** : Accessible via `http://[votre_ip]:8082`, ce conteneur simule une application malveillante visant à compromettre le site web défensif.

## Instructions de Déploiement

1. **Accédez au répertoire root** de votre VM Debian :

   ```bash
   cd /root
   ```

2. **Mettez à jour les paquets et installez Git** :

   ```bash
   apt update && apt install git -y
   ```

3. **Clonez le dépôt GitHub** :

   ```bash
   git clone https://github.com/eDr0pko/deployment_demonstrateur.git
   ```

4. **Naviguez dans le répertoire cloné** :

   ```bash
   cd deployment_demonstrateur/
   ```

5. **Ajustez les permissions des répertoires** :

   ```bash
   chmod -R 755 defense-website
   chmod -R 755 attack-website
   chown -R www-data:www-data defense-website
   chown -R www-data:www-data attack-website
   ```

6. **Rendez le script de déploiement exécutable** :

   ```bash
   chmod +x deployment.sh
   ```

7. **Convertissez les fins de ligne du script si nécessaire** :

   ```bash
   sed -i 's/\r$//' deployment.sh
   ```

8. **Exécutez le script de déploiement** :

   ```bash
   ./deployment.sh
   ```

Ce script automatisera le déploiement des conteneurs Docker pour les trois services mentionnés.

## Accès aux Services

- **Site Web Défensif** : [http://[votre_ip]:8080](http://[votre_ip]:8080)

- **Base de Données** : [http://[votre_ip]:8081](http://[votre_ip]:8081)

- **Site Web Attaquant** : [http://[votre_ip]:8082](http://[votre_ip]:8082)

Remplacez `[votre_ip]` par l'adresse IP de votre machine virtuelle Debian.

