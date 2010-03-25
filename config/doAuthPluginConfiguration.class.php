<?php

/**
 * doAuthPlugin configuration.
 *
 * @package    doAuthPlugin
 * @subpackage config
 * @author     davert
 */
class doAuthPluginConfiguration extends sfPluginConfiguration {
  /**
   * @see sfPluginConfiguration
   */
  public function initialize() {

    if (sfConfig::get('app_doAuth_routes_register', true) && in_array('baseAuth', sfConfig::get('sf_enabled_modules', array()))) {
      $this->dispatcher->connect('routing.load_configuration', array('doAuthRouting', 'listenToRoutingLoadConfigurationEvent'));
    }

    if (sfConfig::get('app_doAuth_email_registration', true) && !sfConfig::get('app_doAuth_activation',false)) {
      $this->dispatcher->connect('user.registered', array('doAuthMailer', 'sendRegistration'));
    }

    if (sfConfig::get('app_doAuth_activation',false) && sfConfig::get('app_doAuth_email_activation',true)) {
        $this->dispatcher->connect('user.registered', array('doAuthMailer', 'sendActivation'));
        
        if (sfConfig::get('app_doAuth_email_registration', true)) {
          $this->dispatcher->connect('user.activated', array('doAuthMailer', 'sendRegistration'));
        }
    }
  }
}
