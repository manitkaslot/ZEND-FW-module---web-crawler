<?php
class Parser_Provider_Api_Ebay extends Parser_Provider_Abstract implements  Parser_Provider_Api_Interface {


     /**
      * Get data from specified url
      * @param string $url
     */
    public function getData($url) {

       preg_match('#(?:http://(?:www\.){0,1}(?:ebay|cgi.ebay)\.(com|co\.uk|ca|cn|de|es|fr|it|co\.jp)(?:/.*){0,1}(/itm/|/ctg/|/ebaymotors/))([^/]+)/([0-9]+)(.*?)(?:/.*|$)#i', $url, $matches); //extract amazon id

       if (empty($matches[4])) return array();

       $settings = $this->getSettings('Ebay');

       if (empty($settings['AppID'])) {
           throw new Exeption('Please provide APP ID, to use Ebay API');
       }

       $params['callname']         = 'GetSingleItem';
       $params['responseencoding'] = 'XML';
       $params['appid']            = $settings['AppID'];
       $params['siteid']           = 0;
       $params['ItemID']           = $matches[4];
       $params['version']          = 745;
       $params['IncludeSelector']  = 'TextDescription,ItemSpecifics,Details';

       $query ='';
       foreach($params as $key => $value) {
           $query .= '&' . $key . '=' . $value;
       }
       $apiURL = 'http://open.api.ebay.com/shopping?' . $query;

       $page = $this->httpClient->getPage($apiURL);

       $xml = simplexml_load_string($page);

       if (!empty($xml->Errors)) {
           $error  = implode("\n", (array) $xml->Errors);
           return array();

       } else if(!empty($xml)) {

           $price        = '';
           $currency     = '';
           $description  = (string) $xml->Item->Description;

           if ((string) $xml->Item->ListingType == 'FixedPriceItem') {
               if (!empty($xml->Item->BuyItNowPrice)) {
                   $price = (string) $xml->Item->BuyItNowPrice;
               } else {
                   $price = (string) $xml->Item->CurrentPrice;
               }

               $currency = (string) $xml->Item->CurrentPrice['currencyID'];

           } else {
               if (!empty($xml->Item->EndTime)) {
                   $description .= "\n\n  This auction ends at: " . date('Y-m-d H:i O', strtotime((string) $xml->Item->EndTime));
               }
           }

           return array(
               'image' => array((string) $xml->Item->PictureURL),
               'title' => (string) $xml->Item->Title,
               'price' => $price,
               'currency' => $currency,
               'desc' => $description,
               'url' => (string) $xml->Item->ViewItemURLForNaturalSearch
          );

       } else return array();

    }

}

