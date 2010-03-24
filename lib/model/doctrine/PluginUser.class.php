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
      $salt = md5(rand(100000, 999999).$this->getUsername());
      $this->setSalt($salt);
    }
    $modified = $this->getModified();
    if ((!$algorithm = $this->getAlgorithm()) || (isset($modified['algorithm']) && $modified['algorithm'] == $this->getTable()->getDefaultValueOf('algorithm')))
    {
      $algorithm = sfConfig::get('app_doAuth_algorithm_callable', 'sha1');
    }
    $algorithmAsStr = is_array($algorithm) ? $algorithm[0].'::'.$algorithm[1] : $algorithm;
    if (!is_callable($algorithm))
    {
      throw new sfException(sprintf('The algorithm callable "%s" is not callable.', $algorithmAsStr));
    }
    $this->setAlgorithm($algorithmAsStr);

    parent::_set('password', call_user_func_array($algorithm, array($salt.$password)));
  }

  /**
   * Returns whether or not the given password is valid.
   *
   * @param string $password
   * @return boolean
   */
  public function checkPassword($password)
  {
    if ($callable = sfConfig::get('app_doAuth_check_password_callable'))
    {
      return call_user_func_array($callable, array($this->getUsername(), $password, $this));
    }
    else
    {
      return $this->basicCheckPassword($password);
    }
  }

  /**
   * Returns whether or not the given password is valid.
   *
   * @param string $password
   * @return boolean
   * @throws sfException
   */
  public function basicCheckPassword($password)
  {
    $algorithm = $this->getAlgorithm();
    if (false !== $pos = strpos($algorithm, '::'))
    {
      $algorithm = array(substr($algorithm, 0, $pos), substr($algorithm, $pos + 2));
    }
    if (!is_callable($algorithm))
    {
      throw new sfException(sprintf('The algorithm callable "%s" is not callable.', $algorithm));
    }

    return $this->getPassword() == call_user_func_array($algorithm, array($this->getSalt().$password));
  }

  /**
   * Sets the password hash.
   *
   * @param string $v
   */
  public function setPasswordHash($v)
  {
    if (!is_null($v) && !is_string($v))
    {
      $v = (string) $v;
    }

    if ($this->password !== $v)
    {
      $this->_set('password', $v);
    }
  }
}
