<?php
require 'config.php'; requireLogin();
$user = $_SESSION['user'];
$pdo = db();

$date_debut = $_GET['date_debut'] ?? date('Y-01-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');

function fmtFR($d){ 
    try{ return (new DateTime($d))->format('d/m/Y'); } 
    catch(Exception $e){ return $d; } 
}

// RECUPERATION PERSISTANTE depuis BDD (pas de session)
$stmt = $pdo->prepare("SELECT * FROM factures WHERE user_id=? AND datefact BETWEEN ? AND ? ORDER BY datefact ASC, id ASC");
$stmt->execute([$user['id'], $date_debut, $date_fin]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si aucune facture sur periode, on recupere quand meme les infos pour diagnostic
$totalCountStmt = $pdo->prepare("SELECT COUNT(*) FROM factures WHERE user_id=?");
$totalCountStmt->execute([$user['id']]);
$totalAll = $totalCountStmt->fetchColumn();

$totHT=0; $totTVA=0; $totTimbre=0; $totTTC=0;
foreach($factures as $f){ $totHT+=$f['ht']; $totTVA+=$f['tva']; $totTimbre+=$f['timbre']; $totTTC+=$f['ttc']; }

$raison = $user['raison_sociale'] ?? $user['login'] ?? '';
$mf = $user['matricule_fiscal'] ?? '';
$adresse = $user['adresse'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8">
<title>Etat Factures - <?=fmtFR($date_debut)?> au <?=fmtFR($date_fin)?></title>
<style>
@page { size: A4 portrait; margin: 12mm 10mm 15mm 10mm; }
* { box-sizing:border-box; }
body{font-family: 'Inter', Arial, sans-serif; margin:0; background:#e2e8f0; color:#0f172a; font-size:12px; -webkit-print-color-adjust:exact; print-color-adjust:exact;}
.no-print{}
.page{max-width:210mm; margin:0 auto; background:white; padding:12mm 10mm; min-height:277mm;}
.header{border-bottom:2px solid #0f172a; padding-bottom:10px; margin-bottom:12px; display:flex; justify-content:space-between;}
.header-left h1{margin:0; font-size:18px; text-transform:uppercase; letter-spacing:0.5px;}
.header-left h2{margin:2px 0 0; font-size:11px; color:#475569; font-weight:400;}
.header-right{text-align:right; font-size:10px; color:#334155;}
.company-block{border:1px solid #cbd5e1; border-radius:8px; padding:8px 12px; margin-bottom:10px; display:flex; justify-content:space-between; background:#f8fafc;}
.company-block small{display:block; font-size:8px; text-transform:uppercase; color:#64748b; letter-spacing:0.5px; margin-bottom:2px;}
.periode{text-align:center; background:#0f172a; color:white; border-radius:6px; padding:8px; margin-bottom:10px; font-size:12px;}
.periode b{font-size:13px;}
table{width:100%; border-collapse:collapse; font-size:10px;}
th{background:#f1f5f9; text-align:left; padding:6px 5px; border:1px solid #cbd5e1; font-size:8.5px; text-transform:uppercase;}
td{padding:5px; border:1px solid #e2e8f0; vertical-align:top;}
tfoot th{background:#0f172a; color:white; border:1px solid #0f172a; font-size:10px;}
.text-right{text-align:right;}
.footer{margin-top:12px; border-top:1px solid #cbd5e1; padding-top:6px; font-size:8px; color:#64748b; display:flex; justify-content:space-between;}
/* IMPRESSION PROPRE COMPTABLE - seulement l'etat */
@media print{
  body{background:white; margin:0;}
  .no-print{display:none !important;}
  .page{box-shadow:none; margin:0; padding:0; max-width:100%; border-radius:0;}
  thead{display:table-header-group;}
  tfoot{display:table-footer-group;}
  tr{page-break-inside:avoid;}
}
@media screen{
  .page{box-shadow:0 4px 24px rgba(0,0,0,.12); border-radius:8px; margin:20px auto;}
}
</style>
</head>
<body>

<div class="no-print" style="max-width:210mm; margin:14px auto; display:flex; justify-content:space-between; align-items:center; background:white; padding:10px 14px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06);">
  <a href="dashboard.php" style="text-decoration:none; color:#0f172a; font-weight:600; font-size:12px; border:1px solid #cbd5e1; padding:8px 14px; border-radius:8px;">← Retour Dashboard</a>
  <div style="display:flex; gap:8px; align-items:center;">
    <span style="font-size:11px; color:#64748b;">Aperçu avant impression comptable - <?=count($factures)?> fact. / <?=$totalAll?> au total en base</span>
    <button onclick="window.print()" style="background:#0f172a; color:white; border:0; padding:8px 14px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:600;">🖨️ Imprimer / PDF</button>
  </div>
</div>

<div class="page">
  <div class="header">
    <div class="header-left">
      <h1 style="font-size:20px; text-transform:none;"><?=htmlspecialchars($raison)?></h1>
      <h2 style="font-size:12px; color:#0f172a; font-weight:600;">Matricule Fiscal : <?=htmlspecialchars($mf)?></h2>
      <?php if($adresse):?><div style="margin-top:3px; font-size:10px; color:#475569;"><?=htmlspecialchars($adresse)?></div><?php endif;?>
    </div>
    <div class="header-right">
      <div style="display:inline-block; background:#dcfce7; color:#166534; border-radius:12px; padding:3px 8px; font-size:8px; font-weight:700;">CONFORME TTN</div>
      <div style="margin-top:6px;">Edité le <?=date('d/m/Y H:i')?></div>
    </div>
  </div>

  <div class="periode">
    Etat des factures emises - Periode du <b><?=fmtFR($date_debut)?></b> au <b><?=fmtFR($date_fin)?></b> &nbsp;•&nbsp; <?=count($factures)?> facture(s)
  </div>

  <?php if(count($factures)>0):?>
  <table>
    <thead>
      <tr>
        <th style="width:18mm;">N° Facture</th>
        <th style="width:18mm;">Date</th>
        <th>Client</th>
        <th style="width:22mm;">MF Client</th>
        <th class="text-right" style="width:18mm;">HT</th>
        <th class="text-right" style="width:16mm;">TVA</th>
        <th class="text-right" style="width:14mm;">Timbre</th>
        <th class="text-right" style="width:22mm;">TTC</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($factures as $f):?>
      <tr>
        <td><b><?=htmlspecialchars($f['numfact'])?></b></td>
        <td><?=fmtFR($f['datefact'])?></td>
        <td><?=htmlspecialchars($f['client'])?><br><span style="font-size:8px; color:#64748b;">ID: <?=$f['id']?></span></td>
        <td style="font-size:9px;"><?=htmlspecialchars($f['mf_client'])?></td>
        <td class="text-right"><?=number_format($f['ht'],3,',',' ')?></td>
        <td class="text-right"><?=number_format($f['tva'],3,',',' ')?></td>
        <td class="text-right"><?=number_format($f['timbre'],3,',',' ')?></td>
        <td class="text-right"><b><?=number_format($f['ttc'],3,',',' ')?></b></td>
      </tr>
      <?php endforeach;?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-right">TOTAUX PERIODE</th>
        <th class="text-right"><?=number_format($totHT,3,',',' ')?></th>
        <th class="text-right"><?=number_format($totTVA,3,',',' ')?></th>
        <th class="text-right"><?=number_format($totTimbre,3,',',' ')?></th>
        <th class="text-right"><?=number_format($totTTC,3,',',' ')?> TND</th>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 10px; font-size:9px; display:flex; gap:12px; flex-wrap:wrap;">
    <div>Nb factures: <b><?=count($factures)?></b> / <?=$totalAll?> en base</div>
    <div>Total HT: <b><?=number_format($totHT,3,',',' ')?> TND</b></div>
    <div>Total TVA: <b><?=number_format($totTVA,3,',',' ')?> TND</b></div>
    <div>Total TTC: <b><?=number_format($totTTC,3,',',' ')?> TND</b></div>
  </div>

  <?php else:?>
  <div style="padding:30px; text-align:center; border:2px dashed #cbd5e1; border-radius:10px;">
    <b>Aucune facture sur cette periode</b><br>
    <span style="font-size:10px; color:#64748b;">Periode du <?=fmtFR($date_debut)?> au <?=fmtFR($date_fin)?> - 0 resultat<br>Total en base pour user <?=$user['id']?> : <?=$totalAll?> factures<br>Verifiez vos dates ou importez des factures</span>
  </div>
  <?php endif;?>

  <div class="footer">
    <div>El Fatoora Pro - Document comptable - TTN TEIF 1.9.0 - A transmettre au comptable</div>
    <div>Page imprimable - <?=count($factures)?> ligne(s) - Periode <?=fmtFR($date_debut)?> au <?=fmtFR($date_fin)?></div>
  </div>
</div>

</body></html>
