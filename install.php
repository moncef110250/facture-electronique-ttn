<?php
// Installation rapide
echo "Installation Facture Electronique TTN Web\n";
if(!is_dir('vendor')) { echo "Run: composer install\n"; }
if(!is_dir('uploads')) mkdir('uploads',0777,true);
if(!is_dir('exports')) mkdir('exports',0777,true);
chmod('uploads',0777);
chmod('exports',0777);
echo "Dossiers créés\n";
echo "Base MySQL (optionnelle):\n";
echo file_get_contents('database.sql');
echo "\nInstallation OK - Ouvrez index.php\n";
?>
