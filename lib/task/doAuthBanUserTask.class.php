<?php

class doAuthBanUserTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The user name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'user';
    $this->name = 'ban';
    $this->briefDescription = 'Deactivates user';

    $this->detailedDescription = <<<EOF
The [user:ban] task disables a user:

  [./symfony user:ban davert|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $user = Doctrine::getTable('User')->retrieveByUsername($arguments['username']);

    if (!$user)
    {
      throw new sfException(sprintf('User identified by "%s" username does not exist or is not active.', $arguments['username']));
    }

    $user->setIsActive(false);
    $user->save();

    $this->log('User '. $arguments['username'].' is banned');

    
  }
}
