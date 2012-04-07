<?php
class Parser_Provider_Api_Vimeo extends Parser_Provider_Abstract implements Parser_Provider_Api_Interface {

  /**
    * Get data from specified url
    * @param string $url
    */
   public function getData($url) {

       preg_match('#([0-9]+)#', $url, $m);

       if (empty($m[0])) return array();

       $response = json_decode($this->httpClient->getPage('http://vimeo.com/api/v2/video/' . $m[0] . '.json'), true);

       if (!empty($response)) {
           return array (
               'image' => array($response[0]['thumbnail_large']),
               'title' => $response[0]['title'],
               'embed_url' => "http://vimeo.com/moogaloop.swf?clip_id=" . $m[0] . "&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=00adef&fullscreen=1",
               'url'       => $url,
               'desc'      => $response[0]['description']
           );

       }

   }

}

