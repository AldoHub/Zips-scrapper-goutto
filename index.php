<?php
require_once("./vendor/autoload.php");
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;


//file location
$zipcodesJSON = file_get_contents("zips.json");

//site data
$totalPages = "";
$zipcode = "";
$siteUrl = "";
$currentPage = 1;
$apartmentsData = [];

//create the client
$client = new Client(HttpClient::create(array(
    'headers' => array(
        'user-agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0', // will be forced using 'Symfony BrowserKit' in executing
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.5',
        'Referer' => 'http://yourtarget.url/',
        'Upgrade-Insecure-Requests' => '1',
        'Save-Data' => 'on',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache',
    ),
)));
//set the agent to be able to scrap
$client->setServerParameter('HTTP_USER_AGENT', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0');


//reads the json file
function readZipcodes(){
    global $zipcodesJSON;
    global $zipcode;
    global $currentPage;
    global $apartmentsData;
    global $siteUrl;
    $json = json_decode($zipcodesJSON, true);

    //loop through the zipcodes in the file
    foreach($json as $key => $v){
      foreach($v as $_v){
        $currentPage = 1;
        $zipcode = $_v;
        $siteUrl = "";
        $apartmentsData = [];
        getApartmentsData();
      }
    }


}


function fetchApartments(){
    global $siteUrl;
    global $zipcode;
    global $client;
   
    //set the to current index
    if($siteUrl === ""){
        //set the default url here
        $siteUrl = "https://www.apartments.com/houston-tx-" . $zipcode . "/"; 
    }
    echo $siteUrl . '<br/>';
    
    $crawler = $client->request('GET', $siteUrl);
    
    //return the crawler obj
    return $crawler;
    
   
}

function getApartmentsData(){
    global $siteUrl;
    global $currentPage;
    global $zipcode;
    global $apartmentsData;
   
    //returns the crawler
    $crawler = fetchApartments();
    
    //get the pagination data
    $pagination = $crawler->filter('.searchResults > .pageRange')->each(function ($node) {
        return $node->text();
    });
    //check if pagination has values
    if(sizeof($pagination) == 0){
        $totalPages = 1;
    }else{
        $totalPages = substr($pagination[0], -1);
    }

    //do the recursive call
    if($currentPage > $totalPages){
        //return the array
        print_r($apartmentsData);
        echo "<br/>";
        return $apartmentsData;
    
    }else{
      
        //add one to the current page
       $currentPage++;
      
       //rebuild the url with the current data
       $siteUrl = "https://www.apartments.com/houston-tx-".  $zipcode ."/" . $currentPage ."/";

       //get the apartments data and push it to the array
       $apartments = $crawler->filter(".placards .placardContainer ul li article header div a .property-title span")->each(function ($node) {
            global $apartmentsData;
            array_push($apartmentsData, $node->text());
       });

       //print_r($apartmentsData);
       sleep(3);
       getApartmentsData();
    }

}

readZipcodes();