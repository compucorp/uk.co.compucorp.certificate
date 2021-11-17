<?php

/**
 * This is a shared interface for supported certificate entities.
 */
interface CRM_Certificate_Entity_EntityInterface {

  /**
   * Stores a certificate configuration
   *
   * @param array $values
   *    Configuration data
   *
   * @return array
   *   New Certificate configuration values
   */
  public function store($values);

  /**
   * Returns array of type ids supported by the entity
   *
   * @return Array|NULL
   */
  public function getTypes();

  /**
   * Returns array of status ids supported by the entity
   *
   * @return Array|NULL
   */
  public function getStatuses();

  /**
   * Returns the status(es) of the entity for which a certificate
   * has been configured for
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve entity statuses for
   *
   * @return Array
   */
  public function getCertificateConfiguredStatuses($certificateId);

  /**
   * Returns the type(s) of the entity for which a certificate
   * has been configured for
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve entity types for
   *
   * @return Array
   */
  public function getCertificateConfiguredTypes($certificateId);

  /**
   * Returns a configured certificate by ID
   *
   * @param int $certificateId
   *  Id of the certificate instance to retrieve.
   */
  public function getCertificateConfigurationById($certificateId);

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
  public function getCertificateConfiguration($entityId, $contactId);

  /**
   * Gets all entity certificates vailable for a contact.
   *
   * @param int $contactId
   *  Id of the contact to retrieve certificates for.
   *
   * @return Array
   */
  public function getContactCertificates($contactId);

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
  public function getCertificateDownloadUrl($entityId, $contactId, $absolute = FALSE);

}
