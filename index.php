<?php require 'config.php'; ?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>El Fatoora Pro - Facture Electronique TTN Tunisie</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
body{font-family:Inter,system-ui;background:#f8fafc}
.navbar{background:rgba(255,255,255,.9)!important;backdrop-filter:blur(12px);box-shadow:0 1px 20px rgba(0,0,0,.05)}
.hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#2563eb 100%);color:white;border-radius:0 0 40px 40px;padding:80px 0 100px}
.card{border-radius:20px;border:0;box-shadow:0 8px 30px rgba(0,0,0,.06)}
.btn-primary{background:#0f172a;border:0;padding:12px 24px;border-radius:12px} .btn-primary:hover{background:#1e293b}
.btn-success{background:#16a34a;border:0;border-radius:12px}
.pricing-card{transition:.2s} .pricing-card:hover{transform:translateY(-6px);box-shadow:0 20px 40px rgba(0,0,0,.12)}
.badge-ttn{background:#dcfce7;color:#166534;border-radius:20px;padding:6px 12px}
.upload-demo{border:2px dashed #cbd5e1;border-radius:16px;padding:20px;background:white}
</style></head>
<body>
<nav class="navbar navbar-expand-lg sticky-top"><div class="container">
<a class="navbar-brand fw-bold" href="#">🇹🇳 El Fatoora Pro <span class="badge-ttn ms-2">TTN TEIF 1.9.0</span></a>
<div class="ms-auto d-flex gap-2">
<a href="#tarifs" class="btn btn-outline-dark btn-sm">Tarifs</a>
<?php if(isLogged()):?><a href="dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
<?php else:?><a href="login.php" class="btn btn-outline-dark btn-sm">Connexion</a><a href="register.php" class="btn btn-primary btn-sm">Ouvrir un compte</a><?php endif;?>
</div></div></nav>

<section class="hero"><div class="container"><div class="row align-items-center">
<div class="col-lg-6">
<h1 class="display-5 fw-bold mb-3">Facturation électronique<br>conforme TTN en 30s</h1>
<p class="lead opacity-75 mb-4">Conversion de votre appli WINDEV 24 → SaaS Web PHP. Générez XML TEIF 1.9.0 + PDF avec QR Code CEV ANCE. Validation admin, abonnements payants.</p>
<div class="d-flex gap-3 mb-4">
<a href="register.php" class="btn btn-light btn-lg"><i class="bi bi-rocket"></i> Créer mon compte</a>
<a href="login.php" class="btn btn-outline-light btn-lg">Se connecter</a>
</div>
<div class="d-flex gap-4 small"><span><i class="bi bi-check-circle"></i> Sans affichage XML brut</span><span><i class="bi bi-check-circle"></i> Boutons téléchargement direct</span><span><i class="bi bi-check-circle"></i> Rapport imprimable</span></div>
</div>
<div class="col-lg-6 mt-4 mt-lg-0">
<div class="card p-4 text-dark">
<div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0"><i class="bi bi-file-earmark-excel text-success"></i> fact.xlsx → TTN</h6><span class="badge bg-success">TEIF 1.9.0</span></div>
<div class="upload-demo text-center mb-3"><i class="bi bi-cloud-upload" style="font-size:32px;color:#2563eb"></i><p class="small mb-1 mt-2">Glissez fact.xlsx ici</p><span class="small text-muted">A2 Fournisseur, A7 Client, lignes 13-26 articles</span></div>
<div class="p-3 bg-light rounded">
<div class="d-flex justify-content-between small mb-2"><span>Facture 20260001 - 25/08/2026</span><span class="badge bg-dark">2000 HT</span></div>
<div class="d-grid gap-2">
<button class="btn btn-success btn-sm"><i class="bi bi-filetype-xml"></i> Télécharger XML TEIF 1.9.0</button>
<button class="btn btn-primary btn-sm"><i class="bi bi-filetype-pdf"></i> Télécharger Facture PDF</button>
</div>
<div class="mt-2 small text-muted text-center">QR Code TTN 2.5x2.5cm + CEV après validation</div>
</div>
</div>
</div>
</div></div></section>

<section class="container py-5"><div class="row g-4">
<div class="col-md-4"><div class="card p-4 h-100"><i class="bi bi-shield-check" style="font-size:32px;color:#2563eb"></i><h5 class="mt-3">Conforme TTN 2026</h5><p class="small text-muted">TEIF 1.9.0, signature électronique, conservation 10 ans obligatoire. Plus de Deprecated, fix 46259 Excel.</p></div></div>
<div class="col-md-4"><div class="card p-4 h-100"><i class="bi bi-printer" style="font-size:32px;color:#f59e0b"></i><h5 class="mt-3">Rapport imprimable</h5><p class="small text-muted">État des factures émises entre 2 dates, total HT/TVA/TTC, bouton Imprimer intégré.</p></div></div>
<div class="col-md-4"><div class="card p-4 h-100"><i class="bi bi-people" style="font-size:32px;color:#16a34a"></i><h5 class="mt-3">SaaS Payant</h5><p class="small text-muted">Comptes clients, validation par admin, blocage, forfaits mensuel/annuel/usage.</p></div></div>
</div></section>

<section id="tarifs" class="container py-5"><h2 class="text-center fw-bold mb-1">Tarifs simples et transparents</h2><p class="text-center text-muted mb-5">Service payant - Validation par administrateur</p>
<div class="row g-4 justify-content-center">
<div class="col-md-4"><div class="card p-4 pricing-card h-100"><h5>À l'usage</h5><h2 class="fw-bold">1 DT <small class="fs-6 text-muted">/ facture</small></h2><ul class="small mt-3"><li>XML TEIF 1.9.0 + PDF</li><li>Téléchargements illimités</li><li>Rapport entre 2 dates</li><li>Support email</li></ul><a href="register.php" class="btn btn-outline-dark w-100 mt-3">Choisir</a></div></div>
<div class="col-md-4"><div class="card p-4 pricing-card h-100 border-primary border-2"><span class="badge bg-primary mb-2">Populaire</span><h5>Mensuel</h5><h2 class="fw-bold">49 DT <small class="fs-6 text-muted">/ mois</small></h2><ul class="small mt-3"><li><b>Factures illimitées</b></li><li>XML + PDF + QR TTN</li><li>Rapports imprimables</li><li>Support prioritaire</li></ul><a href="register.php" class="btn btn-primary w-100 mt-3">Choisir Mensuel</a></div></div>
<div class="col-md-4"><div class="card p-4 pricing-card h-100"><h5>Annuel</h5><h2 class="fw-bold">490 DT <small class="fs-6 text-muted">/ an</small></h2><p class="small text-success">2 mois offerts</p><ul class="small"><li><b>Factures illimitées</b></li><li>Tout inclus</li><li>Archivage 10 ans</li><li>Accès API TTN</li></ul><a href="register.php" class="btn btn-outline-dark w-100 mt-3">Choisir Annuel</a></div></div>
</div></section>

<footer class="bg-dark text-white py-4 mt-5"><div class="container text-center small"><p class="mb-1">🇹🇳 El Fatoora Pro - Facture Electronique TTN TEIF 1.9.0 - Conversion WINDEV24 → Web PHP</p><p class="opacity-50">Domaine officiel à venir - Test grandeur nature sur Render - Admin: admin/admin123</p></div></footer>
</body></html>
