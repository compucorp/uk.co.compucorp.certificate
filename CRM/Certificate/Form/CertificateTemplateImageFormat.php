<?php

use CRM_Certificate_BAO_CompuCertificateTemplateImageFormat as CompuCertificateTemplateImageFormat;

class CRM_Certificate_Form_CertificateTemplateImageFormat {

  /**
   * @var CRM_Core_Form
   */
  private $form;

  /**
   * Path to where extension templates are physically stored.
   *
   * @var string
   */
  private $templatePath;

  public function __construct(&$form) {
    $this->form = &$form;
    $this->templatePath = CRM_Certificate_ExtensionUtil::path() . '/templates';
  }

  public function buildForm() {
    $this->addElements();
    $this->addTemplates();
    $this->setDefaultValues();
  }

  public function postProcess() {
    $values = $this->form->exportValues();
    $params['template_id'] = $this->form->_id;
    $params['image_format_id'] = $values['image_format_id'];
    CompuCertificateTemplateImageFormat::upsert($params);
  }

  private function addElements() {
    $this->form->add('select', 'image_format_id', ts('Image Format'),
      [
        'null' => ts('- default -'),
      ] + CRM_Certificate_BAO_CompuCertificateImageFormat::getList(TRUE),
      FALSE
    );
  }

  private function addTemplates() {
    CRM_Core_Region::instance('page-body')->add([
      'template' => "{$this->templatePath}/CRM/Admin/Form/MessageTemplates/ImageFormats.tpl",
    ]);
  }

  private function setDefaultValues() {
    if (empty($this->form->_id)) {
      return;
    }

    $defaults = $this->form->_defaultValues;
    $templateImageFormat = CompuCertificateTemplateImageFormat::getByTemplateId($this->form->_id);
    $imageFormatId = $templateImageFormat->image_format_id ?? NULL;
    $defaults['image_format_id'] = $imageFormatId;
    $this->form->setDefaults($defaults);
  }

}
