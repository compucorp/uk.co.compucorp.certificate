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
    if (!$this->shouldHandle()) {
      return;
    }

    $formProcessor = new CRM_Certificate_Form_CertificateTemplateImageFormat($this->form);
    $formProcessor->buildForm();
  }

  /**
   * Checks if this is the right form.
   *
   * @return bool
   */
  private function shouldHandle() {
    return $this->form instanceof CRM_Admin_Form_MessageTemplates && ($this->form->_action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD));
  }

}
