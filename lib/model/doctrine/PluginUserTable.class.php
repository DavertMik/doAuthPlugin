<?php

class PluginUserTable extends Doctrine_Table {
  
  public static function getInstance() {
    return Doctrine_Core::getTable('User');
  }

  public function retrieveByUsername($username, $isActive = true) {
    $query = Doctrine::getTable('User')->createQuery('u')
      ->where('u.username = ?', $username)
      ->addWhere('u.is_active = ?', $isActive)
    ;

    return $query->fetchOne();
  }

}