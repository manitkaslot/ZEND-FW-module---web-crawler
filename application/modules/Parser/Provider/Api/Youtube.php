<?php
class Parser_Provider_Api_Youtube extends Parser_Provider_Abstract implements  Parser_Provider_Api_Interface {


   /**
    * Get data from specified url
    * @param string $url
    */
   public function getData($url) {

       preg_match('#v=([a-zA-Z0-9\-_]+)#', $url, $m);

       if (empty($m[1])) return array();

       $response = json_decode($this->httpClient->getPage('http://gdata.youtube.com/feeds/api/videos/' . $m[1] . '?v=2&alt=json&format=5'), true);

       if (!empty($response)) {
           return array (
               'image' => array($response['entry']['media$group']['media$thumbnail'][1]['url']),
               'title' => $response['entry']['title']['$t'],
               'embed_url' => "http://www.youtube.com/v/" . $m[1] . "?fs=1",
               'url'       => $url,
               'desc'      => $response['entry']['media$group']['media$description']['$t']
           );

       }

   }

}