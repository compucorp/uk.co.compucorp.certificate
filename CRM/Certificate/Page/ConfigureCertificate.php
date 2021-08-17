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
    $certificates = $this->getAllConfiguredCertificates();
    $this->assign('rows', $certificates);
  }

  private function getAllConfiguredCertificates() {
    $certificates = [];
    $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
    $certificateBAO->find();

    while ($certificateBAO->fetch()) {
      $entity = CRM_Certificate_Entity_EntityFactory::create($certificateBAO->entity);

      $options = CRM_Certificate_Enum_CertificateType::getOptions();
      $configuredTypes = $entity->getCertificateConfiguredTypes($certificateBAO->id);
      $configuredStatuses = $entity->getCertificateConfiguredStatuses($certificateBAO->id);
      $action = CRM_Core_Action::formLink($this->actionLinks(), NULL, ['id' => $certificateBAO->id]);

      $certificates[$certificateBAO->id] = [
        "name" => $certificateBAO->name,
        "type" => CRM_Utils_Array::value($certificateBAO->entity, $options, ts('unknown')),
        "linked_to" => $this->transformCertificateConfiguredTypes($configuredTypes),
        "status" => $this->transformCertificateConfiguredStatuses($configuredStatuses),
        "action" => $action,
      ];
    }

    return $certificates;
  }

  public function transformCertificateConfiguredTypes($configuredTypes) {
    if (empty($configuredTypes)) {
      return "ALL";
    }

    return implode(', ', array_column($configuredTypes, 'label'));
  }

  public function transformCertificateConfiguredStatuses($configuredStatuses) {
    if (empty($configuredStatuses)) {
      return "ALL";
    }

    return implode(', ', array_column($configuredStatuses, 'label'));
  }

  public static function &actionLinks() {
    if (empty(self::$_actionLinks)) {
      self::$_actionLinks = [
        CRM_Core_Action::UPDATE => [
          'name' => ts('Edit'),
          'url' => 'civicrm/admin/certificates/add',
          'qs' => 'action=update&id=%%id%%',
          'title' => ts('Edit Certificate configuration'),
          'class' => 'crm-popup',
        ],
        CRM_Core_action::DELETE => [
          'name' => ts('Delete'),
          'url' => 'civicrm/admin/certificates/delete',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Certificate configuration'),
          'class' => 'crm-popup',
        ],
      ];
    }

    return self::$_actionLinks;
  }

}
