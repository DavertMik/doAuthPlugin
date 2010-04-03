<?php

abstract class PluginUser extends BaseUser
{
  protected
    $_groups         = null,
    $_permissions    = null,
    $_allPermissions = null;

  /**
   * Returns the string representation of the object.
   *
   * @return string
   */
  public function __toString()
  {
    return (string) $this->getUsername();
  }

  /**
   * Sets the user password.
   *
   * @param string $password
   */
  public function setPassword($password)
  {
    if (!$password && 0 == strlen($password))
    {
      return;
    }

    if (!$salt = $this->getSalt())
    {
      $salt = md5(mt_rand(100000, 999999).$this->getUsername());
      $this->setSalt($salt);
    }
    $modified = $this->getModified();
    $algorithm = sfConfig::get('app_doAuth_algorithm_callable', 'sha1');

    $algorithmAsStr = is_array($algorithm) ? $algorithm[0].'::'.$algorithm[1] : $algorithm;
    if (!is_callable($algorithm))
    {
      throw new sfException(sprintf('The algorithm callable "%s" is not callable.', $algorithmAsStr));
    }

    parent::_set('password', call_user_func_array($algorithm, array($salt.$password)));
  }


}
