<?php

use Goutte\Client;

class Crawler {
    static private $instance        = null;
    
    private $city                   = "Marseille";
    private $json_item_url          = "http://www.lookr.com/_library/wct.ajaxproxy.php?rest=@@location@@&method=explore&hl=en";
    private $location_url           = "explore/43.296950;5.381070-Marseille:provence-alpes-cote-dazur,france/meteo/live,view:daylight,distance:far";
    private $slider_url             = "http://api.lookr.com/embed/timelapse/@@id@@/day";
    private $current_slider_id      = null;
    
    private $pics                   = array();
    private $client                 = null;
    
    private $current_item_samina_url= null;
    
    private $data_folder            = "/data";
    private $current_data_folder    = null;
    
    private function __construct() {
        $this->client       = new Client();
        $this->json_item_url = str_replace("@@location@@", $this->location_url, $this->json_item_url);
    }
    
    /**
     * Create singleton to perform the crawler
     * @return type
     */
    static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new Crawler();
        }
        return self::$instance;
    }
    
    /**
     * Move the client until the $this->city's slider
     */
    private function goToSlider(){
        $this->client->request("get", $this->json_item_url);
        $json       = json_decode($this->client->getResponse()->getContent());
                
        foreach($json->content->items as $item){
            $title = substr($item->title, 0, strlen($this->city));
            if( strtolower($title) == strtolower($this->city) ){
                $this->current_item_samina_url = $item->link;
            }
        }
        
        $this->client->request("get", $this->current_item_samina_url);
    }
    
    /**
     * Search the embeded slider url and define its ID
     * @throws Exception
     */
    private function defineSliderUrl(){
        preg_match('/[0-9]{1,}/', $this->current_item_samina_url, $ids);
        if(count($ids) == 0){
            throw new Exception("Id not found");
        }
        
        $this->current_slider_id    = $ids[0];
        $this->slider_url           = str_replace("@@id@@", $ids[0], $this->slider_url);
    }
    
    private function searchPics(){
        $this->client->request("get", $this->slider_url);
        $response = $this->client->getResponse()->getContent();
        
        preg_match_all('/http:\/\/[a-zA-Z0-9\/\.\-]{1,}[0-9]{1,}\@[0-9]{1,}.jpg/i', $response, $link_pics);

        if(count($link_pics) == 0){
            throw new Exception("Pics not found");
        }
        if(count($link_pics[0])==0){
            throw new Exception("Pics not found");
        }
        
        //get fullhd pics
        foreach($link_pics[0] as $link){
            $this->pics[] = str_replace ("preview", "full", $link);
        }
    }
    
    private function downloadPics(){
        foreach($this->pics as $k => $link){
            $content = file_get_contents($link);
            preg_match('/[0-9]{1,}.jpg/', $link, $ids);
            if(count($ids) == 0){
                throw new Exception("Pic Id not found");
            }
            $id_pic     = str_replace(".jpg", "", $ids[0]);
            $name_pic   = date('Y-m-d_h:i:s', $id_pic).".jpg";
            file_put_contents($this->current_data_folder."/".$name_pic, $content);
        }
    }
    
    private function createFolder(){
        $this->current_data_folder = $this->data_folder."/".$this->current_slider_id;
        
        if(!file_exists($this->current_data_folder)){
            mkdir($this->current_data_folder, 0755, true);
        }
        if(!file_exists($this->current_data_folder)){
            throw new Exception("Unable to create folder ".$this->current_data_folder);
        }
    }
    
    public function run(){
        $this->goToSlider();
        $this->defineSliderUrl();
        $this->searchPics();
        $this->createFolder();
        $this->downloadPics();
    }
    
    public function test($start_date, $end_date){
        $url = "http://archive.lookr-cdn.com/day/full/@@hour@@/02/1421725202@@@integer@@.jpg";
        
        $start_date = new \DateTime($start_date);
        $end_date   = new \DateTime($end_date);
        
        $start_date = $start_date->getTimestamp();
        $end_date   = $end_date->getTimestamp();
        
        while($start_date < $end_date){
            $url_pic = str_replace("@@hour@@", $start_date, $url);

            for($i = $start_date; $i < $start_date+3600; $i++){
                $url_pic_final = str_replace("@@integer@@", $i, $url_pic);
                
                $headers = get_headers($url_pic_final);
                
                preg_match('/[0-9]{3}/', $headers[0], $http_code);
                var_dump($url_pic_final);
                var_dump(date('Y-m-d_h:i:s', $i));
                if($http_code[0] == 200){
                    file_put_contents($this->data_folder."/link.txt", $url_pic_final."\n", FILE_APPEND);
                    $i          += 3600;
                }
            }
            $start_date += 3600;
        }
    }
}