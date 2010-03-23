<?php

/**
 * User table.
 *
 * @package    doAuthPlugin
 * @subpackage model
 * @author     davert <davert@ukr.net>
  */
abstract class PluginUserTable extends Doctrine_Table
{
  /**
   * Retrieves a doAuthUser object by username and is_active flag.
   *
   * @param  string  $username The username
   * @param  boolean $isActive The user's status
   *
   * @return doAuthUser
   */
  public function retrieveByUsername($username, $isActive = true)
  {
    $query = Doctrine::getTable('User')->createQuery('u')
      ->where('u.username = ?', $username)
      ->addWhere('u.is_active = ?', $isActive)
    ;

    return $query->fetchOne();
  }
}
