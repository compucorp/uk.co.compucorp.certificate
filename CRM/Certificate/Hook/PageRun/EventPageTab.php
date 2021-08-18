<?php
use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_Hook_PageRun_EventPageTab {

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

    $certificateType = CRM_Certificate_Enum_CertificateType::EVENTS;
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificate = $entity->getCertificateConfiguration($id, $contactId);

    if ($configuredCertificate) {
      $query = [
        "contact_id" => $contactId,
        "participant_id" => $id,
      ];

      $downloadUrl = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificates/event', $query));
      CRM_Core_Resources::singleton()
        ->addScriptFile("uk.co.compucorp.certificate", "./js/eventDownloadButton.js");
      Civi::resources()->addVars(E::SHORT_NAME, ['download_url' => $downloadUrl]);
    }
  }

  private function shouldRun() {
    $pageName = $this->page->getVar('_name');
    $id = $this->page->getVar('_id');
    $action = $this->page->getVar('_action');

    if ($pageName === "CRM_Event_Page_Tab" && $action === CRM_Core_Action::VIEW && !empty($id)) {
      return TRUE;
    }

    return FALSE;
  }

}
