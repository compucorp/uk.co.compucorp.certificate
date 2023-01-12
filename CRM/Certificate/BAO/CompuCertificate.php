<?php

use CRM_Certificate_ExtensionUtil as E;
use CRM_Certificate_Enum_DownloadFormat as DownloadFormat;
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
    $certificateBAO->whereDateIsValid();
    $certificateBAO->whereAdd('start_date IS NULL OR end_date IS NULL OR start_date = CURRENT_DATE OR end_date = CURRENT_DATE OR CURRENT_TIMESTAMP BETWEEN start_date AND end_date');
    $certificateBAO->selectAdd(self::$_tableName . '.id' . ' as certificate_id');
    $certificateBAO->find();

    $configuredCertificates = $certificateBAO->fetchAll();

    return $configuredCertificates;
  }

  /**
   * Returns the supported download formats.
   *
   * @return array
   */
  public static function getSupportedDownloadFormats() {
    return [
      DownloadFormat::PDF  => E::ts('PDF'),
      DownloadFormat::IMAGE   => E::ts('Image'),
    ];
  }

  /**
   * Add condition to ensure the certificate start_date and end_date is valid.
   */
  public function whereDateIsValid() {
    $this->whereAdd('(start_date IS NULL OR end_date IS NULL OR start_date = CURRENT_DATE OR end_date = CURRENT_DATE OR CURRENT_TIMESTAMP BETWEEN start_date AND end_date)');
  }

}
