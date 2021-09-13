<?php

class CRM_Certificate_BAO_CompuCertificateEntityType extends CRM_Certificate_DAO_CompuCertificateEntityType {

  /**
   * Create a new CertificateEntityType based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_Certificate_DAO_CompuCertificateEntityType|NULL
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateEntityType';
    $entityName = 'CompuCertificateEntityType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates multiple CertificateEntityType and attach them to
   * a certificate
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   *    certificate instance to attach entity types to
   * @param array $entityTypeIds
   *    array of entityType ids
   *
   * @return Array
   */
  public static function assignCertificateEntityTypes($certificate, $entityTypeIds) {
    $result = [];
    $certificateId = $certificate->id;

    if (!is_array($entityTypeIds)) {
      throw new CRM_Core_Exception('Entity Types parameter must be an array');
    }

    self::validateEntityTypeIds($certificate, $entityTypeIds);

    self::removeCertificateCurrentEntityType($certificateId);

    foreach ($entityTypeIds as $entityTypeId) {
      $entityTypeDAO = self::create([
        'certificate_id' => $certificateId,
        'entity_type_id' => $entityTypeId,
      ]);

      $result[] = $entityTypeDAO->toArray();
    }

    return $result;
  }

  /**
   * Ensure only allowed entity type ids are allowed per the configured entity
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   *    certificate instance to attach entity types to
   * @param array $entityTypeIds
   *    array of entityType ids
   *
   * @return bool
   *
   * @throws CRM_Core_Exception
   */
  private static function validateEntityTypeIds($certificate, $entityTypeIds) {
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificate->entity);
    $types = $entity->getTypes();
    $invalidEntityTypes = array_diff($entityTypeIds, $types);

    if (!empty($invalidEntityTypes)) {
      throw new CRM_Core_Exception("entity types are not supported by the selected entity");
    }

    return TRUE;
  }

  /**
   * Unassign all previously assigned entity types
   * from a certificate
   *
   * @param int $certificateId
   *    id of the certificate instance
   */
  private static function removeCertificateCurrentEntityType($certificateId) {
    $entityTypeBAO = new CRM_Certificate_BAO_CompuCertificateEntityType();
    $entityTypeBAO->whereAdd("certificate_id = $certificateId");
    $entityTypeBAO->delete(TRUE);
  }

}
