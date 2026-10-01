<?php require 'config.php'; requireLogin(); requireAdmin();
$pdo=db();
if(isset($_GET['action']) && isset($_GET['id'])){
    $id=intval($_GET['id']);
    if($_GET['action']=='valider'){ $pdo->exec("UPDATE users SET statut='actif', date_validation=datetime('now') WHERE id=$id"); }
    if($_GET['action']=='bloquer'){ $pdo->exec("UPDATE users SET statut='bloque' WHERE id=$id"); }
    if($_GET['action']=='activer'){ $pdo->exec("UPDATE users SET statut='actif' WHERE id=$id"); }
    header('Location: admin.php'); exit;
}
$users=$pdo->query("SELECT * FROM users ORDER BY date_inscription DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - El Fatoora Pro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f8fafc} .card{border-radius:20px;border:0;box-shadow:0 4px 20px rgba(0,0,0,.06)}</style></head>
<body>
<nav class="navbar bg-white shadow-sm"><div class="container"><a class="navbar-brand fw-bold" href="dashboard.php">🇹🇳 Admin El Fatoora Pro</a>
<div class="ms-auto"><a href="dashboard.php" class="btn btn-outline-dark btn-sm">Dashboard</a> <a href="logout.php" class="btn btn-dark btn-sm">Logout</a></div></div></nav>
<div class="container py-4">
<div class="card p-4">
<h5><i class="bi bi-people"></i> Gestion Clients - Validation / Blocage</h5>
<table class="table table-hover small mt-3"><thead><tr><th>RS</th><th>MF</th><th>Login</th><th>Email</th><th>Forfait</th><th>Utilisées</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $u):?>
<tr><td><?=htmlspecialchars($u['raison_sociale'])?></td><td><?=$u['matricule_fiscal']?></td><td><?=$u['login']?></td><td><?=$u['email']?></td><td><span class="badge bg-info"><?=$u['forfait']?></span></td><td><?=$u['factures_utilisees']?></td>
<td><?php if($u['statut']=='en_attente') echo '<span class="badge bg-warning">En attente</span>'; elseif($u['statut']=='actif') echo '<span class="badge bg-success">Actif</span>'; else echo '<span class="badge bg-danger">Bloqué</span>'; ?></td>
<td>
<?php if($u['statut']=='en_attente'):?><a href="?action=valider&id=<?=$u['id']?>" class="btn btn-success btn-sm">Valider</a><?php endif;?>
<?php if($u['statut']!='bloque'):?><a href="?action=bloquer&id=<?=$u['id']?>" class="btn btn-danger btn-sm">Bloquer</a><?php else:?><a href="?action=activer&id=<?=$u['id']?>" class="btn btn-success btn-sm">Activer</a><?php endif;?>
</td></tr>
<?php endforeach;?>
</tbody></table>
</div>
<div class="card p-4 mt-4 small text-muted">Admin peut bloquer un utilisateur → login refusé. Validation obligatoire après ouverture compte. Paiement forfait à gérer manuellement en attendant module paiement (Flouci, D17).</div>
</div></body></html>
