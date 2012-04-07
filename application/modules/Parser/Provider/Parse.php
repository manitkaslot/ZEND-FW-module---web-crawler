<?php
class Parser_Provider_Parse {


   /**
    * Start parsing given url
    * @param string $url
    * @throws Exception
    * @return array|boolen
    */
   public function parse($url) {

      $urlParts = parse_url($url);

      if (empty($urlParts['host'])) return false;

      $settings = Zend_Registry::get('parser_settings');
      $parsed   = array();

      $curlOb = new Parser_Provider_Curl;

      //First try load from API
      foreach($settings['provider']['api'] as $provider => $value) {

          if (array_key_exists('domains', $value)) {
              $domains = explode(',', $value['domains']);

              if (in_array($urlParts['host'], $domains)) { //check if domain exist within config
                  $className   = 'Parser_Provider_Api_' . $provider;
                  $providerObj = new $className($curlOb);

                  if (!$providerObj instanceof Parser_Provider_Api_Interface) {
                      throw new Exception(sprintf('API class %s does not implement Parser_Provider_Interface', $className));
                  }

                  $parsed = $providerObj->getData($url);
              }

          }
      }

      //if no luck with API's then parse raw HTML
      if (empty($parsed)) {
          $htmlParser = new Parser_Provider_Html(new Parser_Provider_Image, new Parser_Provider_Url, $curlOb);
          $parsed     = $htmlParser->parseHtml($url);

      }

      return $parsed;

   }
}