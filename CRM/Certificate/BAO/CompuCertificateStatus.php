<?php

class CRM_Certificate_BAO_CompuCertificateStatus extends CRM_Certificate_DAO_CompuCertificateStatus {

  /**
   * Create a new CompuCertificateStatus based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateStatus|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateStatus';
    $entityName = 'CompuCertificateStatus';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates mulitple CertificateEntityStatus and attach them to
   * a certificate
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   *    certificate instance to attach entity statuses to
   * @param array $statuses
   *    array of status ids
   *
   * @return Array
   */
  public static function assignCertificateEntityStatuses($certificate, $statuses) {
    $result = [];
    $certificateId = $certificate->id;

    if (!is_array($statuses)) {
      throw new CRM_Core_Exception('Statuses parameter must be an array');
    }

    self::validateStatusIds($certificate, $statuses);

    self::removeCertificateCurrentEntityStatus($certificateId);

    foreach ($statuses as $statusId) {
      $statusDAO = self::create([
        'certificate_id' => $certificateId,
        'status_id' => $statusId,
      ]);

      $result[] = $statusDAO->toArray();
    }

    return $result;
  }

  /**
   * Ensure only allowed status ids are allowed per the configured entity
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   *    certificate instance to attach entity statuses to
   * @param array $entityStatusIds
   *    array of entityStatus ids
   *
   * @return bool
   *
   * @throws CRM_Core_Exception
   */
  private static function validateStatusIds($certificate, $entityStatusIds) {
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificate->entity);
    $statuses = $entity->getStatuses();
    $invalidStatuses = array_diff($entityStatusIds, $statuses);

    if (!empty($invalidStatuses)) {
      throw new CRM_Core_Exception("entity statuses are not supported by the selected entity");
    }

    return TRUE;
  }

  /**
   * Unassign all previously assigned statuses
   * from a certificate
   *
   * @param int $certificateId
   *    id of the certificate instance
   */
  private static function removeCertificateCurrentEntityStatus($certificateId) {
    $statusBAO = new CRM_Certificate_BAO_CompuCertificateStatus();
    $statusBAO->whereAdd("certificate_id = $certificateId");
    $statusBAO->delete(TRUE);
  }

}
