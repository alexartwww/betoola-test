<?php
if (!defined('ROOT')) {
    define('ROOT', $_SERVER['DOCUMENT_ROOT'] ?? dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
}

define('INIT', true);

require_once(implode(DIRECTORY_SEPARATOR, [ROOT, 'vendor', 'autoload.php']));
