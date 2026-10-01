<?php
session_start();
function db() {
    static $pdo=null;
    if($pdo===null){
        $dbFile = __DIR__.'/data.db';
        $pdo = new PDO("sqlite:".$dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = file_get_contents(__DIR__.'/database.sql');
        // Split by ; for SQLite init
        $stmts = array_filter(array_map('trim', explode(';', $sql)));
        foreach($stmts as $stmt){
            if(stripos($stmt,'CREATE TABLE')!==false) $pdo->exec($stmt);
        }
        // Admin par défaut si vide
        $cnt = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if($cnt==0){
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (raison_sociale, matricule_fiscal, email, login, password_hash, role, statut, forfait) VALUES ('Administrateur TTN','ADMIN000','admin@elfatoora.tn','admin','".$hash."','admin','actif','annuel')");
        }
    }
    return $pdo;
}
function isLogged(){ return isset($_SESSION['user']); }
function isAdmin(){ return isset($_SESSION['user']) && $_SESSION['user']['role']==='admin'; }
function requireLogin(){ if(!isLogged()){ header('Location: login.php'); exit; } }
function requireAdmin(){ if(!isAdmin()){ header('Location: dashboard.php'); exit; } }
?>
