<?php
$dom = new DOMDocument();
$dom->load("http://televize.sh.cvut.cz/xmltv/sit2.xml");

$programmes = $dom->getElementsByTagName('programme');

foreach ($programmes as $programme) {
    echo $programme->getAttribute("channel") .$programme->getAttribute("start") .$programme->getAttribute("stop").var_export($programme->getElementsByTagName("title"), true). "<br>";
}