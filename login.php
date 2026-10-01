<?php require 'config.php';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = db()->prepare("SELECT * FROM users WHERE login=? OR email=?");
    $stmt->execute([$login,$login]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if($u && password_verify($pass, $u['password_hash'])){
        if($u['statut']==='bloque'){ $msg='Compte bloqué par administrateur.'; }
        elseif($u['statut']==='en_attente' && $u['role']!=='admin'){ $msg='Compte en attente de validation admin.'; }
        else { 
            $_SESSION['user']=$u; 
            // FIX: Admin reste sur son espace admin, pas dashboard client
            if($u['role']==='admin'){ header('Location: admin.php'); exit; }
            else { header('Location: dashboard.php'); exit; }
        }
    } else { $msg='Login ou mot de passe incorrect.'; }
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion - El Fatoora Pro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8fafc;font-family:Inter,system-ui} .card{border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);border:0}</style></head>
<body class="d-flex align-items-center" style="min-height:100vh">
<div class="container"><div class="row justify-content-center"><div class="col-md-5">
<div class="card p-4 p-md-5">
<div class="text-center mb-4"><h3>🇹🇳 El Fatoora Pro</h3><p class="text-muted">Connexion sécurisée TTN TEIF 1.9.0</p></div>
<?php if($msg):?><div class="alert alert-warning"><?=$msg?></div><?php endif;?>
<form method="post">
<input class="form-control mb-3" name="login" placeholder="Login ou Email" required>
<input type="password" class="form-control mb-3" name="password" placeholder="Mot de passe" required>
<button class="btn btn-primary w-100 py-2" style="background:#0f172a;border:0">Se connecter</button>
</form>
<div class="text-center mt-3"><a href="register.php">Créer un compte client</a> | <a href="index.php">Retour accueil</a></div>
<div class="mt-4 p-3 bg-light rounded small">Admin test: <b>admin / admin123</b> → redirige vers Admin<br>Client → redirige vers Dashboard (même design SaaS)</div>
</div></div></div></div></body></html>
