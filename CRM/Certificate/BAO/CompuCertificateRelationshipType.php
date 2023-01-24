<?php

class CRM_Certificate_BAO_CompuCertificateRelationshipType extends CRM_Certificate_DAO_CompuCertificateRelationshipType {

  /**
   * Create a new CompuCertificateRelationshipType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateRelationshipType|NULL
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateRelationshipType';
    $entityName = 'CompuCertificateRelationshipType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates CertificateRelationshipType and attach them to
   * a certificate
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   *    certificate instance to attach entity types to
   * @param array $relationshipTypeIds
   *    array of relationshipType ids
   *
   * @return Array
   */
  public static function assignCertificateRelationshipTypes($certificate, $relationshipTypeIds) {
    $result = [];
    $certificateId = $certificate->id;

    if (!is_array($relationshipTypeIds)) {
      throw new CRM_Core_Exception('Relationship Types parameter must be an array');
    }

    self::removeCertificateCurrentRelationshipType($certificateId);

    foreach ($relationshipTypeIds as $relationshipTypeId) {
      $relationshipTypeDAO = self::create([
        'certificate_id' => $certificateId,
        'relationship_type_id' => $relationshipTypeId,
      ]);

      $result[] = $relationshipTypeDAO->toArray();
    }

    return $result;
  }

  /**
   * Unassign all previously assigned relationship types
   * from a certificate
   *
   * @param int $certificateId
   *    id of the certificate instance
   */
  private static function removeCertificateCurrentRelationshipType($certificateId) {
    $entityTypeBAO = new CRM_Certificate_BAO_CompuCertificateRelationshipType();
    $entityTypeBAO->whereAdd("certificate_id = $certificateId");
    $entityTypeBAO->delete(TRUE);
  }

  /**
   * Returns CompuCertificateRelationshipTypes by certificate ID.
   *
   * @param int $certificateId
   *  id of the certificate instance
   */
  public static function getByCertificateId($certificateId) {
    $relationshipTypes = new self();
    $relationshipTypes->whereAdd('certificate_id = ' . $certificateId);
    $relationshipTypes->find();
    return $relationshipTypes->fetchAll();
  }

}
