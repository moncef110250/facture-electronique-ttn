<?php
// envoi_ttn.php - Envoi à TTN et récupération QR + Ref Unique
session_start();
require 'vendor/autoload.php';

$xml_path = $_POST['xml_path'] ?? '';
$mode = $_POST['mode'] ?? 'simulation';
$token = $_POST['token'] ?? '';

if(!file_exists($xml_path)) die("XML introuvable");

$xmlContent = file_get_contents($xml_path);
$sNumfact = basename($xml_path);

if($mode === 'simulation') {
    // SIMULATION : On simule une réponse TTN (pour test sans contrat TTN)
    $refTTN = '0736-2026-'.date('Ymd').'-'.rand(100000,999999);
    $cev = 'CEV-2D-Doc-ANCE-SIMULATION-'.bin2hex(random_bytes(16));
    
    // Génération QR Code simulé (avec données TTN)
    // En prod ce QR vient de TTN (image PNG base64)
    require_once __DIR__.'/vendor/autoload.php';
    
    // Utilise endroid/qr-code pour générer QR
    // Contenu du QR selon spec TTN:
    $qrData = "TTN:$refTTN|MF_FRS:1234567V/A/M/000|MF_CLI:7654321B/A/M/000|FACT:$sNumfact|TTC:2381.000|URL:https://el-fatoora.tn/verify?ref=$refTTN|CEV:$cev";
    
    // Création image QR simple avec GD (sans librairie)
    $qrPath = __DIR__.'/exports/QR_'.basename($xml_path, '.xml').'.png';
    @mkdir(__DIR__.'/exports', 0777, true);
    
    // Si librairie qr-code installée, sinon placeholder
    if(class_exists('\Endroid\QrCode\QrCode')) {
        $qrCode = new \Endroid\QrCode\QrCode($qrData);
        $qrCode->setSize(300);
        $qrCode->writeFile($qrPath);
    } else {
        // Fallback GD - carré noir avec texte
        $img = imagecreatetruecolor(300,300);
        $white = imagecolorallocate($img,255,255,255);
        $black = imagecolorallocate($img,0,0,0);
        imagefill($img,0,0,$white);
        imagerectangle($img,10,10,290,290,$black);
        imagestring($img,5,40,130,"QR TTN SIMU",$black);
        imagestring($img,3,20,150,substr($refTTN,0,25),$black);
        imagepng($img,$qrPath);
        imagedestroy($img);
    }
    
    // Sauvegarde XML final signé par TTN (simulation)
    $xmlFinal = str_replace('</TEIF>', "  <TTNInfo><ReferenceTTN>$refTTN</ReferenceTTN><CEV>$cev</CEV><QRCode>BASE64_".base64_encode(file_get_contents($qrPath))."</QRCode></TTNInfo>\n</TEIF>", $xmlContent);
    $xmlFinalPath = __DIR__.'/exports/FINAL_'.basename($xml_path);
    file_put_contents($xmlFinalPath, $xmlFinal);
    
    $_SESSION['qr'] = 'exports/'.basename($qrPath);
    $_SESSION['ref_ttn'] = $refTTN;
    $_SESSION['cev'] = $cev;
    
    header('Location: index.php?ttn_ok=1&ref='.$refTTN);
    exit;
}

// Mode API REST réel TTN
if($mode === 'api') {
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->post('https://ws-ttn.tn/el-fatoora/api/v1/invoices', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/xml',
                'X-Version' => '1.9.0'
            ],
            'body' => $xmlContent,
            'verify' => false
        ]);
        $json = json_decode($response->getBody(), true);
        $refTTN = $json['referenceTTN'];
        $qrBase64 = $json['qrCodeBase64'];
        $qrPath = __DIR__.'/exports/QR_'.basename($xml_path, '.xml').'.png';
        file_put_contents($qrPath, base64_decode($qrBase64));
        $_SESSION['qr'] = 'exports/'.basename($qrPath);
        $_SESSION['ref_ttn'] = $refTTN;
        header('Location: index.php?ttn_ok=1&ref='.$refTTN);
        exit;
    } catch(Exception $e) {
        die("Erreur API TTN: ".$e->getMessage());
    }
}

// Mode SFTP réel TTN
if($mode === 'sftp') {
    // Utilise phpseclib
    // $sftp = new \phpseclib3\Net\SFTP('sftp.ttn.tn');
    // $sftp->login($token, $password);
    // $sftp->put('/in/'.basename($xml_path), $xmlContent);
    die("Mode SFTP à configurer avec tes identifiants TTN - code dans WINDEV24_Envoi_TTN_Recuperation_QR.wl");
}
