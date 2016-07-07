# Description

Le plugin sert à syncrhoniser les comptes utilisateurs entre Google Apps et FusionDirectory.

# Fonctionnement

Le plugin récupère la liste des utilisateurs présents sur votre compte Google Apps (noms, prénoms, emails).
Il vérifie via les adresses email (considérée comme identifiant unique) s'ils sont présents.

Dans le cas où l'email existe, le nom et le prénom sont comparés et modifiés si besoin.

Dans le cas où l'email n'existe pas, l'utilisateur est créé avec avec les onglets Samba,Unix et Mail.
Les onglets Samba sont uniquement ajoutés si un nom de domaine Samba est indiqué dans le fichier de configuration config.php.


# Versions

v1 : Google Apps est utilisé en tant que source maitre et importe les utilisateurs (Email, Nom, Prénom) dans FusionDirectory. En cas d'email déjà existant, les noms et prénoms seront modifié par la valeur présente dans Google Apps.


TODO :

- FusionDirectory doit remplacer les mots de passe Google Apps par ceux rentrés lors d'une modification sur FusionDirectory
- FusionDirectory est utilisé en tant que source maitre et importe les utilisateurs (Email, Nom, Prénom) dans Google Apps

# Installation

- Faire un git clone du projet ou dézipper le projet sur une machine disposant de PHP. (L'installation sur le serveur Fusion Directory n'est pas obligatoire)
- Modifier le fichier de configuration config.php avec l'adresse de FusionDirectory, le compte admin ainsi que le nombre de caractère pour la génération des mots de passe.
- Lancer le script 

```bash
php sync.php
```

- Lors du premier lancement de cette commande, la demande de token au service Google Apps est demandé.

```bash
php sync.php
Open the following link in your browser:
https://accounts.google.com/o/oauth2/auth?response_type=code&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&client_id=*******.apps.googleusercontent.com&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadmin.directory.user.readonly+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadmin.directory.group.readonly+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadmin.directory.orgunit.readonly&access_type=offline&approval_prompt=auto
Enter verification code:
```

- Cliquez sur le lien et entrer ensuite le token obtenu une fois loggé sur un compte Google Apps disposant du statut administrateur. (Un fichier sera créé dans votre "home" : ~/.credentials/admin-directory_v1-php-quickstart.json )

- Relancez le script à chaque fois que vous souhaitez effectuer une synchronisation.