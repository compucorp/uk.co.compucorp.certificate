<?php

/**
 * This is a shared interface for supported certificate entities.
 */
interface CRM_Certificate_Entity_EntityInterface {

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
   *  Id of the certificate instance to retrieve entity types for
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

}
