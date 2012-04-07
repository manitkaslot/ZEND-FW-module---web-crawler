<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initRoutes() {

        $this->bootstrap("frontController");
        $front = $this->getResource("frontController");

        $front->addModuleDirectory(APPLICATION_PATH . '/modules');
    }

}

