<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../autoload.php';
$hash = 'DEIN_GEHEIMER_HASH';

if (
    !isset($_POST['hash']) ||
    !hash_equals($hash, $_POST['hash'])
) {
    http_response_code(403);
    exit('Zugriff verweigert');
}

$sql = [];
$sql[] = 'TRUNCATE offers;';
$sql[] = 'TRUNCATE offer_competencies;';
$sql[] = 'TRUNCATE crawl_list;';

foreach($sql as $s){
    DB::DB()->query($s);
}
echo 'Die Tabellen offers, offer_competencies und crawl_list wurden truncated.';