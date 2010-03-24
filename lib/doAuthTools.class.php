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

}


?>
