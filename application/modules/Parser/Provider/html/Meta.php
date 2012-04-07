<?php
class Parser_Provider_Html_Meta  implements Parser_Provider_Html_Interface {


    public function getData(DOMXpath $xpath, array $parsed) {

        if (empty($parsed['desc'])) {
            $tempEl = $xpath->query("/html/head/meta[@name='description']")->item(0);
            if (!empty($tempEl)) {
                $parsed['desc'] = $tempEl->getAttribute('content');
            }
        }

        if (empty($parsed['title'])) {
            $parsed['title'] = $xpath->query("/html/head/title")->item(0)->nodeValue;


        }

        return $parsed;

    }


}