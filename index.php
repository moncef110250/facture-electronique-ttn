<?php
// index.php - Application Web Facture Electronique Tunisie TTN
session_start();
require 'TeifGenerator.php';

$message = "";
$result = null;

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['xlsfile'])) {
    $upload = __DIR__.'/uploads/'.basename($_FILES['xlsfile']['name']);
    @mkdir(__DIR__.'/uploads', 0777, true);
    @mkdir(__DIR__.'/exports', 0777, true);
    move_uploaded_file($_FILES['xlsfile']['tmp_name'], $upload);
    
    try {
        $gen = new TeifGenerator();
        $gen->lireXLS($upload);
        $result = $gen->genererXML_TEIF_190();
        $message = "XML TEIF 1.9.0 généré avec succès !";
        $_SESSION['last_xml'] = $result['path'];
        $_SESSION['last_data'] = $result['data'];
    } catch(Exception $e) {
        $message = "Erreur: ".$e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture Electronique Tunisie - TTN El Fatoora - Web</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f5f7fb}
.card{border-radius:18px; box-shadow:0 10px 30px rgba(0,0,0,.07)}
.qr-box{background:white; padding:20px; border-radius:12px; text-align:center}
</style>
</head>
<body>
<div class="container py-4">
<h2 class="mb-4">🇹🇳 Facture Electronique TTN - Version Web PHP</h2>
<p class="text-muted">Conversion de ton appli WINDEV 24 Desktop → Web. Même logique que <code>lecturexls.txt</code> + <code>fact.xlsx</code></p>

<div class="row">
<div class="col-md-6">
<div class="card p-4 mb-4">
<h5>1. Upload XLS (fact.xlsx)</h5>
<form method="post" enctype="multipart/form-data">
<input type="file" name="xlsfile" accept=".xlsx,.xls" class="form-control mb-3" required>
<button class="btn btn-primary w-100">Générer XML TEIF 1.9.0</button>
</form>
<?php if($message): ?>
<div class="alert alert-info mt-3"><?=$message?></div>
<?php endif; ?>
</div>

<?php if($result): ?>
<div class="card p-4">
<h5>2. Résultat</h5>
<p><b>Facture:</b> <?=$result['data']['sNumfact']?> | <b>Date:</b> <?=$result['data']['dateObj']->format('d/m/Y')?></p>
<p><b>Fournisseur:</b> <?=$result['data']['sFournisseur']?> (<?=$result['data']['sMatfiscfour']?>)</p>
<p><b>Client:</b> <?=$result['data']['sClient']?> (<?=$result['data']['sMatfisccli']?>)</p>
<p><b>HT:</b> <?=$result['data']['Htfacture']?> | <b>TVA:</b> <?=$result['data']['xtotaltva']?> | <b>Timbre:</b> <?=$result['data']['timbre']?> | <b>TTC:</b> <?=$result['data']['xTtcfacture']?></p>
<a href="exports/<?=basename($result['path'])?>" class="btn btn-success" download>Télécharger XML TEIF v1.9.0 (withoutSig)</a>
<pre class="mt-3 small" style="max-height:300px; overflow:auto; background:#f8f9fa; padding:10px"><?=htmlspecialchars(substr($result['xml'],0,2000))?>...</pre>
</div>
<?php endif; ?>
</div>

<div class="col-md-6">
<div class="card p-4 mb-4">
<h5>3. Envoi TTN + QR Code</h5>
<p class="small text-muted">Après génération, envoi à TTN pour récupérer Référence Unique + QR Code + CEV</p>

<?php if($result): ?>
<form method="post" action="envoi_ttn.php">
<input type="hidden" name="xml_path" value="<?=$result['path']?>">
<div class="mb-2">
<label>Mode envoi</label>
<select name="mode" class="form-select">
<option value="api">API REST TTN (test)</option>
<option value="sftp">SFTP TTN (prod)</option>
<option value="simulation">SIMULATION (sans TTN)</option>
</select>
</div>
<div class="mb-2">
<label>Token TTN / Login SFTP</label>
<input type="text" name="token" class="form-control" placeholder="Bearer token ou login SFTP">
</div>
<button class="btn btn-dark w-100">Envoyer à TTN et récupérer QR</button>
</form>

<div class="mt-4 p-3 bg-light rounded">
<h6>Que doit contenir le QR Code TTN ?</h6>
<ul class="small">
<li><b>Référence Unique TTN</b> ex: 0736-2026-20260001-000123 (attribuée par TTN)</li>
<li><b>MF Fournisseur + MF Client</b></li>
<li><b>Num facture + Date + Montant HT/TTC</b></li>
<li><b>Cachet Electronique Visible CEV 2D-Doc ANCE</b> (QR signé)</li>
<li>URL vérif: https://el-fatoora.tn/verify?ref=...</li>
</ul>
<p class="small text-danger"><b>Important:</b> Tu ne génères pas le QR toi-même. C'est TTN qui te le donne après validation.</p>
</div>
<?php else: ?>
<p class="text-muted">Génère d'abord le XML</p>
<?php endif; ?>
</div>

<div class="card p-4">
<h5>4. Facture avec QR (aperçu final)</h5>
<div class="qr-box">
<?php if(isset($_SESSION['qr'])): ?>
<img src="<?=$_SESSION['qr']?>" style="width:180px; height:180px"><br>
<small>Réf TTN: <?=$_SESSION['ref_ttn']?></small>
<?php else: ?>
<div style="width:180px; height:180px; background:#eee; margin:0 auto; display:flex; align-items:center; justify-content:center">QR Code TTN<br>2.5x2.5cm</div>
<small class="text-muted">QR apparaîtra ici après envoi TTN</small>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>

<div class="mt-4 card p-4">
<h5>Architecture PHP Web (conversion WINDEV24)</h5>
<pre class="small">WINDEV Desktop:
  lecturexls.txt (xlsOuvre, xlsDonnée) -> tableaux tabARTICLE[] + totaux
  Génération XML texte pur (WINDEV24_TEXTE_PUR.wl)
  sFTPConnecte + sFTPEnvoieFichier -> TTN

PHP Web:
  PhpSpreadsheet (équivalent xlsOuvre) -> TeifGenerator.php::lireXLS()
  Génération XML TEIF 1.9.0 (même structure)
  phpseclib SFTP + Guzzle HTTP pour API TTN
  Base MySQL pour archivage 10 ans obligatoire

Base de données MySQL:
  CREATE TABLE factures (id, numfact, datefact, mf_fournisseur, mf_client, ht, tva, timbre, ttc, xml_path, ref_ttn, qr_path, cev, statut)
</pre>
</div>

</div>
</body>
</html>
