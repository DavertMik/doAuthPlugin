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

}


?>
