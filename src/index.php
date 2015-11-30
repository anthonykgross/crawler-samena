<?php 
require_once realpath(__DIR__.'/vendor/').'/autoload.php';
require "Crawler.php";

$seconds = 10*60;

if($argv[1] == "FALSE" || $argv[2] == "FALSE"){
    while(true){
        Crawler::getInstance()->run();
        var_dump("Waiting $seconds sec.");
        sleep($seconds);
    }
}
else{
    Crawler::getInstance()->test($argv[1],$argv[2]);
}
