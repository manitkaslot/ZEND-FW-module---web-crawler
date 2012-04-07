<?php
interface Parser_Provider_Html_Interface {

    public function getData(DOMXpath $xpath, array $parsed);
}