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

  public static function getAuthenticatedUser($username, $password, $active = true)
  {
    $user = $this->retrieveByUsername($username, $active);

    // nickname exists?
    if ($user)
    {
      if ($callable = sfConfig::get('app_doAuth_check_password_callable')) {
        $is_ok = call_user_func_array($callable, array($this->getUsername(), $password, $this));
      } else {
        $algorithm = sfConfig::get('app_doAuth_algorithm_callable', 'sha1');

        $algorithmAsStr = is_array($algorithm) ? $algorithm[0].'::'.$algorithm[1] : $algorithm;
        if (!is_callable($algorithm))
        {
          throw new sfException(sprintf('The algorithm "%s" is not callable.', $algorithmAsStr));
        }

        $is_ok = ($this->getPassword() == call_user_func_array($algorithm, array($this->getSalt().$password)));
      }
      if ($is_ok) return $user;
    }

    return null;
  }

}