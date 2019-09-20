<?php
define('SVG_UTIL_PATH', __DIR__);

include_once(SVG_UTIL_PATH . '/lib/ShiftSVG.php');
$svg = new \SVGCreator\Lib\ShiftSVG();
$src = (!empty($_REQUEST['src']) ? $_REQUEST['src'] : 'empty');
$html = $svg->get($src);

header('Content-Type: image/svg+xml');
echo($html);