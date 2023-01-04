<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

class CRM_Certificate_Form_CertificateImageFormats extends CRM_Admin_Form {

  /**
   * Page title.
   * @var int
   */
  protected $_title = "Certificate (Image) Formats";

  /**
   * Certificate Image Format ID.
   * @var int
   */
  public $_id = NULL;

  /**
   * @var bool
   */
  public $submitOnce = TRUE;

  public function preProcess() {
    parent::preProcess();

    $actionPrefix = [CRM_Core_Action::UPDATE => 'Update', CRM_Core_Action::DELETE => 'Delete'];
    $titlePrefix = CRM_Utils_Array::value($this->_action, $actionPrefix, 'Add');

    $this->setTitle($titlePrefix . ' Image Format');
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    if ($this->_action & CRM_Core_Action::DELETE) {
      $formatName = CompuCertificateImageFormat::getFieldValue(CompuCertificateImageFormat::class, $this->_id, 'name');
      $this->assign('formatName', $formatName);
      return;
    }

    $this->add('text', 'name', ts('Name'), [], TRUE);
    $this->add('text', 'description', ts('Description'));
    $this->add('text', 'width', ts('Width (px)'), ['min' => 1, 'step' => '0.01'], TRUE);
    $this->add('text', 'height', ts('Height (px)'), ['min' => 1, 'step' => '0.01'], TRUE);
    $this->add('text', 'quality', ts('Quality'), ['min' => 1, 'max' => 10], TRUE);
    $this->add('select', 'extension', ts('Extension'), CompuCertificateImageFormat::getSupportedExtensions(), FALSE);
    $this->add('checkbox', 'is_default', ts('Is this Image Format the default?'));
    $this->assign('elementNames', $this->getRenderableElementNames());
  }

  /**
   * @return array
   */
  public function setDefaultValues() {
    return $this->_values;
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    if ($this->_action & CRM_Core_Action::DELETE) {
      CompuCertificateImageFormat::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected Image Format has been deleted.'), ts('Record Deleted'), 'success');
      return;
    }

    $values = $this->controller->exportValues($this->getName());
    $values['is_default'] = isset($values['is_default']);
    $bao = new CompuCertificateImageFormat();
    $bao->saveImageFormat($values, $this->_id);

    $status = ts('Image Format titled <strong>%1</strong> has been saved.', [1 => $values['name']], ts('Saved'), 'success');
    if ($this->_action & CRM_Core_Action::UPDATE) {
      $status = ts('Image Format titled <strong>%1</strong> has been updated.', [1 => $values['name']], ts('Saved'), 'success');
    }
    CRM_Core_Session::setStatus($status, 'Image format updated', 'success');
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
