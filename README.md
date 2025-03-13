# deployment_demonstrateur

Ce mettre dans le /root 

apt update && apt install git -y

clone le projet

cd deployment_demonstrateur/

faire chmod +x deployment.sh

faire sed -i 's/\r$//' deployment.sh

executer le .sh grace a la commande ./deployment.sh
