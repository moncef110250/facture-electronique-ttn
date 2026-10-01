<?php require 'config.php'; requireLogin();
$id = intval($_GET['id'] ?? 0); $type = $_GET['type'] ?? 'xml';
$stmt = db()->prepare("SELECT * FROM factures WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$f && isAdmin()){
    $stmt = db()->prepare("SELECT * FROM factures WHERE id=?"); $stmt->execute([$id]); $f=$stmt->fetch(PDO::FETCH_ASSOC);
}
if(!$f) die('Facture introuvable');
$path = $type=='pdf' ? $f['pdf_path'] : $f['xml_path'];
if(!file_exists($path)) die('Fichier introuvable: '.$path);
if($type=='pdf'){ header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="'.basename($path).'"'); }
else { header('Content-Type: application/xml'); header('Content-Disposition: attachment; filename="'.basename($path).'"'); }
readfile($path);
