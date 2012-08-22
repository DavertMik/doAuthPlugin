<?php

class doAuthCreateUserTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The user name'),
      new sfCommandArgument('email', sfCommandArgument::REQUIRED, 'The email address'),
      new sfCommandArgument('password', sfCommandArgument::REQUIRED, 'The password'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'user';
    $this->name = 'create';
    $this->briefDescription = 'Creates a user';

    $this->detailedDescription = <<<EOF
The [user:create] task creates a user:

  [./symfony user:create davert davert@example.com password|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $user = new User();
    $user->setUsername($arguments['username']);
    $user->setEmail($arguments['email']);
    $user->setPassword($arguments['password']);
    $user->setIsActive(true);
    $user->save();

    $this->log('User '.$arguments['username'].' created');

    
  }
}
