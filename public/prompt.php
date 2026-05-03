<?php 
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../autoload.php';

$a = new Prompt();
echo '<div style="width:100%; word-break: break-all;">';
echo nl2br(htmlentities( $a->get(1)));
echo '</div>';