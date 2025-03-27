# Deployment du Démonstrateur

Ce guide vous accompagne dans le déploiement d'un démonstrateur sur une machine virtuelle Debian, utilisant Docker pour orchestrer plusieurs conteneurs.

---

## 📜 Table des matières  
1. [Prérequis](#Prérequis)  
2. [Schéma des Conteneurs Docker](#Schéma-des-Conteneurs-Docker)  
3. [Instructions de Déploiement](#Instructions-de-Déploiement)  
4. [Accès aux Services](#Accès-aux-Services)  
5. [Identifiants de connexion 🔑](#Identifiants-de-connexion-🔑)  
 
---

## Prérequis

- **VM Debian** : Installez une machine virtuelle Debian en utilisant VMWare Workstation. Une vidéo d'installation est disponible ici : [Installation de la VM Debian](https://youtu.be/pr54p_7nEHA?si=3T9XeOvmft6K17Sh) (choisissez SSH à 8min23).

- **Configuration réseau** : Assurez-vous que la VM est configurée en mode **NAT**.

## Schéma des Conteneurs Docker

Le déploiement inclut quatre conteneurs Docker. Deux d'entre eux sont dédiés à la base de données et sont reliés à un troisième conteneur, formant ainsi le site vulnérable. Le quatrième conteneur représente le site contrôlé par l'attaquant. Seuls les trois conteneurs situés à gauche sont accessibles via un navigateur:

```
 ____VM_____________________________________________________________________________
|                                                                                   |                           
|                                   RÉSEAU DOCKER                                   |   
|                                                                                   |
|   _______________________________site vulnérable_______________________________   |
|  |     _____________________                                                   |  |
|  |    |                     |                                                  |  | 
|  |    |       Apache        |                                                  |  |  
|  |    |  Site Web Défensif  |                                                  |  |
|  |    |    (Port : 8080)    |                                                  |  |
|  |    |_____________________|                                                  |  |
|  |             |                                                               |  |
|  |             |                                                               |  |
|  |             |                                                               |  |                          
|  |     ________|____________                        _____________________      |  |
|  |    |                     |    Base de Données   |                     |     |  |
|  |    |     phpMyAdmin      |______________________|        MySQL        |     |  |
|  |    |    (Port : 8081)    |                      |    (Port : 3306)    |     |  |
|  |    |_____________________|                      |_____________________|     |  |
|  |                                                                             |  |
|  |_____________________________________________________________________________|  |
|                                                                                   |
|        _____________________                                                      |
|       |                     |                                                     |
|       |       Apache        |                                                     | 
|       |  Site Web Attaquant | <--site contrôlé par l'attaquant                    |
|       |    (Port : 8082)    |                                                     |
|       |_____________________|                                                     |
|                                                                                   |
|___________________________________________________________________________________|
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

## Identifiants de connexion 🔑

Cliquez pour afficher les mots de passe sur le site:


<details>
  <summary>Afficher les identifiants</summary>

  **Admin**  
  - ✉️ Email : `admin@admin.com`  
  - 🔑 Mot de passe : `1234`

  **Utilisateurs**  
  - ✉️ Email : `user1@user.com` ou `user2@user.com` ou `user3@user.com`  
  - 🔑 Mot de passe : `1234`

  **Artistes**  
    - ✉️ Email : `avicii@artist.com` ou `calvinharris@artist.com` ou `davidguetta@artist.com` ou  `kygo@artist.com` ou `martingarrix@artist.com`
    - 🔑 Mot de passe : `1234`

</details>

Cliquez pour afficher le mot de passe de la base de donée:


<details>
  <summary>Afficher les identifiants</summary>

  **Admin**  
  - ✉️ Username : `root`  
  - 🔑 Mot de passe : `superpass`

</details>

