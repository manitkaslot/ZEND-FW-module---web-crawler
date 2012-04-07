<?php
/**
 * Common shared methods between classes
 * @author aurimas
 *
 */
abstract class Parser_Provider_Abstract {

    /**
     * Parsing settings
     * @var array
     */
    private $settings = null;

    /**
     * Http Client object
     */
    public $httpClient = null;

    /**
     * Init abstracted object
     * @param object $httpClient
     */
    public function __construct($httpClient) {
        $this->httpClient = $httpClient;
        $this->setSettings();
    }

    /**
     * Store settings from regist into object
     * @return void
     */
    public function setSettings() {
        $this->settings = Zend_Registry::get('parser_settings');
    }

    /**
     * Get specific provider settings
     * @param string $provider name of provider
     * @throws Exception
     * @return array
     */
    public function getSettings($provider) {

        $provider = strtolower($provider);

        if (!array_key_exists($provider, $this->settings['provider']['api'])) {
            throw new Exception(sprintf('Settings for %s not found', $provider));
        }

        return $this->settings['provider']['api'][$provider];
    }
}