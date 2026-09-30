<?php
// Configuration TTN El Fatoora - Tunisie TEIF 1.9.0
define('TTN_SFTP_HOST', 'sftp.ttn.tn');
define('TTN_SFTP_PORT', 22);
define('TTN_SFTP_LOGIN', getenv('TTN_LOGIN') ?: 'VOTRE_LOGIN_TTN');
define('TTN_SFTP_PASS', getenv('TTN_PASS') ?: 'VOTRE_MDP_TTN');
define('TTN_API_URL', 'https://ws-ttn.tn/el-fatoora/api/v1/invoices');
define('TTN_API_TOKEN', getenv('TTN_TOKEN') ?: 'VOTRE_JWT_TOKEN');
define('TTN_MODE', getenv('TTN_MODE') ?: 'simulation'); // simulation | sftp | api

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'facture_electronique');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8000');
?>
