<?php

use CRM_Certificate_ExtensionUtil as E;
use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Certificate_Enum_DownloadFormat as DownloadFormat;
use CRM_Certificate_BAO_CompuCertificateStatus as CompuCertificateStatus;
use CRM_Certificate_BAO_CompuCertificateEntityType as CompuCertificateEntityType;
use CRM_Certificate_BAO_CompuCertificateRelationshipType as CompuCertificateRelationshipType;

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
    $certificateBAO->selectAdd(self::$_tableName . '.id' . ' as certificate_id');
    $certificateBAO->find();

    $configuredCertificates = $certificateBAO->fetchAll();

    return $configuredCertificates;
  }

  /**
   * Returns all the certificate(s) configured for an entity based on the configured relationship types.
   *
   * @param int $entity
   * @param int $contactId
   *
   * @return array
   *   Returns an array of certificate configuration grouped by the related contact ids.
   */
  public static function getRelationshipEntityCertificates($entity, $contactId) {
    $certificateRelationshipType = new CompuCertificateRelationshipType();
    $certificateRelationshipType->joinAdd(['relationship_type_id', new Relationship(), 'relationship_type_id'], 'INNER', 'rlt');
    $certificateRelationshipType->whereAdd("
    (
      (rlt.start_date IS NULL AND rlt.end_date IS NULL AND rlt.is_active = 1) OR
      (rlt.start_date <= date(NOW()) AND rlt.end_date IS NULL) OR 
      (rlt.start_date IS NULL AND rlt.end_date > date(NOW())) OR 
      (rlt.start_date <= date(NOW()) AND rlt.end_date > date(NOW()))
    )");
    $certificateRelationshipType->whereAdd('rlt.is_active = 1');

    $certificateBAO = new self();
    $certificateBAO->joinAdd(['id', new CompuCertificateEntityType(), 'certificate_id'], 'LEFT', 'cert_type');
    $certificateBAO->joinAdd(['id', new CompuCertificateStatus(), 'certificate_id'], 'LEFT', 'cert_status');
    $certificateBAO->joinAdd(['id', $certificateRelationshipType, 'certificate_id'], 'INNER');
    $certificateBAO->whereAdd('entity = ' . $entity);
    $certificateBAO->whereAdd('contact_id_a = ' . $contactId);
    $certificateBAO->whereDateIsValid();
    $certificateBAO->selectAdd();
    $certificateBAO->selectAdd(self::$_tableName . '.id as certificate_id, ' . self::$_tableName . '.start_date, ' . self::$_tableName . '.end_date, name, entity, entity_type_id, status_id, template_id, download_format, contact_id_b as related_contact, contact_id_a as contact');
    $certificateBAO->find();

    $configuredCertificates = $certificateBAO->fetchAll();
    $groupedByRelatedContactId = [];
    foreach ($configuredCertificates as $configuredCertificate) {
      $groupedByRelatedContactId[$configuredCertificate['related_contact']][] = $configuredCertificate;
    }

    return $groupedByRelatedContactId;
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
    $prefix = self::$_tableName;
    $this->whereAdd("({$prefix}.start_date IS NULL OR date({$prefix}.start_date) <= date(NOW())) AND ({$prefix}.end_date IS NULL OR date({$prefix}.end_date) >= date(NOW()) )");
  }

  /**
   * Get the instance relationship types
   */
  public function getRelationshipTypes(string $column = NULL) {
    $relationshipTypes = CompuCertificateRelationshipType::getByCertificateId($this->id);
    if (!empty($relationshipTypes) && !empty($relationshipTypes)) {
      $relationshipTypes = array_column($relationshipTypes, $column);
    }

    return $relationshipTypes;
  }

}
