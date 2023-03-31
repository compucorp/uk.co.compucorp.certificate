<?php

use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

/**
 * This is a shared interface for supported certificate entities.
 */
abstract class CRM_Certificate_Entity_AbstractEntity {

  /**
   * Returns the current entity;
   *
   * @return int
   */
  abstract public function getEntity();

  /**
   * Stores a certificate configuration
   *
   * @param array $values
   *    Configuration data
   *
   * @return array
   *   New Certificate configuration values
   */
  abstract public function store($values);

  /**
   * Returns array of type ids supported by the entity
   *
   * @return Array|NULL
   */
  abstract public function getTypes();

  /**
   * Returns array of status ids supported by the entity
   *
   * @return Array|NULL
   */
  abstract public function getStatuses();

  /**
   * Returns the status(es) of the entity for which a certificate
   * has been configured for
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve entity statuses for
   *
   * @return Array
   */
  abstract public function getCertificateConfiguredStatuses($certificateId);

  /**
   * Returns the type(s) of the entity for which a certificate
   * has been configured for
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve entity types for
   *
   * @return Array
   */
  abstract public function getCertificateConfiguredTypes($certificateId);

  /**
   * Returns a configured certificate by ID
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve.
   */
  public function getCertificateConfigurationById($certificateId) {
    $certificateBAO = CRM_Certificate_BAO_CompuCertificate::findById($certificateId);
    $statuses = $this->getCertificateConfiguredStatuses($certificateBAO->id);
    $types = $this->getCertificateConfiguredTypes($certificateBAO->id);
    $relationshipTypes = CRM_Certificate_BAO_CompuCertificateRelationshipType::getByCertificateId($certificateId);

    $certificate = [
      'name' => $certificateBAO->name,
      'type' => $certificateBAO->entity,
      'end_date' => $certificateBAO->end_date,
      'start_date' => $certificateBAO->start_date,
      'download_format' => $certificateBAO->download_format,
      'message_template_id' => $certificateBAO->template_id,
      'linked_to' => implode(',', array_column($types, 'id')),
      'statuses' => implode(',', array_column($statuses, 'id')),
      'relationship_types' => implode(',', array_column($relationshipTypes, 'relationship_type_id')),
    ];

    $this->addEntityExtraField($certificateBAO, $certificate);

    return $certificate;
  }

  /**
   * Gets a certificate configuration, if a configured certificate for the entity exists
   * whose type and status corresponds with the entityId passed, if not it returns false
   *
   * @param int $entityId
   *  Id of the entity to get a configured certificate for
   * @param int $contactId
   *  Id of the contact the entity belongs to
   *
   * @return \CRM_Certificate_BAO_CompuCertificate|bool
   */
  public function getCertificateConfiguration($entityId, $contactId) {
    try {
      $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
      $this->addEntityConditionals($certificateBAO, $entityId, $contactId);
      $certificateBAO->whereDateIsValid();
      $certificateBAO->orderBy(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id Desc');
      $certificateBAO->selectAdd(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id');
      $certificateBAO->find(TRUE);

      if (!empty($certificateBAO->id)) {
        return $certificateBAO;
      }
    }
    catch (\Exception $e) {
    }
    return FALSE;
  }

  /**
   * Gets entity certificates available for a contact based on relationship.
   *
   * @param int $contactId
   *  Id of the contact to retrieve related contact certificates for.
   *
   * @return Array
   */
  public function getRelationshipCertificates($contactId) {
    $certificates = [];
    $configuredCertificates = CompuCertificate::getRelationshipEntityCertificates($this->getEntity(), $contactId);

    foreach ($configuredCertificates as $relatedContactId => $configuredCertificate) {
      //for the related contacts, get their certificates that matches the certificate configuration.
      $certificates = array_merge($this->formatConfiguredCertificatesForContact($configuredCertificate, $relatedContactId), $certificates);
    }

    return $certificates;
  }

  /**
   * Add entity specific retrieval conditions.
   *
   * @param \CRM_Certificate_BAO_CompuCertificate $certificateBAO
   *  Object to add coniditions to.
   * @param int $entityId
   *  Id of the entity to get a configured certificate for.
   * @param int $contactId
   *  Id of the contact the entity belongs to.
   */
  abstract protected function addEntityConditionals($certificateBAO, $entityId, $contactId);

  /**
   * Add entity specific fields when retrieving an entity certificate.
   *
   * @param \CRM_Certificate_BAO_CompuCertificate $certificateBAO
   *  Object to add coniditions to.
   * @param array &$certificate
   *  Key-value pairs to append extra field to.
   */
  protected function addEntityExtraField($certificateBAO, &$certificate) {}

  /**
   * Gets all entity certificates vailable for a contact.
   *
   * @param int $contactId
   *  Id of the contact to retrieve certificates for.
   *
   * @return array
   */
  abstract public function getContactCertificates($contactId);

  /**
   * Formats certificate configurations into an entity certificate.
   *
   * @param array $configuredCertificates
   *  The configured certificates to format.
   * @param int $contactId
   *  The contact ID.
   * @return array
   */
  abstract public function formatConfiguredCertificatesForContact(array $configuredCertificates, $contactId);

  /**
   * Gets an entity certificate download URL.
   *
   * @param int $entityId
   *  Id of the entity to get a download URL for.
   * @param int $contactId
   *  Id of the contact the entity belongs to.
   * @param bool $absolute
   *   Whether to force the output to be an absolute link (beginning with a URI-scheme such as 'http:').
   *
   * @return string
   */
  abstract public function getCertificateDownloadUrl($entityId, $contactId, $absolute = FALSE);

}
