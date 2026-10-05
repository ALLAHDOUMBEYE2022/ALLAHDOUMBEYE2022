# Mettre le site MaxiTech en ligne gratuitement (InfinityFree)

InfinityFree est un hébergement **gratuit**, **sans publicité**, compatible **PHP + MySQL**, avec certificat **HTTPS gratuit**.
Comptez environ 30 minutes. Les noms de menus peuvent légèrement varier selon les mises à jour d'InfinityFree.

---

## Étape 1 — Créer le compte et l'hébergement

1. Allez sur **https://www.infinityfree.com** → **Register** et créez un compte avec votre e-mail (confirmez l'e-mail reçu).
2. Dans l'espace client, cliquez sur **Create Account** (créer un hébergement).
3. Choisissez **un sous-domaine gratuit**, par exemple `maxitech-tchad` + l'extension proposée
   (ex. `maxitech-tchad.infinityfreeapp.com`). Vous pourrez brancher un vrai nom de domaine plus tard.
4. Validez. L'hébergement est actif en quelques minutes.

## Étape 2 — Créer la base de données MySQL

1. Ouvrez l'hébergement → **Control Panel** (panneau de contrôle) → **MySQL Databases**.
2. Créez une base nommée `maxitech`.
3. Notez les 4 informations affichées (vous en aurez besoin à l'étape 4) :

| Information | Exemple | Où la trouver |
|---|---|---|
| Serveur (hostname) | `sql301.infinityfree.com` | Page *MySQL Databases* |
| Nom de la base | `if0_37000000_maxitech` | Page *MySQL Databases* |
| Utilisateur | `if0_37000000` | Page *MySQL Databases* |
| Mot de passe | (celui de l'hébergement) | Espace client → *Account* → **Show password** |

## Étape 3 — Envoyer les fichiers du site

Utilisez le fichier **`maxitech-site.zip`** fourni.

**Méthode simple (navigateur) :**
1. Dans l'espace client, ouvrez **File Manager**.
2. Entrez dans le dossier **`htdocs`** et supprimez le fichier d'exemple (`index2.html`) s'il existe.
3. Envoyez `maxitech-site.zip` dans `htdocs`, puis faites **clic droit → Extract** (décompresser) directement dans `htdocs`.
4. Vérifiez que `index.php`, `install.php` et les dossiers `admin`, `assets`, `includes`… sont **directement dans `htdocs`**
   (et non dans un sous-dossier). Supprimez ensuite le fichier `.zip`.

**Si l'extraction n'est pas proposée :** décompressez le zip sur votre ordinateur et envoyez les fichiers avec
**FileZilla** (gratuit) en utilisant les identifiants FTP indiqués dans l'espace client (*FTP Details*),
vers le dossier `htdocs`.

## Étape 4 — Lancer l'installation

1. Ouvrez `https://VOTRE-SITE/install.php` (ex. `https://maxitech-tchad.infinityfreeapp.com/install.php`).
2. **Étape 1/2** : saisissez le serveur, le nom de la base, l'utilisateur et le mot de passe notés à l'étape 2 → *Tester et enregistrer*.
3. **Étape 2/2** : choisissez votre identifiant et un **mot de passe administrateur solide** ; laissez cochée
   l'option « Importer le catalogue MaxiTech » → *Installer*.
4. **Supprimez `install.php`** dans le File Manager (sécurité).

> Astuce : si un nouvel hébergement affiche une erreur ou une page « site non trouvé », patientez jusqu'à 1 heure
> le temps que le sous-domaine soit actif partout, puis réessayez.

## Étape 5 — Activer le HTTPS (cadenas)

Espace client → **SSL Certificates** → générez le certificat gratuit pour votre domaine, puis installez-le
(bouton prévu à cet effet). Votre site s'affichera avec le cadenas 🔒, important pour la confiance des clients.

## Étape 6 — Prendre le site en main

- Administration : `https://VOTRE-SITE/admin/`
- **Produits** : ajustez les **stocks** (5 par défaut), ajoutez les **photos** de chaque ordinateur, modifiez les prix.
- **Commandes** et **demandes de service** arrivent dans l'admin ; le client peut aussi vous écrire directement sur WhatsApp.
- Coordonnées, numéros Mobile Money, bandeau promo, codes promo : fichier **`config.php`**
  (modifiable dans le File Manager → clic droit → *Edit*).

## Partager le site sur Facebook

Ajoutez le lien du site :
- dans la section **Infos / Site web** de votre page Facebook ;
- sur le bouton d'action de la page (**Envoyer un message WhatsApp** ou **Voir la boutique**) ;
- dans chaque publication de produit, avec le lien direct de la fiche (ex. `https://VOTRE-SITE/produit.php?slug=...`).

## Limites de l'hébergement gratuit, et quand passer à un hébergement payant

- Convient très bien pour démarrer et pour quelques centaines de visites par jour.
- Pas d'envoi d'e-mails depuis le site (le site n'en a pas besoin : tout passe par l'admin et WhatsApp).
- Pour un nom de domaine à vous (`maxitech-tchad.com`, `maxitech.td`…), plus de rapidité et une adresse e-mail pro,
  un hébergement payant d'entrée de gamme suffit. Le site s'y installe exactement de la même façon (étapes 2 à 4).

## Sauvegarder régulièrement

Control Panel → **phpMyAdmin** → votre base → **Exporter** (format SQL). Faites-le une fois par semaine et
après chaque grosse mise à jour du catalogue. Pensez aussi à garder une copie du dossier `uploads/` (photos des produits).
