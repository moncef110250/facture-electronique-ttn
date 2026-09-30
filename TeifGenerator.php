<?php
// TEIF Generator - Lit ton fact.xlsx exactement comme lecturexls.txt
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeifGenerator {
    private $data = [];
    
    public function lireXLS($chemin) {
        $spreadsheet = IOFactory::load($chemin);
        $sheet = $spreadsheet->getActiveSheet();
        
        // --- TON CODE WINDEV traduit en PHP (lecturexls.txt) ---
        // FOURNISSEUR
        $sFournisseur = $sheet->getCell('A2')->getValue();
        $sMatfiscfour = $sheet->getCell('A3')->getValue();
        $sRcfour = $sheet->getCell('A4')->getValue();
        $sAdrfour = $sheet->getCell('A5')->getValue();
        // CLIENT
        $sClient = $sheet->getCell('A7')->getValue();
        $sMatfisccli = $sheet->getCell('A8')->getValue();
        $sAdrcli = $sheet->getCell('A9')->getValue() . " " . $sheet->getCell('A10')->getValue();
        // FACTURE
        $sNumfact = $sheet->getCell('C1')->getValue();
        $dAtefact = $sheet->getCell('D1')->getValue(); // peut être DateTime
        $sDevise = $sheet->getCell('E1')->getValue() ?: 'TND';
        
        // CORPS 12 à 26
        $tabARTICLE = []; $tabQUANTITE = []; $tabPUHT = []; $tabTotht = []; $tabTva = []; $tabTtc = [];
        $tva19 = 0; $tva7 = 0; $tva13 = 0; $tva0 = 0;
        
        for($i=13; $i<=26; $i++) { // ligne Excel 13 à 26 = tes articles
            $j = $i - 12;
            $tabARTICLE[$j] = $sheet->getCell("A$i")->getValue();
            $tabQUANTITE[$j] = $sheet->getCell("B$i")->getValue();
            $tabPUHT[$j] = $sheet->getCell("C$i")->getValue();
            $tabTotht[$j] = $sheet->getCell("D$i")->getCalculatedValue();
            $tabTva[$j] = $sheet->getCell("E$i")->getCalculatedValue();
            $tabTtc[$j] = $sheet->getCell("F$i")->getCalculatedValue();
            
            if($tabTotht[$j] > 0) {
                $xtaux = $tabTva[$j] * 100 / $tabTotht[$j];
                if(round($xtaux)==7) $tva7 += $tabTva[$j];
                if(round($xtaux)==13) $tva13 += $tabTva[$j];
                if(round($xtaux)==19) $tva19 += $tabTva[$j];
                if(round($xtaux)==0) $tva0 += $tabTva[$j];
            }
        }
        
        $timbre = $sheet->getCell('F8')->getValue() ?: 1;
        $Htfacture = $sheet->getCell('F7')->getCalculatedValue();
        $xtotaltva = $tva0 + $tva7 + $tva13 + $tva19;
        $xTtcfacture = $xtotaltva + $Htfacture + $timbre;
        
        // Format date ddMMyyyy - GESTION 46259 (numérique Excel)
        $dateObj = null;
        try {
            if($dAtefact instanceof DateTime) {
                $dateObj = $dAtefact;
            } elseif($dAtefact instanceof \DateTimeInterface) {
                $dateObj = new DateTime($dAtefact->format('Y-m-d H:i:s'));
            } elseif(is_numeric($dAtefact)) {
                // 46259 = date Excel
                $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(floatval($dAtefact));
            } else {
                $dateObj = new DateTime(trim((string)$dAtefact));
            }
        } catch(Exception $e) {
            // Fallback: aujourd'hui si date illisible
            $dateObj = new DateTime();
        }
        $sDateFormatee = $dateObj->format('dmY');
        
        $this->data = compact('sFournisseur','sMatfiscfour','sRcfour','sAdrfour','sClient','sMatfisccli','sAdrcli','sNumfact','sDateFormatee','sDevise','tabARTICLE','tabQUANTITE','tabPUHT','tabTotht','tabTva','tabTtc','tva19','tva7','tva13','tva0','timbre','Htfacture','xtotaltva','xTtcfacture','dateObj');
        return $this->data;
    }
    
    public function genererXML_TEIF_190() {
        $d = $this->data;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<TEIF version="1.9.0" controlingAgency="TTN">'."\n";
        $xml .= '  <InvoiceHeader>'."\n";
        $xml .= '    <MessageSenderIdentifier type="I-01">'.$this->esc($d['sMatfiscfour']).'</MessageSenderIdentifier>'."\n";
        $xml .= '    <MessageRecieverIdentifier type="I-01">'.$this->esc($d['sMatfisccli']).'</MessageRecieverIdentifier>'."\n";
        $xml .= '  </InvoiceHeader>'."\n";
        $xml .= '  <InvoiceBody>'."\n";
        $xml .= '    <Bgm><DocumentIdentifier>'.$this->esc($d['sNumfact']).'</DocumentIdentifier><DocumentType code="I-11">FACTURE</DocumentType></Bgm>'."\n";
        $xml .= '    <Dtm><DateText functionCode="I-31" format="ddMMyyyy">'.$d['sDateFormatee'].'</DateText></Dtm>'."\n";
        $xml .= '    <PartnerSection>'."\n";
        $xml .= '      <PartnerDetails functionCode="I-62"><Nad><PartnerIdentifier type="I-01">'.$this->esc($d['sMatfiscfour']).'</PartnerIdentifier><PartnerName nameType="Physical">'.$this->esc($d['sFournisseur']).'</PartnerName></Nad></PartnerDetails>'."\n";
        $xml .= '      <PartnerDetails functionCode="I-63"><Nad><PartnerIdentifier type="I-01">'.$this->esc($d['sMatfisccli']).'</PartnerIdentifier><PartnerName nameType="Physical">'.$this->esc($d['sClient']).'</PartnerName></Nad></PartnerDetails>'."\n";
        $xml .= '    </PartnerSection>'."\n";
        $xml .= '    <LinSection>'."\n";
        
        $ligneXML = 0;
        for($k=1; $k<=14; $k++) {
            if(empty($d['tabARTICLE'][$k]) || floatval($d['tabTotht'][$k])==0) continue;
            $ligneXML++;
            $taux = $d['tabTotht'][$k] > 0 ? round($d['tabTva'][$k]*100/$d['tabTotht'][$k]) : 0;
            $xml .= '      <Lin>'."\n";
            $xml .= '        <ItemIdentifier>'.$ligneXML.'</ItemIdentifier>'."\n";
            $xml .= '        <LinImd lang="fr"><ItemCode>ART'.str_pad($ligneXML,2,'0',STR_PAD_LEFT).'</ItemCode><ItemDescription>'.$this->esc($d['tabARTICLE'][$k]).'</ItemDescription></LinImd>'."\n";
            $xml .= '        <LinQty><Quantity measurementUnit="PCE">'.number_format($d['tabQUANTITE'][$k],3,'.','').'</Quantity></LinQty>'."\n";
            $xml .= '        <LinTax><LinTaxDetails><TaxTypeName code="I-160">TVA</TaxTypeName><TaxCategory>Rate</TaxCategory><TaxDetails><TaxRateDetailType><TaxRate>'.$taux.'</TaxRate><TaxRateBasis>'.number_format($d['tabTotht'][$k],3,'.','').'</TaxRateBasis></TaxRateDetailType></TaxDetails></LinTaxDetails></LinTax>'."\n";
            $xml .= '        <LinMoa><MoaDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-180"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['tabTotht'][$k],3,'.','').'</Amount></Moa></MoaDetails><MoaDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-176"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['tabTva'][$k],3,'.','').'</Amount></Moa></MoaDetails></LinMoa>'."\n";
            $xml .= '      </Lin>'."\n";
        }
        $xml .= '    </LinSection>'."\n";
        $xml .= '    <InvoiceMoa>'."\n";
        $xml .= '      <AmountDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-180"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['Htfacture'],3,'.','').'</Amount></Moa></AmountDetails>'."\n";
        $xml .= '      <AmountDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-176"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['xtotaltva'],3,'.','').'</Amount></Moa></AmountDetails>'."\n";
        $xml .= '      <AmountDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-181"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['xTtcfacture'],3,'.','').'</Amount></Moa></AmountDetails>'."\n";
        $xml .= '    </InvoiceMoa>'."\n";
        
        if($d['tva19']>0) {
            $xml .= '    <InvoiceTax><InvoiceTaxDetails><Tax><TaxTypeName code="I-160">TVA 19%</TaxTypeName><TaxCategory>Rate</TaxCategory><TaxDetails><TaxRateDetailType><TaxRate>19</TaxRate><TaxRateBasis>'.number_format($d['Htfacture'],3,'.','').'</TaxRateBasis></TaxRateDetailType></TaxDetails></Tax><AmountDetailsSection><MoaDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-176"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['tva19'],3,'.','').'</Amount></Moa></MoaDetails></AmountDetailsSection></InvoiceTaxDetails></InvoiceTax>'."\n";
        }
        
        if($d['timbre']>0) {
            $xml .= '    <InvoiceAlc><AlcDetails><Alc><AlcTypeName code="I-190">Timbre fiscal</AlcTypeName></Alc><AmountDetailsSection><MoaDetails><Moa currencyCodeList="ISO_4217" amountTypeCode="I-184"><Amount currencyIdentifier="'.$d['sDevise'].'">'.number_format($d['timbre'],3,'.','').'</Amount></Moa></MoaDetails></AmountDetailsSection></AlcDetails></InvoiceAlc>'."\n";
        }
        
        $xml .= '  </InvoiceBody>'."\n";
        $xml .= '</TEIF>';
        
        $chemin = __DIR__.'/exports/TEIF_'.$d['sNumfact'].'_v1.9.0.xml';
        @mkdir(__DIR__.'/exports', 0777, true);
        file_put_contents($chemin, $xml);
        return ['xml'=>$xml, 'path'=>$chemin, 'data'=>$d];
    }
    
    private function esc($s){ return htmlspecialchars($s ?? '', ENT_XML1 | ENT_COMPAT, 'UTF-8'); }
}
