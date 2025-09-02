# GIT starter kit

## How GIT works

Git est un outil de versioning distribué qui permet de suivre les modifications d'un projet en temps réel.
Il est essentiel pour collaborer sur des projets, partager du code et revenir à une version antérieure si nécessaire.

## Commandes de base avec Git

1. Initialiser un repository

```
git init
```

2. Ajouter des fichiers au staging

```
git add .
```

3. **Créer une première commit**

```
git commit -m "Initial commit"
```

4. Push vers un dépôt distant (ex: GitHub)
```
git remote add origin <URL-du-dépôt>
git push -u origin main
```

Exemple : Gérer un Dockerfile avec Git
Étape 1 : Créer un projet simple
mkdir docker-kata && cd docker-kata
Copy
Étape 2 : Créer un Dockerfile
# Dockerfile
FROM python:3.9-slim
WORKDIR /app
COPY . .
CMD ["python", "app.py"]
Copy
Étape 3 : Ajouter le fichier au repository Git
git add Dockerfile
Copy
Étape 4 : Commit et push
git commit -m "Ajout de Dockerfile"
git push
Copy
Avantages du combo Git + Docker
Versionning des configurations : Suivre les changements d'un Dockerfile.
Collaboration : Partager facilement des images et des configurations.
Reversion : Revenir à une version antérieure de votre code ou de vos conteneurs.
Conclusion
Avec Git, vous pouvez gérer efficacement vos projets, y compris ceux liés à Docker. Ce starter kit vous permet de commencer rapidement avec des commandes simples et des exemples concrets. Découvrez le monde du versioning et de la collaboration en ligne ! 🚀
