<?php
class Parser_Provider_Curl {

   /**
    * Information from cURL
    * @var array
    */
   private $curlInfo = null;

   /**
    * Set cookie for cURL
    * @var unknown_type
    */
   private $setCookie = false;


   /**
    * Get page with cURL
    * @param string $url
    * return string
    */
   public function getPage($url) {

       $ch = curl_init($url);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	   $useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.63 Safari/535.7";
	   curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	   //curl_setopt($ch, CURLOPT_HEADER, true);
	   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

	   if ($this->setCookie == true) {
	       $cookieJar = tempnam ("/tmp", "CURLCOOKIE");
	       curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookieJar);
	   }

	   $page            = curl_exec($ch);
	   $this->curlInfo  = curl_getinfo($ch);
	   curl_close($ch);

	   return $page;

   }

   /**
    * Switch cookie to enabled-disable on cUrl
    * @param boolean $enable
    * @return void
    */
   public function setEnableCookie($enable = false) {
       $this->setCookie = $enable;
   }

   /**
    * Gettingin information about last request
    * @return array
    */
   public function getLastRequestInfo() {
       return $this->curlInfo;
   }

}