<?php

class BaseRegisterUserForm extends PluginUserForm {

  public function configure()
  {
    parent::configure();

    $this->setWidget('password',  new sfWidgetFormInputPassword());
    $this->setWidget('repeat_password',  new sfWidgetFormInputPassword());

    $this->setValidators(array(
      'id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'username' => new sfValidatorString(array('required'=> true)),
      'email' => new sfValidatorEmail(array('required'=> true)),
      'password' => new sfValidatorString(array('required'=> true)),
      'repeat_password' => new sfValidatorString(array('required'=> true)),
    ));

    $this->useFields(array('username','password','repeat_password','email'));

    $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
      new sfValidatorSchemaCompare('password', '==', 'repeat_password'),
      new sfValidatorDoctrineUnique(array('model'=> 'User','column'=> 'email')),
      new sfValidatorDoctrineUnique(array('model'=> 'User','column'=> 'username')),
    )));
  }
}

?>