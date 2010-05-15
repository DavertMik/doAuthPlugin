<?php

class BaseRegisterUserForm extends PluginUserForm {

  public function configure()
  {
    $this->setWidget('password',  new sfWidgetFormInputPassword());
    $this->setWidget('repeat_password',  new sfWidgetFormInputPassword());

    $this->setValidators(array(
      'id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'username' => new sfValidatorString(array('required'=> true)),
      'email' => new sfValidatorEmail(array('required'=> true)),
      'password' => new sfValidatorString(array('required'=> true)),
      'repeat_password' => new sfValidatorString(array('required'=> true)),
    ));

    unset($this['id'],$this['is_active'],$this['is_super_admin'],$this['last_login'],$this['created_at'],$this['updated_at']);

    $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
      new sfValidatorSchemaCompare('password', '==', 'repeat_password'),
      new sfValidatorDoctrineUnique(array('model'=> 'User','column'=> 'email')),
      new sfValidatorDoctrineUnique(array('model'=> 'User','column'=> 'username')),
    )));
  }
}

?>