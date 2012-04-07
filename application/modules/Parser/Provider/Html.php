<?php
/**
 * Parses html using php DOM
 * @author aurimas
 *
 */
class Parser_Provider_Html extends Parser_Provider_Abstract {

    /**
     * Image utility object
     * @var object
     */
    private $_imageOb = null;

    /**
     * Url utility object
     * @var object
     */
    private $_urlOb = null;

    /**
     * Construct object, inject required dependencies
     * @param Parser_Provider_Image $imageOb
     * @param Parser_Provider_Url $urlOb
     * @param object
     */
    public function __construct(Parser_Provider_Image $imageOb, Parser_Provider_Url $urlOb, $httpClient) {

        $this->_imageOb = $imageOb;
        $this->_urlOb   = $urlOb;

        parent::__construct($httpClient);

    }

    /**
     * Start parsing html
     * @param string $url url to parse
     * @return array
     */
    public function parseHtml($url) {

         $parsed = array();

         $this->httpClient->setEnableCookie(true);

         $doc = new DOMDocument();
         $doc->recover             = true;
         $doc->strictErrorChecking = false;

         @$doc->loadHTML($this->httpClient->getPage($url));

         $xpath = new DOMXpath($doc);

         $pathParts = pathinfo(__FILE__);

         $adapters = new DirectoryIterator($pathParts['dirname'] . DIRECTORY_SEPARATOR . 'Html');
         foreach($adapters as $adapter) {
              $fileParts = pathinfo($adapter);

              if ($fileParts['extension'] == 'php' && !empty($fileParts['filename']) && $fileParts['filename'] != 'Interface') {
                  $className = 'Parser_Provider_Html_' . ucfirst(strtolower($fileParts['filename']));

                  $pObj   = new $className;

                  if (!$pObj instanceof Parser_Provider_Html_Interface) {
                      throw new Exception(sprintf('Html class %s does not implement Parser_Provider_Html_Interface', $className));
                  }

                  $parsed = array_merge($parsed, $pObj->getData($xpath, $parsed));

              }

         }

         $parsed['image'] = $this->findImages($xpath, $url);

         return $parsed;
    }

    /**
     * Find images on html, return sorted from bigest to smallest
     * @return array
     */
    protected function findImages(DOMXpath $xpath, $url) {

         $images   = $xpath->query('//img');
         $imgList  = array();
         $i        = 0;

         $relImgTag = $xpath->query('/html/head/link[@rel="image_src"]')->item(0);

	     if (!empty($relImgTag)) {
    	     list($idth, $height)       = $this->_imageOb->getImageSize($relImgTag->getAttribute('href'));
        	 $imgList[$i]['img']        = $relImgTag->getAttribute('href');
             $imgList[$i]['dimensions'] = $width * $height;
             $i++;
	     }


         foreach($images as $image) {

             $src = $image->getAttribute('src');

             if (!empty($src)) {

                 $imageUrl = $this->_urlOb->url_to_absolute($url, $src);

                 if (empty($imageUrl)) continue;

                 list($width, $height) = $this->_imageOb->getImageSize($imageUrl);

                 if ($width > 50 && $height > 50) {

                     $exist = false;
			         foreach($imgList as $curImg) {
			            if ($curImg['img'] == $imageUrl) {
			                $exist = true;
			                break;
			            }
			         }

                     if ($exist == false) {
    			         $imgList[$i]['img']        = $imageUrl;
    			         $imgList[$i]['dimensions'] = $width * $height;
    			         $i++;
			         }

                 }

                 if ($i == 20) break; //stop after 20 images

             }

         }

         $imgReturn = array();
         if (!empty($imgList)) {
             usort($imgList, function ($a, $b) {
                 if ($a['dimensions'] == $b['dimensions']) {
                     return 0;
                 }
                 return ($a['dimensions'] > $b['dimensions']) ? -1 : 1;
             });

             foreach ($imgList as $key => $image) {
                if ($key == 5) break;
                $imgReturn[] = $image['img'];
             }
         }

         return $imgReturn;

    }

}