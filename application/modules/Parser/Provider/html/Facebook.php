<?php
class Parser_Provider_Html_Facebook implements Parser_Provider_Html_Interface  {


    public function getData(DOMXpath $xpath, array $parsed) {

       if (empty($parsed['desc'])) {
           $tempEl = $xpath->query("/html/head/meta[@property='og:description']")->item(0);
           if (!empty($tempEl)) {
               $parsed['desc'] = $tempEl->getAttribute('content');
           }
       }

       if (empty($parsed['title'])) {
           $tempEl = $xpath->query("/html/head/meta[@property='og:title']")->item(0);
           if (!empty($tempEl)) {
               $parsed['title'] = $tempEl->getAttribute('content');
           }

       }

       if (empty($parsed['image'])) {
           $tempEl = $xpath->query("/html/head/meta[@property='og:image']")->item(0);
           if (!empty($tempEl)) {
               $parsed['image'] = $tempEl->getAttribute('content');
           }
       }

       return $parsed;

    }
}