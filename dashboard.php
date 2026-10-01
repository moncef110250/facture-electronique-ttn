<?php require 'config.php'; requireLogin();
$user = $_SESSION['user'];
$pdo = db();
$message=''; $result=null;

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['xlsfile'])){
    $upload = __DIR__.'/uploads/'.basename($_FILES['xlsfile']['name']);
    @mkdir(__DIR__.'/uploads',0777,true); @mkdir(__DIR__.'/exports',0777,true);
    move_uploaded_file($_FILES['xlsfile']['tmp_name'], $upload);
    try{
        require 'TeifGenerator.php';
        $gen = new TeifGenerator();
        $gen->lireXLS($upload);
        $result = $gen->genererXML_TEIF_190();
        // Générer PDF correspondant
        require 'generate_pdf.php';
        $pdfPath = genererFacturePDF($result['data'], $result['path']);
        
        // Sauver en base
        $d=$result['data'];
        $stmt=$pdo->prepare("INSERT INTO factures (user_id,numfact,datefact,mf_fournisseur,fournisseur,mf_client,client,ht,tva,timbre,ttc,xml_path,pdf_path,statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$user['id'],$d['sNumfact'],$d['dateObj']->format('Y-m-d'),$d['sMatfiscfour'],$d['sFournisseur'],$d['sMatfisccli'],$d['sClient'], floatval($d['Htfacture']??0), floatval($d['xtotaltva']??0), floatval($d['timbre']??0), floatval($d['xTtcfacture']??0), $result['path'], $pdfPath, 'brouillon']);
        
        $_SESSION['last_facture_id']=$pdo->lastInsertId();
        $_SESSION['last_xml']=$result['path'];
        $_SESSION['last_pdf']=$pdfPath;
        $_SESSION['last_data']=$d;
        $message='Facture générée avec succès !';
        // Recharge user pour compteur
        $pdo->exec("UPDATE users SET factures_utilisees=factures_utilisees+1 WHERE id=".$user['id']);
    }catch(Exception $e){ $message='Erreur: '.$e->getMessage(); }
}

// Rapport entre 2 dates
$rapport=null;
if(isset($_GET['date_debut']) && isset($_GET['date_fin'])){
    $stmt=$pdo->prepare("SELECT * FROM factures WHERE user_id=? AND datefact BETWEEN ? AND ? ORDER BY datefact");
    $stmt->execute([$user['id'], $_GET['date_debut'], $_GET['date_fin']]);
    $rapport=$stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - El Fatoora Pro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f8fafc;font-family:Inter,system-ui}
.navbar{background:white!important;box-shadow:0 1px 20px rgba(0,0,0,.05)}
.card{border-radius:20px;border:0;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.btn-primary{background:#0f172a;border:0} .btn-primary:hover{background:#1e293b}
.upload-zone{border:2px dashed #cbd5e1;border-radius:16px;padding:40px;text-align:center;background:#f8fafc;transition:.2s}
.upload-zone:hover{border-color:#2563eb;background:#eff6ff}
.badge-ttn{background:#dcfce7;color:#166534}
</style></head>
<body>
<nav class="navbar navbar-expand-lg sticky-top"><div class="container">
<a class="navbar-brand fw-bold" href="dashboard.php">🇹🇳 El Fatoora Pro <span class="badge badge-ttn ms-2">TTN TEIF 1.9.0</span></a>
<div class="ms-auto d-flex align-items-center gap-3">
<span class="small"><?=htmlspecialchars($user['raison_sociale'])?> (<?=$user['forfait']?>)</span>
<?php if(isAdmin()):?><a href="admin.php" class="btn btn-warning btn-sm"><i class="bi bi-shield"></i> Admin</a><?php endif;?>
<a href="logout.php" class="btn btn-outline-dark btn-sm">Déconnexion</a>
</div></div></nav>

<div class="container py-4">
<?php if($message):?><div class="alert alert-success"><?=$message?></div><?php endif;?>

<div class="row g-4">
<!-- Upload -->
<div class="col-lg-7">
<div class="card p-4">
<h5 class="mb-3"><i class="bi bi-cloud-upload"></i> Nouvelle Facture depuis fact.xlsx</h5>
<p class="text-muted small">Même logique WINDEV 24 - lecture A2-A10 + lignes 13-26</p>
<form method="post" enctype="multipart/form-data">
<div class="upload-zone mb-3">
<i class="bi bi-file-earmark-excel" style="font-size:48px;color:#16a34a"></i>
<p class="mt-2 mb-2">Glissez votre <b>fact.xlsx</b> ici ou cliquez</p>
<input type="file" name="xlsfile" accept=".xlsx,.xls" class="form-control" required>
</div>
<button class="btn btn-primary w-100 py-3"><i class="bi bi-magic"></i> Générer XML + PDF</button>
</form>
</div>

<?php if(isset($_SESSION['last_data'])): $d=$_SESSION['last_data']; ?>
<div class="card p-4 mt-4 border-success">
<h5 class="text-success"><i class="bi bi-check-circle"></i> Facture <?=$d['sNumfact']?> générée</h5>
<div class="row small mt-3">
<div class="col-6"><b>Fournisseur:</b><br><?=$d['sFournisseur']?><br><span class="text-muted"><?=$d['sMatfiscfour']?></span></div>
<div class="col-6"><b>Client:</b><br><?=$d['sClient']?><br><span class="text-muted"><?=$d['sMatfisccli']?></span></div>
</div>
<div class="d-flex gap-2 mt-2 small"><span>HT: <b><?=$d['Htfacture']?></b></span><span>TVA: <b><?=$d['xtotaltva']?></b></span><span>Timbre: <?=$d['timbre']?></span><span>TTC: <b><?=$d['xTtcfacture']?> <?=$d['sDevise']?></b></span></div>
<hr>
<p class="small text-muted mb-2">✅ XML conforme TEIF 1.9.0 généré - Aucun code affiché, téléchargement direct :</p>
<div class="d-grid gap-2">
<a href="download.php?type=xml&id=<?=$_SESSION['last_facture_id']?>" class="btn btn-success"><i class="bi bi-filetype-xml"></i> Télécharger XML TEIF 1.9.0</a>
<a href="download.php?type=pdf&id=<?=$_SESSION['last_facture_id']?>" class="btn btn-primary"><i class="bi bi-filetype-pdf"></i> Télécharger Facture PDF</a>
</div>
<div class="mt-3 p-2 bg-light rounded small">📦 QR Code TTN 2.5x2.5cm sera intégré après envoi TTN (référence unique + CEV ANCE)</div>
</div>
<?php endif;?>
</div>

<!-- Rapport -->
<div class="col-lg-5">
<div class="card p-4">
<h5><i class="bi bi-graph-up"></i> État des factures émises</h5>
<p class="small text-muted">Rapport entre 2 dates - Imprimable</p>
<form method="get" class="row g-2 mb-3">
<div class="col-5"><label class="small">Du</label><input type="date" name="date_debut" class="form-control" value="<?=$_GET['date_debut']??date('Y-m-01')?>" required></div>
<div class="col-5"><label class="small">Au</label><input type="date" name="date_fin" class="form-control" value="<?=$_GET['date_fin']??date('Y-m-d')?>" required></div>
<div class="col-2 d-flex align-items-end"><button class="btn btn-dark w-100"><i class="bi bi-search"></i></button></div>
</form>
<?php if($rapport!==null):?>
<div id="rapportPrint">
<div class="d-flex justify-content-between align-items-center mb-2"><h6>Rapport du <?=$_GET['date_debut']?> au <?=$_GET['date_fin']?></h6><button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer"></i> Imprimer</button></div>
<table class="table table-sm small"><thead><tr><th>N°</th><th>Date</th><th>Client</th><th>TTC</th><th>Statut</th></tr></thead><tbody>
<?php $tot=0; foreach($rapport as $f): $tot+= $f['ttc']; ?>
<tr><td><?=$f['numfact']?></td><td><?=$f['datefact']?></td><td><?=substr($f['client'],0,15)?></td><td><?=$f['ttc']?></td><td><span class="badge bg-secondary"><?=$f['statut']?></span></td></tr>
<?php endforeach;?>
</tbody><tfoot><tr><th colspan="3">Total</th><th><?=$tot?> TND</th><th><?=count($rapport)?> fact.</th></tr></tfoot></table>
</div>
<?php else:?>
<p class="text-muted small">Sélectionnez 2 dates pour générer l'état.</p>
<?php endif;?>
</div>

<div class="card p-4 mt-4">
<h6><i class="bi bi-credit-card"></i> Mon Abonnement</h6>
<p class="small">Forfait: <b><?=strtoupper($user['forfait'])?></b> | Utilisées: <b><?=$user['factures_utilisees']?></b></p>
<?php if($user['forfait']=='usage'):?><div class="alert alert-info small">1 DT par facture émise - Recharge à prévoir</div><?php endif;?>
<a href="index.php#tarifs" class="btn btn-outline-primary btn-sm w-100">Voir tarifs / Changer forfait</a>
</div>
</div>
</div>

<div class="mt-4 card p-3 small text-muted">Architecture: WINDEV24 lecturexls.txt → PHP TeifGenerator.php::lireXLS() + genererXML_TEIF_190() + genererFacturePDF() - Conforme TTN El Fatoora</div>
</div>
</body></html>
