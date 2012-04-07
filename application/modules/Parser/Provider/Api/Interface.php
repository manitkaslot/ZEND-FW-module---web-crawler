<?php
interface Parser_Provider_Api_Interface {

   /**
     * Get data from provider
     * @param string $url
     * @return mixed boolean|array
     */
   public function getData($url);

}