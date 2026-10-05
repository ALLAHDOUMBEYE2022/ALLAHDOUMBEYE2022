# MaxiTech — Site e-commerce & services informatiques

Site web dynamique (PHP + MySQL) de **MaxiTech**, boutique d'ordinateurs, d'accessoires et de services informatiques à N'Djamena (Tchad). Les prix sont en FCFA.

## Fonctionnalités

### Site public
- **Accueil** : bandeau promo avec compte à rebours, catégories, offres du moment, produits phares, meilleures ventes, services, avis clients.
- **Boutique** : recherche, filtres (catégorie, promo, reconditionné, en stock, budget), tri et pagination.
- **Fiche produit** : caractéristiques, économie réalisée, alerte « stock limité », « Acheter maintenant », accessoires suggérés, produits similaires.
- **Panier** : ajout sans rechargement de page, **codes promo**, barre de progression vers la **livraison offerte**.
- **Commande** : paiement à la livraison, **Mobile Money (Airtel / Moov)** ou finalisation sur **WhatsApp**. Le stock est mis à jour automatiquement.
- **Confirmation** avec récapitulatif envoyable sur WhatsApp, et **suivi de commande** (référence + téléphone).
- **Services** : 9 services détaillés et formulaire de **demande d'intervention / devis**.
- **Contact** (avec protection anti-robots), **À propos** + FAQ, **newsletter**.
- Bouton **WhatsApp flottant** sur toutes les pages, design **responsive** (mobile, tablette, ordinateur).

### Administration (`/admin`)
- Tableau de bord : chiffre d'affaires, nouvelles commandes, stock faible, meilleures ventes.
- Gestion des **commandes** (statuts, remise en stock en cas d'annulation, contact WhatsApp du client).
- **Produits** (ajout de photos, promos, mise en avant, stock rapide), **catégories**, **services**.
- **Demandes de service**, **messages**, **abonnés newsletter** (export Excel/CSV).
- Gestion des comptes administrateurs (changement de mot de passe, comptes employés).

## Installation chez un hébergeur (o2switch, Hostinger, LWS…) ou en local (XAMPP / WAMP)

1. Créez une base de données MySQL (ex. `maxitech`) depuis le panneau de votre hébergeur ou phpMyAdmin.
2. Ouvrez `config.php` et renseignez `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Envoyez tous les fichiers sur le serveur (dossier `public_html` ou `htdocs`).
4. Ouvrez `https://votre-site/install.php` : choisissez l'identifiant et le mot de passe administrateur, cochez « données de démonstration » si vous le souhaitez.
5. **Supprimez `install.php`** du serveur une fois l'installation terminée.
6. Connectez-vous sur `https://votre-site/admin/`.

Prérequis : PHP 8.1 ou plus récent avec PDO MySQL (présent chez tous les hébergeurs courants). Les dossiers `uploads/` et `data/` doivent être accessibles en écriture.

### Test rapide sans MySQL
```bash
MAXITECH_DB_DRIVER=sqlite php -S localhost:8000
```
Puis ouvrez http://localhost:8000.

## Personnalisation

Tout se règle dans **`config.php`** :

| Réglage | Rôle |
|---|---|
| `SITE_PHONE`, `SITE_WHATSAPP`, `SITE_EMAIL`, `SITE_ADDRESS`, `SITE_HOURS` | Coordonnées affichées partout |
| `SITE_FACEBOOK`, `SITE_INSTAGRAM`, `SITE_TIKTOK` | Liens vers les réseaux sociaux |
| `DELIVERY_FEE`, `FREE_DELIVERY_FROM` | Frais de livraison et seuil de gratuité |
| `MOBILE_MONEY` | Numéros Airtel Money / Moov Money |
| `COUPONS` | Codes promo (pourcentage ou montant fixe, panier minimum) |
| `PROMO_BANNER`, `PROMO_END` | Bandeau promotionnel et date de fin du compte à rebours |
| `TESTIMONIALS` | Avis clients de la page d'accueil |

Les produits, catégories et services se gèrent depuis l'administration. Sans photo, un produit affiche l'illustration de sa catégorie : ajoutez de vraies photos (fond blanc, format carré ou 4:3) pour de meilleures ventes.

## Structure

```
index.php, boutique.php, produit.php, panier.php, commande.php,
confirmation.php, suivi.php, services.php, contact.php, apropos.php, newsletter.php
install.php          Installateur (à supprimer après usage)
config.php           Réglages de l'entreprise et de la base de données
includes/            Connexion BD, fonctions, en-tête/pied de page, schéma
admin/               Espace d'administration
assets/              CSS, JavaScript, illustrations SVG
uploads/             Photos des produits envoyées depuis l'admin
data/                Verrou d'installation (et base SQLite en mode test)
```

## Sécurité
Requêtes préparées (PDO), protection CSRF sur tous les formulaires, mots de passe chiffrés (`password_hash`), échappement HTML systématique, vérification du type réel des images envoyées, exécution de scripts interdite dans `uploads/`, accès direct bloqué à `includes/` et `data/`.
