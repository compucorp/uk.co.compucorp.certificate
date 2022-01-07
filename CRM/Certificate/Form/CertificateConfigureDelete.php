<?php

use CRM_Certificate_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Certificate_Form_CertificateConfigureDelete extends CRM_Core_Form {

  /**
   * certificate configuration to delete
   * @var int
   */
  public $id;

  public function preProcess() {
    CRM_Utils_System::setTitle('Delete certificate configuration');

    $this->id = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    $url = CRM_Utils_System::url('civicrm/admin/certificates', 'reset=1');
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($url);
  }

  public function buildQuickForm() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Delete'),
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
        'isDefault' => TRUE,
      ],
    ]);

    parent::buildQuickForm();
  }

  public function postProcess() {
    if (!empty($this->id)) {
      CRM_Certificate_BAO_CompuCertificate::deleteById($this->id);
      CRM_Core_Session::setStatus(E::ts('Certificate configuration deleted successfully.'), ts('Item deleted'), 'success');
    }
  }

}
