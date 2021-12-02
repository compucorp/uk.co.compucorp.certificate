<?php
use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_Hook_PageRun_MemberPageTab {

  private $page = NULL;

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

    $certificateType = CRM_Certificate_Enum_CertificateType::MEMBERSHIPS;
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificate = $entity->getCertificateConfiguration($id, $contactId);

    if ($configuredCertificate) {
      $downloadUrl = $entity->getCertificateDownloadUrl($id, $contactId);

      Civi::resources()->addVars(E::SHORT_NAME, ['download_url' => $downloadUrl]);
      Civi::resources()->addScriptFile(E::LONG_NAME, "/js/compucertificate.js", 0);
      Civi::resources()->addScriptFile(E::LONG_NAME, "./js/memberDownloadButton.js", 1);
    }
  }

  private function shouldRun() {
    $pageName = $this->page->getVar('_name');
    $id = $this->page->getVar('_id');
    $action = $this->page->getVar('_action');

    if ($pageName === "CRM_Member_Page_Tab" && $action === CRM_Core_Action::VIEW && !empty($id)) {
      return TRUE;
    }

    return FALSE;
  }

}
