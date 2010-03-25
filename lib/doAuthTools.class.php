<?php

/**
 * additional user tools
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
    return md5($user->getId().'-'.$user->getUsername().substr($user->getPassword(),0,5));
  }

  /**
   * Returns an activation code
   *
   * @param User $user
   * @return string
   *
   */

  public static function activationCode(User $user) {
    return md5($user->getCreatedAt().time().$user->getUsername().substr($user->getUsername(),0,5));
  }

  /**
   * Returns a code for password reset
   *
   * @param User $user
   * @return string
   *
   */

  public static function passwordResetCode(User $user) {
    return md5($user->getSalt().$user->getUsername().$user->getCreatedAt());
  }


  public static function generatePassword() {
    return substr(md5(rand(1000,9999).time()),0,8);
  }
}


?>
