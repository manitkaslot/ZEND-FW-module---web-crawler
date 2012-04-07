<?php
class Parser_IndexController extends Zend_Controller_Action
{

    public function indexAction() {

        $websiteURL = $this->_request->getPost('website');

        if (!empty($websiteURL)) {
            $parser = new Parser_Provider_Parse();
            $parsed = $parser->parse($websiteURL);

            $this->view->assign('parsed', $parsed);
        }

    }

}
