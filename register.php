<?php require 'config.php';
$msg=''; $ok=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $rs=$_POST['raison_sociale']; $mf=$_POST['matricule_fiscal']; $rc=$_POST['registre_commerce']; $adr=$_POST['adresse'];
    $email=$_POST['email']; $tel=$_POST['telephone']; $login=$_POST['login']; $pass=$_POST['password']; $forfait=$_POST['forfait'];
    $hash=password_hash($pass, PASSWORD_DEFAULT);
    try{
        $stmt=db()->prepare("INSERT INTO users (raison_sociale, matricule_fiscal, registre_commerce, adresse, email, telephone, login, password_hash, forfait) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$rs,$mf,$rc,$adr,$email,$tel,$login,$hash,$forfait]);
        $ok=true; $msg='Compte créé avec succès ! En attente de validation par administrateur. Vous serez notifié par email.';
    }catch(Exception $e){ $msg='Erreur: '.$e->getMessage().' (MF, Email ou Login déjà utilisé)'; }
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ouverture Compte - El Fatoora Pro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8fafc} .card{border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);border:0}</style></head>
<body><div class="container py-5"><div class="row justify-content-center"><div class="col-md-8">
<div class="card p-4 p-md-5">
<h3 class="mb-1">🇹🇳 Ouverture Compte Client</h3><p class="text-muted">Service facturation électronique TTN - Payant</p>
<?php if($msg):?><div class="alert <?= $ok?'alert-success':'alert-danger'?>"><?=$msg?></div><?php endif;?>
<?php if(!$ok):?>
<form method="post" class="row g-3">
<div class="col-md-6"><label>Raison Sociale *</label><input class="form-control" name="raison_sociale" required></div>
<div class="col-md-6"><label>Matricule Fiscal *</label><input class="form-control" name="matricule_fiscal" placeholder="1234567V/A/M/000" required></div>
<div class="col-md-6"><label>Registre Commerce</label><input class="form-control" name="registre_commerce"></div>
<div class="col-md-6"><label>Téléphone</label><input class="form-control" name="telephone"></div>
<div class="col-12"><label>Adresse</label><input class="form-control" name="adresse"></div>
<div class="col-md-6"><label>Email *</label><input type="email" class="form-control" name="email" required></div>
<div class="col-md-6"><label>Login *</label><input class="form-control" name="login" required></div>
<div class="col-md-6"><label>Mot de passe *</label><input type="password" class="form-control" name="password" required></div>
<div class="col-md-6"><label>Forfait *</label><select class="form-select" name="forfait" required>
<option value="usage">À l'usage - 1 DT / facture</option><option value="mensuel">Mensuel - 49 DT / mois illimité</option><option value="annuel">Annuel - 490 DT / an illimité</option>
</select></div>
<div class="col-12"><button class="btn btn-primary w-100 py-2" style="background:#0f172a;border:0">Créer mon compte (validation admin)</button></div>
</form>
<?php else: ?><a href="login.php" class="btn btn-primary">Aller à la connexion</a><?php endif;?>
<div class="text-center mt-3"><a href="index.php">Retour accueil</a></div>
</div></div></div></div></body></html>
