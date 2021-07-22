<?php

use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_Hook_PageRun_CaseDownloadButtonHook {

  private $page = null;

  public function __construct(&$page) {
    $this->page = $page;
  }

  public function run() {
    if ($this->shouldRun()) {
      $this->addDownloadButton();
    }
  }

  public function addDownloadButton() {
    $id = $this->page->getVar('_id');
    $contactId = $this->page->getVar('_contactId');
    $entity = CRM_Certificate_Entity_EntityFactory::create(CRM_Certificate_Enum_CertificateType::CASES);
    $certificateConfig = $entity->getCertificateConfiguration($id);

    if ($certificateConfig) {
      $query = [
        "id" => $id,
        "cid" => $contactId,
        "reset" => 1,
      ];

      $download_url = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificate/case', $query));

      CRM_Core_Resources::singleton()
        ->addScriptFile("uk.co.compucorp.certificate", "./js/certificateDownloadButton.js")
        ->addStyleFile("uk.co.compucorp.certificate", "./css/style.css");

      Civi::resources()->addVars(E::SHORT_NAME, ['download_url' => $download_url])
        ->addVars(E::SHORT_NAME, ['type' => 'cases']);
    }
  }

  public function shouldRun() {
    $pageName = $this->page->getVar('_name');
    $id = $this->page->getVar('_id');
    $action = $this->page->getVar('_action');

    if ($pageName == "CRM_Case_Page_Tab" && $action == 4 && !empty($id)) {
      return true;
    }
    return false;
  }
}
