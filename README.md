````md
# n1immo-nvsite — Guide Git (équipe dev)

Ce dépôt versionne le code WordPress **principalement dans `wp-content/`** (thèmes, plugins, mu-plugins, config WP Rocket).
⚠️ Les médias `wp-content/uploads` et les caches **ne doivent pas être versionnés**.

---

## 0) Règles de branches

- **`dev`** : branche de travail (toute l’équipe pousse ici via PR)
- **`main`** : branche PROD (réservée au responsable / pas de push direct)

✅ On travaille via branches de feature → Pull Request → merge vers `dev`.

---

## 1) Installation / Récupérer le projet en local

### 1.1 Cloner le dépôt
```bash
git clone https://github.com/makedifferentproject/n1immo-nvsite.git
cd n1immo-nvsite
````

### 1.2 Aller sur la branche dev

```bash
git checkout dev
git pull
```

---

## 2) Workflow standard (recommandé) — branche feature + PR

### 2.1 Mettre à jour `dev` avant de démarrer

```bash
git checkout dev
git pull
```

### 2.2 Créer une branche de travail

Nom conseillé : `feature/...` ou `fix/...`

```bash
git checkout -b feature/ma-tache
```

Exemples :

* `feature/home-slider`
* `fix/menu-mobile`
* `fix/form-validation`

### 2.3 Faire tes modifications (dans wp-content)

Ex : thème / plugin / mu-plugin

### 2.4 Voir les fichiers modifiés

```bash
git status
git diff
```

### 2.5 Ajouter les fichiers

```bash
git add .
```

Ou fichier par fichier :

```bash
git add wp-content/themes/mon-theme/functions.php
```

### 2.6 Commit

Message clair (format conseillé) :

* `fix: ...`
* `feat: ...`
* `chore: ...`
* `docs: ...`

```bash
git commit -m "fix: correct header spacing on mobile"
```

### 2.7 Push ta branche sur GitHub

```bash
git push -u origin feature/ma-tache
```

### 2.8 Ouvrir une Pull Request (PR)

Sur GitHub :

1. **Pull requests** → **New pull request**
2. **base = dev**  ← compare = `feature/ma-tache`
3. Décrire la modif + screenshots si besoin
4. Demander review au responsable

---

## 3) Mettre à jour ta branche (éviter conflits)

Si `dev` a avancé pendant que tu travailles :

```bash
git checkout dev
git pull
git checkout feature/ma-tache
git merge dev
```

S’il y a des conflits :

1. Résoudre dans les fichiers
2. Puis :

```bash
git add .
git commit -m "chore: resolve merge conflicts"
git push
```

---

## 4) Cas simple (petite modif) — travailler directement sur `dev` (à éviter si possible)

```bash
git checkout dev
git pull
# modifications...
git add .
git commit -m "fix: small css tweak"
git push
```

---

## 5) Ce qu’on NE push JAMAIS

* `wp-content/uploads/`
* `wp-content/cache/`
* `wp-content/upgrade/`
* backups / logs / fichiers temporaires
* secrets (`.env`, `wp-config.php`, clés API…)

✅ Si tu vois `uploads` dans `git status`, stop et préviens.

---

## 6) Commandes utiles (cheat sheet)

### Voir les branches

```bash
git branch -a
```

### Voir l’historique

```bash
git log --oneline --decorate --graph -15
```

### Annuler des changements non commit

```bash
git checkout -- .
```

### Retirer un fichier du “stage” (avant commit)

```bash
git restore --staged chemin/du/fichier
```

### Créer / changer de branche

```bash
git checkout -b feature/ma-tache
git checkout dev
```

---

## 7) Déploiement (info)

Le déploiement sur serveur (FTP / CI) est géré par le responsable :

* push/merge sur `dev` → environnement DEV
* merge sur `main` → PROD

---

## 8) Support

En cas de doute (conflits, push refusé, fichiers interdits détectés) :
➡️ contacter le responsable du dépôt.

 
