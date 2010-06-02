<?php

/**
 * This library is very important to secure your users and their passwords.
 * Please, be sure, that you don't show Email, Password, Salt fields to public
 * If you need you can update this algorithms by copying this class to your lib folder
 */

class doAuthTools {

  /**
   * Returns a hash to save in cookie
   *
   * @param User $user
   * @return string
   *
   */
  

  public static function rememberHash(User $user) {
    return sha1($user->getId().sfConfig::get('sf_csrf_secret','').$user->getEmail().substr($user->getSalt().$user->getPassword(),20,30));
  }

  /**
   * Returns an activation code
   *
   * @param User $user
   * @return string
   *
   */

  public static function activationCode(User $user) {
    return sha1(mt_rand(10000,99999).sfConfig::get('sf_csrf_secret','').$user->getEmail());
  }

  /**
   * Returns a code for password reset
   *
   * @param User $user
   * @return string
   *
   */

  public static function passwordResetCode(User $user) {
    return sha1(substr($user->getSalt(),10,10).sfConfig::get('sf_csrf_secret','').$user->getEmail().$user->getPassword());
  }

  /**
   * Returns a new password on user request
   *
   * @return string
   *
   */


  public static function generatePassword() {
    $pool   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $string = '';
    for ($i = 1; $i <= 10; $i++)
    {
      mt_srand();
      $string .= $pool{mt_rand(0, 61)};
    }

    return $string;
  }
}


?>
