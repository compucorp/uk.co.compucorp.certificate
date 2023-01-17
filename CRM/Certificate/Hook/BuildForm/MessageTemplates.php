<?php

class CRM_Certificate_Hook_BuildForm_MessageTemplates {

  /**
   * The form submitted data.
   *
   * @var CRM_Core_Form
   */
  private $form;

  /**
   * @param \CRM_Core_Form $form
   */
  public function __construct(CRM_Core_Form &$form) {
    $this->form = $form;
  }

  public function run() {
    $formProcessor = new CRM_Certificate_Form_CertificateTemplateImageFormat($this->form);
    $formProcessor->buildForm();
  }

  /**
   * Checks if this is the right form.
   *
   * @param \CRM_Core_Form $form
   *
   * @return bool
   */
  public static function shouldRun($form) {
    return $form instanceof CRM_Admin_Form_MessageTemplates && ($form->_action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD));
  }

}
