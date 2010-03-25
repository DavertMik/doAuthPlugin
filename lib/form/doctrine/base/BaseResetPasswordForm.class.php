<?php

class BaseResetPasswordForm extends BaseForm
{
  /**
   * @see sfForm
   */
  public function setup()
  {
    $this->setWidgets(array(
      'email' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(      
      'email' => new sfValidatorAnd(array(new sfValidatorEmail(), new sfValidatorDoctrineChoice(array('model'=> 'User','column' => 'email'))))
    ));

    $this->widgetSchema->setNameFormat('reset_password[%s]');
  }
}
