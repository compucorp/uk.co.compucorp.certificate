<?php

use CRM_Certificate_BAO_CompuCertificateStatus as CompuCertificateStatus;
use CRM_Certificate_BAO_CompuCertificateEntityType as CompuCertificateEntityType;

class CRM_Certificate_BAO_CompuCertificate extends CRM_Certificate_DAO_CompuCertificate {

  /**
   * Create a new CompuCertificate based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificate|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificate';
    $entityName = 'CompuCertificate';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  public static function deleteById($id) {
    $className = 'CRM_Certificate_DAO_CompuCertificate';
    $entityName = 'CompuCertificate';

    CRM_Utils_Hook::pre('delete', $entityName, $id);
    $instance = new $className();
    $instance->id = $id;
    $instance->delete();
    CRM_Utils_Hook::post('delete', $entityName, $id);
  }

  /**
   * Returns all the certificate(s) configured for an entity.
   *
   * @param int $entity
   * @return array
   */
  public static function getEntityCertificates($entity) {
    $certificateBAO = new self();
    $certificateBAO->joinAdd(['id', new CompuCertificateEntityType(), 'certificate_id'], 'LEFT', 'cert_type');
    $certificateBAO->joinAdd(['id', new CompuCertificateStatus(), 'certificate_id'], 'LEFT', 'cert_status');
    $certificateBAO->whereAdd('entity = ' . $entity);
    $certificateBAO->selectAdd(self::$_tableName . '.id' . ' as certificate_id');
    $certificateBAO->find();

    $configuredCertificates = $certificateBAO->fetchAll();

    return $configuredCertificates;
  }

}
