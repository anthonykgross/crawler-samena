<?php 
require_once realpath(__DIR__.'/vendor/').'/autoload.php';
require "Crawler.php";


if($argv[1] == "FALSE" || $argv[2] == "FALSE"){
    Crawler::getInstance()->run();
}
else{
    Crawler::getInstance()->test($argv[1],$argv[2]);
}