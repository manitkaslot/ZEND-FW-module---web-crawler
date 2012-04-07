<?php
/**
 * Module bootstrap
 * @author aurimas
 *
 */
class Parser_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * Bootstrap model, load module specific configs into applications
     * @return void
     */
    protected function _bootstrap()
    {

        $_conf = new Zend_Config_Ini(APPLICATION_PATH . "/modules/" . strtolower($this->getModuleName()) . "/config/application.ini", APPLICATION_ENV);
        $this->_options = array_merge($this->_options, $_conf->toArray());

        Zend_Registry::set('parser_settings', $this->_options);

        parent::_bootstrap();
    }

    /**
     * Init custom resources
     * @return void
     */
    protected function _initRegisterModuleResources()  {

       $this->getResourceLoader()->addResourceType('provider', '/Provider', 'Provider');
    }

}