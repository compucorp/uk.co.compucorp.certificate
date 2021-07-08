<?php
use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_BAO_CompuCertificateEntityType extends CRM_Certificate_DAO_CompuCertificateEntityType {

  /**
   * Create a new CompuCertificateEntityType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateEntityType|NULL
   * 
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
   * Creates mulitple CompuCertificateEntityType and attach them to
   * a certificate
   * 
   * @param int $CompuCertificateId
   *    Id of the certificate instance
   * 
   * @param array $entityTypes
   *    array of entityType ids 
   */
  public static function createEntityTypes($CompuCertificateId, $entityTypes) {
    $result = array();

    if (!is_array($entityTypes)) {
      throw new API_Exception('Entity Types paramter must be an array');
    }

    //remove previously synced entitytypes if any
    $entityTypeBAO = new CRM_Certificate_BAO_CompuCertificateEntityType();
    $entityTypeBAO->whereAdd("certificate_id = $CompuCertificateId");
    $entityTypeBAO->delete(true);

    foreach ($entityTypes as $entityTypeId) {
      $entityTypeDAO = self::create([
        'certificate_id' => $CompuCertificateId,
        'entity_type_id' => $entityTypeId
      ]);

      $result[] = $entityTypeDAO->toArray();
    }
    return $result;
  }

}
