<?php

/**
 * doAuthPlugin configuration.
 * 
 * @package    doAuthPlugin
 * @subpackage config
 * @author     davert
 */
class doAuthPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {

  if (sfConfig::get('app_auth_routes_register', true) && in_array('baseAuth', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array('doAuthRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
  }
  
}
