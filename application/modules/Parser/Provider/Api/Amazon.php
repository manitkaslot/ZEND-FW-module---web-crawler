<?php
/**
 *
 * Amazon content provider through API
 * @author aurimas
 *
 */
class Parser_Provider_Api_Amazon extends Parser_Provider_Abstract implements  Parser_Provider_Api_Interface {


   /**
    * Get data from specified url
    * @param string $url
    */
   public function getData($url) {

       preg_match('#(?:http://(?:www\.){0,1}amazon\.(com|co\.uk|ca|cn|de|es|fr|it|co\.jp)(?:/.*){0,1}(/dp/|/gp/product/))(.*?)(?:/.*|$)#i', $url, $matches); //extract amazon id

       if (empty($matches[3])) return array();

       $settings = $this->getSettings('Amazon');

       if (empty($settings['sharedSecret']) || empty($settings['accessKey'])) {
           throw new Exeption('Please provide shared secret and access key, to use Amazon API');
       }

       //Find out API url by region
       switch ($matches[1]) {
           case 'com':
             $host = 'webservices.amazon.com';
           break;
           case 'co.uk':
             $host = 'ecs.amazonaws.co.uk';
           break;
           case 'ca':
             $host = 'ecs.amazonaws.ca';
           break;
           case 'cn':
             $host = 'webservices.amazon.cn';
           break;
           case 'de':
             $host = 'ecs.amazonaws.de';
           break;
           case 'es':
             $host = 'webservices.amazon.es';
           break;
           case 'fr':
             $host = 'ecs.amazonaws.fr';
           break;
           case 'it':
             $host = 'webservices.amazon.it';
           break;
           case 'it':
             $host = 'webservices.amazon.it';
           break;
           case 'co.jp':
             $host = 'ecs.amazonaws.jp';
           break;
       }

       $method = "GET";
       $uri = "/onca/xml";


       $params['Service']        = 'AWSECommerceService';
       $params['AWSAccessKeyId'] = $settings['accessKey'];
       $params['Operation']      = 'ItemLookup';
       $params['ItemId']         = $matches[3];
       $params['ResponseGroup']  = 'Large';
       $params['Version']        = '2011-08-01';
       $params['Timestamp']      = date('c');
       $params['AssociateTag']   = 'none';

       ksort($params);

       $query = array();
       foreach ($params as $param => $value) {
             $param = str_replace("%7E", "~", rawurlencode($param)); //change encoded to ~
             $value = str_replace("%7E", "~", rawurlencode($value));
             $query[] = $param . "=" . $value;
       }

       $query = implode('&', $query);

       $toSign = $method . "\n" . $host . "\n" . $uri . "\n" . $query;

       $signature = base64_encode(hash_hmac("sha256", $toSign, $settings['sharedSecret'], True));

       $signature = str_replace("%7E", "~", rawurlencode($signature));

       $urlParse = "http://" . $host . $uri . "?" . $query . "&Signature=" . $signature;

       $page = $this->httpClient->getPage($urlParse);

       $xml = simplexml_load_string($page);

       if (!empty($xml->Error)) {
           $error  = implode("\n", (array) $xml->Error);
           return false;

       } elseif (!empty($xml->Items->Request->Errors->Error)) {
           $error  = implode("\n", (array) $xml->Items->Request->Errors->Error);
           return false;

       } else if(!empty($xml)) {

           $desc = '';
           if (!empty($xml->Items->Item->EditorialReviews->EditorialReview->Content)) {
               $desc = (string) $xml->Items->Item->EditorialReviews->EditorialReview->Content;
           }

           $price = '';
           if (!empty($xml->Items->Item->Offers->Offer->OfferListing->Price->FormattedPrice)) {
               $price = (string) $xml->Items->Item->Offers->Offer->OfferListing->Price->FormattedPrice;
           } else if(!empty($xml->Items->Item->ItemAttributes->ListPrice->FormattedPrice)) {
               $price = (string) $xml->Items->Item->ItemAttributes->ListPrice->FormattedPrice;
           }

           $imgUrl = '';
           if (!empty($xml->Items->Item->LargeImage->URL)) {
               $imgUrl = array((string) $xml->Items->Item->LargeImage->URL);
           } else if(!empty($xml->Items->Item->ImageSets->ImageSet->LargeImage->URL)) {
               $imgUrl = array((string) $xml->Items->Item->ImageSets->ImageSet->LargeImage->URL);
           }

           return array(
             'image' => $imgUrl,
             'title' => (string) $xml->Items->Item->ItemAttributes->Title,
             'price' =>  $price,
             'currency' => (string) $xml->Items->Item->ItemAttributes->ListPrice->CurrencyCode,
             'desc' => $desc,
             'url' => 'http://www.amazon.' . $matches[1] . $matches[2] . $matches[3]
           );

       } else return array();


   }

}

