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
}
