<?php
require 'vendor/autoload.php';
use Scraper\Scrapify;


// $searchParam = $argv[1] = "Hello";
$searchParam1 = "Exagona";
// /**
//  * @var Scrapify $scrapy
//  */
$scrapy = new Scrapify($searchParam1);
print_r($scrapy->extract());
?>