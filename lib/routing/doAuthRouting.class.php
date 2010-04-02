<?php

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     davert
 */
class doAuthRouting
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   * @static
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();

    // preprend our routes
    $r->prependRoute('signin', new sfRoute('/login', array('module' => 'baseAuth', 'action' => 'signin')));
   	$r->prependRoute('signout', new sfRoute('/logout', array('module' => 'baseAuth', 'action' => 'signout')));
    $r->prependRoute('register', new sfRoute('/register', array('module' => 'baseAuth', 'action' => 'register')));
   	$r->prependRoute('password', new sfRoute('/request_password', array('module' => 'baseAuth', 'action' => 'password','wildcard'=> true)));

    if (sfConfig::get('app_doAuth_activation',false)) {
      $r->prependRoute('activation', new sfRoute('/activate/:code', array('module' => 'baseAuth', 'action' => 'activate')));
    }
    // activation

  }

}