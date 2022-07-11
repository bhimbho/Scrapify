<?php
require 'vendor/autoload.php';
use Scraper\Scrapify;


if (isset($argv[1])) {
    $searchParam = $argv[1];
    /**
     * @var Scrapify $scrapy
     */
    //  To run on command line use "php index.php {search Parameter}"
    $scrapy = new Scrapify($searchParam1);
    print_r($scrapy->extract());
} else {
    echo 'Kindly pass a seach value';
}
?>