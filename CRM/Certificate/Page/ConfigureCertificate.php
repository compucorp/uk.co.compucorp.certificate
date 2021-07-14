<?php

use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_Page_ConfigureCertificate extends CRM_Core_Page {

  private $title = 'Configure Certificate';

  /**
   * The action links that we need to display for the browse screen.
   *
   * @var array
   */
  private static $_actionLinks = NULL;

  public function run() {

    CRM_Utils_System::setTitle(E::ts($this->title));

    $this->assign('title', E::ts($this->title));
    $this->browse();
    parent::run();
  }

  public function browse() {
    $certificates = [];
    $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
    $certificateBAO->find();
    while ($certificateBAO->fetch()) {
      $entity = CRM_Certificate_Entity_EntityFactory::create($certificateBAO->entity);
      $certificates[$certificateBAO->id] = [
        "name" => $certificateBAO->name,
        "type" =>  CRM_Utils_Array::value($certificateBAO->entity, CRM_Certificate_Enum_CertificateType::getOptions(), ts('unknown')),
        "linked_to" => implode(', ', array_column($entity->getCertificateConfiguredTypes($certificateBAO->id), 'label')),
        "status" => implode(', ', array_column($entity->getCertificateConfiguredStatuses($certificateBAO->id), 'label')),
        "action" => CRM_Core_Action::formLink($this->actionLinks(), NULL, ['id' => $certificateBAO->id])
      ];
    }

    $this->assign('rows', $certificates);
  }

  public static function &actionLinks() {
    if (empty(self::$_actionLinks)) {
      self::$_actionLinks = [
        CRM_Core_Action::UPDATE => [
          'name' => ts('Edit'),
          'url' => 'civicrm/admin/certificates/add',
          'qs' => 'action=update&id=%%id%%',
          'title' => ts('Edit Certificate configuration'),
        ],
        CRM_Core_action::DELETE => [
          'name' => ts('Delete'),
          'url' => 'civicrm/admin/certificates/delete',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Certificate configuration'),
        ]
      ];
    }

    return self::$_actionLinks;
  }
}
