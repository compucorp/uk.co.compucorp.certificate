<?php

/**
 * This is a shared interface for supported certificate entities.
 */
abstract class CRM_Certificate_Entity_AbstractEntity {

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
  abstract public function getCertificateConfigurationById($certificateId);

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
   * Gets all entity certificates vailable for a contact.
   *
   * @param int $contactId
   *  Id of the contact to retrieve certificates for.
   *
   * @return Array
   */
  abstract public function getContactCertificates($contactId);

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
