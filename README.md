# Facture Electronique Tunisie - TTN El Fatoora - Site Web PHP

Conversion WINDEV24 Desktop -> Web PHP 8.2 - TEIF v1.9.0 + QR Code CEV 2D-Doc

## Déploiement rapide

### Option 1: Hébergement mutualisé (OVH, Hostinger, o2switch)
1. Uploader tout le dossier via FTP dans public_html/
2. Aller sur votredomaine.com/install.php
3. Ouvrir votredomaine.com/index.php - C'est en ligne !

### Option 2: Docker (Render, Railway, VPS)
```
docker-compose up -d
```
Ouvre http://localhost:8000

### Option 3: PHP Built-in
```
composer install
php -S 0.0.0.0:8000
```

## Fonctionnalités
- Upload XLS (fact.xlsx) -> Génération XML TEIF 1.9.0 (withoutSig)
- Signature XAdES TunTrust (optionnelle)
- Envoi TTN SFTP / API REST
- Récupération automatique Référence Unique + QR Code CEV + XML final signé TTN
- Génération PDF A4 avec QR 35x35mm
- Archivage 10 ans (MySQL)

## QR Code TTN - Contenu obligatoire
Le QR généré par TTN contient:
- Référence Unique TTN (ex: 0736-2026-20260001-000123)
- MF Fournisseur + MF Client
- Num facture + Date + HT/TTC
- Cachet Electronique Visible CEV 2D-Doc ANCE signé
- URL verif: https://el-fatoora.tn/verify?ref=...

## Configuration TTN
Editez config.php ou variables d'environnement:
TTN_LOGIN, TTN_PASS, TTN_TOKEN, TTN_MODE=simulation|sftp|api

## Structure
- index.php : UI principale
- TeifGenerator.php : Logique XLS -> XML (ton lecturexls.txt)
- envoi_ttn.php : Envoi TTN + QR
- exports/ : XML + PDF + QR générés
- uploads/ : XLS uploadés
