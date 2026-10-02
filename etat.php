<?php
require 'config.php'; requireLogin();
$user = $_SESSION['user'];
$pdo = db();

$date_debut = $_GET['date_debut'] ?? date('Y-01-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');

// Format JJ/MM/AAAA pour l'entête demandé
function fmtFR($d){ 
    try{ return (new DateTime($d))->format('d/m/Y'); } 
    catch(Exception $e){ return $d; } 
}

$stmt = $pdo->prepare("SELECT * FROM factures WHERE user_id=? AND datefact BETWEEN ? AND ? ORDER BY datefact ASC, numfact ASC");
$stmt->execute([$user['id'], $date_debut, $date_fin]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totaux
$totHT=0; $totTVA=0; $totTimbre=0; $totTTC=0;
foreach($factures as $f){ $totHT+=$f['ht']; $totTVA+=$f['tva']; $totTimbre+=$f['timbre']; $totTTC+=$f['ttc']; }

// Info fournisseur (user connecté)
$raison = $user['raison_sociale'] ?? '';
$mf = $user['matricule_fiscal'] ?? $user['mf_fournisseur'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>État des Factures - Période <?=fmtFR($date_debut)?> au <?=fmtFR($date_fin)?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
body{font-family:Inter,Arial,sans-serif; margin:0; background:#f8fafc; color:#0f172a}
.page{max-width:1100px; margin:20px auto; background:white; padding:32px; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.06)}
.header{display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #0f172a; padding-bottom:16px; margin-bottom:20px}
.header h1{margin:0; font-size:22px; letter-spacing:-0.5px}
.header h2{margin:4px 0 0; font-size:13px; font-weight:400; color:#64748b}
.badge{display:inline-block; background:#dcfce7; color:#166534; border-radius:20px; padding:4px 10px; font-size:11px; font-weight:600}
.info-box{background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; margin-bottom:18px; display:flex; justify-content:space-between}
.info-box small{color:#64748b; display:block; font-size:11px; text-transform:uppercase; letter-spacing:.5px}
.periode{text-align:center; background:#0f172a; color:white; border-radius:12px; padding:12px 18px; margin-bottom:22px; font-size:15px; letter-spacing:.2px}
.periode b{font-size:16px}
table{width:100%; border-collapse:collapse; font-size:13px}
th{background:#f1f5f9; text-align:left; padding:10px 8px; border-bottom:2px solid #0f172a; font-size:11px; text-transform:uppercase; letter-spacing:.4px}
td{padding:9px 8px; border-bottom:1px solid #e2e8f0}
tfoot th{background:#0f172a; color:white; border:0; padding:12px 8px; font-size:13px}
tfoot th:first-child{border-radius:8px 0 0 8px}
tfoot th:last-child{border-radius:0 8px 8px 0}
.text-right{text-align:right} .text-center{text-align:center}
.footer{margin-top:28px; display:flex; justify-content:space-between; font-size:11px; color:#64748b; border-top:1px dashed #cbd5e1; padding-top:12px}
/* btn-bar removed */
.btn{padding:10px 18px; border-radius:10px; border:0; cursor:pointer; font-weight:600; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px}
.btn-dark{background:#0f172a; color:white} .btn-outline{background:white; border:1px solid #cbd5e1}
@media print{
  body{background:white} .page{box-shadow:none; margin:0; border-radius:0; padding:20px} 
}
</style>
</head>
<body>

<div style="display:flex; justify-content:center; gap:12px; margin:20px auto; max-width:1100px;">
<a href="dashboard.php" style="background:white; border:1px solid #cbd5e1; padding:10px 18px; border-radius:10px; text-decoration:none; color:#0f172a; font-weight:600; font-size:13px;">← Retour Dashboard</a>
<span style="font-size:12px; color:#64748b; align-self:center;">Ctrl+P pour imprimer / Enregistrer en PDF</span>
</div>

<div class="page">
<div class="header">
<div>
<h1>🇹🇳 État des Factures Émises</h1>
<h2>El Fatoora Pro - Facturation Électronique TTN TEIF 1.9.0</h2>
</div>
<div style="text-align:right">
<span class="badge">Conforme TTN</span><br>
<span style="font-size:12px; color:#64748b; margin-top:6px; display:inline-block">Édité le <?=date('d/m/Y H:i')?></span>
</div>
</div>

<div class="info-box">
<div>
<small>Fournisseur / Émetteur</small>
<b><?=htmlspecialchars($raison)?></b><br>
<span style="font-size:12px">MF: <?=htmlspecialchars($mf)?></span>
</div>
<div style="text-align:right">
<small>Utilisateur</small>
<b><?=htmlspecialchars($user['login'])?></b><br>
<span style="font-size:12px"><?=htmlspecialchars($user['email'])?></span>
</div>
</div>

<div class="periode">
Période du <b><?=fmtFR($date_debut)?></b> au <b><?=fmtFR($date_fin)?></b> &nbsp; • &nbsp; <?=count($factures)?> facture(s)
</div>

<?php if(count($factures)>0):?>
<table>
<thead>
<tr>
<th style="width:90px">N° Facture</th>
<th style="width:85px">Date</th>
<th>Client</th>
<th style="width:110px">MF Client</th>
<th class="text-right" style="width:90px">HT</th>
<th class="text-right" style="width:80px">TVA</th>
<th class="text-right" style="width:70px">Timbre</th>
<th class="text-right" style="width:100px">TTC</th>
</tr>
</thead>
<tbody>
<?php foreach($factures as $f):?>
<tr>
<td><b><?=htmlspecialchars($f['numfact'])?></b></td>
<td><?=fmtFR($f['datefact'])?></td>
<td><?=htmlspecialchars($f['client'])?></td>
<td style="font-size:11px; color:#475569"><?=htmlspecialchars($f['mf_client'])?></td>
<td class="text-right"><?=number_format($f['ht'],3,',',' ')?></td>
<td class="text-right"><?=number_format($f['tva'],3,',',' ')?></td>
<td class="text-right"><?=number_format($f['timbre'],3,',',' ')?></td>
<td class="text-right"><b><?=number_format($f['ttc'],3,',',' ')?> TND</b></td>
</tr>
<?php endforeach;?>
</tbody>
<tfoot>
<tr>
<th colspan="4" class="text-right">TOTAUX PÉRIODE</th>
<th class="text-right"><?=number_format($totHT,3,',',' ')?></th>
<th class="text-right"><?=number_format($totTVA,3,',',' ')?></th>
<th class="text-right"><?=number_format($totTimbre,3,',',' ')?></th>
<th class="text-right"><?=number_format($totTTC,3,',',' ')?> TND</th>
</tr>
</tfoot>
</table>

<div style="margin-top:18px; background:#f8fafc; border-radius:10px; padding:12px 16px; font-size:12px; display:flex; gap:24px">
<div>Nombre factures : <b><?=count($factures)?></b></div>
<div>Total HT : <b><?=number_format($totHT,2,',',' ')?> TND</b></div>
<div>Total TVA : <b><?=number_format($totTVA,2,',',' ')?> TND</b></div>
<div>Total TTC : <b><?=number_format($totTTC,2,',',' ')?> TND</b></div>
</div>

<?php else:?>
<div style="padding:40px; text-align:center; border:2px dashed #cbd5e1; border-radius:16px; background:#f8fafc">
<p>⚠️ Aucune facture sur cette période</p>
<p style="font-size:12px; color:#64748b">Période du <?=fmtFR($date_debut)?> au <?=fmtFR($date_fin)?> - Aucun résultat<br>Essayez d'élargir les dates dans le Dashboard</p>
</div>
<?php endif;?>

<div class="footer">
<div>El Fatoora Pro - TTN TEIF 1.9.0 - Document généré automatiquement</div>
<div>Page 1/1 - <?=count($factures)?> ligne(s)</div>
</div>

</div>



</body></html>
