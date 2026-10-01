<?php
function genererFacturePDF($d, $xmlPath){
    require_once 'vendor/autoload.php';
    $pdfPath = __DIR__.'/exports/Facture_'.$d['sNumfact'].'.pdf';
    @mkdir(__DIR__.'/exports',0777,true);
    
    // Utilise TCPDF si dispo sinon simple HTML to PDF fallback
    if(class_exists('TCPDF')){
        $pdf = new TCPDF('P','mm','A4',true,'UTF-8',false);
        $pdf->SetCreator('El Fatoora Pro');
        $pdf->SetTitle('Facture '.$d['sNumfact']);
        $pdf->AddPage();
        $html = '
        <h1 style="text-align:center">FACTURE ELECTRONIQUE - TTN</h1>
        <p style="text-align:center">TEIF 1.9.0 - Facture '.$d['sNumfact'].' du '.$d['dateObj']->format('d/m/Y').'</p>
        <table border="1" cellpadding="4"><tr><td><b>'.$d['sFournisseur'].'</b><br>MF: '.$d['sMatfiscfour'].'<br>RC: '.$d['sRcfour'].'<br>'.$d['sAdrfour'].'</td>
        <td><b>Client: '.$d['sClient'].'</b><br>MF: '.$d['sMatfisccli'].'<br>'.$d['sAdrcli'].'<br>Facture: <b>'.$d['sNumfact'].'</b><br>Date: '.$d['dateObj']->format('d/m/Y').' | Devise: '.$d['sDevise'].'</td></tr></table><br>
        <table border="1" cellpadding="4"><tr style="background-color:#0f172a;color:white"><th>Designation</th><th>Qte</th><th>PU HT</th><th>Total HT</th><th>TVA</th><th>TTC</th></tr>';
        for($k=1;$k<=14;$k++){
            if(empty($d['tabARTICLE'][$k]) || floatval($d['tabTotht'][$k]??0)==0) continue;
            $html.='<tr><td>'.$d['tabARTICLE'][$k].'</td><td>'.$d['tabQUANTITE'][$k].'</td><td>'.number_format(floatval($d['tabPUHT'][$k]??0),3).'</td><td>'.number_format(floatval($d['tabTotht'][$k]??0),3).'</td><td>'.number_format(floatval($d['tabTva'][$k]??0),3).'</td><td>'.number_format(floatval($d['tabTtc'][$k]??0),3).'</td></tr>';
        }
        $html.='</table><br><table border="1" cellpadding="4" align="right"><tr><td><b>Total HT</b></td><td>'.number_format(floatval($d['Htfacture']??0),3).' '.$d['sDevise'].'</td></tr>
        <tr><td><b>Total TVA</b></td><td>'.number_format(floatval($d['xtotaltva']??0),3).'</td></tr>
        <tr><td><b>Timbre fiscal</b></td><td>'.number_format(floatval($d['timbre']??0),3).'</td></tr>
        <tr style="background-color:#f59e0b"><td><b>Total TTC</b></td><td><b>'.number_format(floatval($d['xTtcfacture']??0),3).' '.$d['sDevise'].'</b></td></tr></table><br><br><br><br>
        <p><b>XML TEIF 1.9.0:</b> '.basename($xmlPath).' - Conforme TTN El Fatoora - QR Code 2.5x2.5cm apres envoi TTN (Ref Unique + CEV ANCE)</p>';
        $pdf->writeHTML($html,true,false,true,false,'');
        $pdf->Output($pdfPath,'F');
    } else {
        // Fallback simple file
        file_put_contents($pdfPath, "Facture ".$d['sNumfact']." - HT ".$d['Htfacture']." TTC ".$d['xTtcfacture']);
    }
    return $pdfPath;
}
?>
