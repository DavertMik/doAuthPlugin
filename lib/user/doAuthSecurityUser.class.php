<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class doAuthSecurityUser extends sfBasicSecurityUser
{
  protected $user = null;

  /**
   * Initializes the doAuthSecurityUser object.
   *
   * @param sfEventDispatcher $dispatcher The event dispatcher object
   * @param sfStorage $storage The session storage object
   * @param array $options An array of options
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);

    if (!$this->isAuthenticated())
    {
      // remove user if timeout
      $this->getAttributeHolder()->removeNamespace('doUser');
      $this->user = null;
    }
  }



  /**
   * Returns the referer uri.
   *
   * @param string $default The default uri to return
   * @return string $referer The referer
   */
  public function getReferer($default)
  {
    $referer = $this->getAttribute('referer', $default);
    $this->getAttributeHolder()->remove('referer');

    return $referer;
  }

  /**
   * Sets the referer.
   *
   * @param string $referer
   */
  public function setReferer($referer)
  {
    if (!$this->hasAttribute('referer'))
    {
      $this->setAttribute('referer', $referer);
    }
  }

  /**
   * Returns whether or not the user has the given credential.
   *
   * @param string $credential The credential name
   * @param boolean $useAnd Whether or not to use an AND condition
   * @return boolean
   */
  public function hasCredential($credential, $useAnd = true)
  {
    if (empty($credential))
    {
      return true;
    }

    if (!$this->getAccount())
    {
      return false;
    }

    if ($this->getAccount()->getIsSuperAdmin())
    {
      return true;
    }

    return parent::hasCredential($credential, $useAnd);
  }

  /**
   * Returns whether or not the user is a super admin.
   *
   * @return boolean
   */
  public function isAdmin()
  {
    return $this->getAccount() ? $this->getAccount()->getIsSuperAdmin() : false;
  }

  /**
   * Returns whether or not the user is anonymous.
   *
   * @return boolean
   */
  public function isAnonymous()
  {
    return !$this->isAuthenticated();
  }

  /**
   * Signs in the user on the application.
   *
   * @param doAuthUser $user The doAuthUser id
   * @param boolean $remember Whether or not to remember the user
   * @param Doctrine_Connection $con A Doctrine_Connection object
   */
  public function signIn($user, $remember = false, $con = null)
  {
    // signin
    $this->setAttribute('user_id', $user->getId(), 'doUser');
    $this->setAuthenticated(true);

    // save last login
    $user->setLastLogin(date('Y-m-d H:i:s'));
    $user->save($con);

    // remember?
    if ($remember)
    {
      // save to cookie
      $hash = base64_encode(serialize(array($user->getUsername(),md5(rand()),doAuthTools::rememberHash($user))));
      $context = sfContext::getInstance();      

      $expiration_age = sfConfig::get('app_doAuth_remember_key_expiration_age', 15 * 24 * 3600);
      // make key as a cookie
      $remember_cookie = sfConfig::get('app_doAuth_remember_cookie_name', 'doRemember');
      sfContext::getInstance()->getResponse()->setCookie($remember_cookie, $hash, time() + $expiration_age);
    }

    $this->dispatcher->notify(new sfEvent($this, 'user.signed_in'));
  }

  /**
   * Signs out the user.
   *
   */
  public function signOut()
  {
    $this->getAttributeHolder()->removeNamespace('doUser');
    $this->user = null;
    $this->clearCredentials();
    $this->setAuthenticated(false);
    $expiration_age = sfConfig::get('app_doAuth_remember_key_expiration_age', 15 * 24 * 3600);
    $remember_cookie = sfConfig::get('app_doAuth_remember_cookie_name', 'doRemember');
    sfContext::getInstance()->getResponse()->setCookie($remember_cookie, '', time() - $expiration_age);
  }

  /**
   * Returns the related doAuthUser.
   *
   * @return User
   */
  public function getAccount()
  {
    if (!$this->user && $id = $this->getAttribute('user_id', null, 'doUser'))
    {
      $this->user = Doctrine::getTable('User')->find($id);

      if (!$this->user)
      {
        // the user does not exist anymore in the database
        $this->signOut();

        throw new sfException('The user does not exist anymore in the database.');
      }
    }

    return $this->user;
  }

  /**
   * Returns the string representation of the object.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getAccount()->__toString();
  }

  /**
   * Returns the doAuthUser object's username.
   *
   * @return string
   */
  public function getUsername()
  {
    return $this->getAccount()->getUsername();
  }


}
