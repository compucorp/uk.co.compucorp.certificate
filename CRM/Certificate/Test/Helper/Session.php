<?php

trait CRM_Certificate_Test_Helper_Session {

  /**
   * Register contact in session.
   *
   * @param int $contactID
   *   Contact Id.
   */
  private function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }

  /**
   * Unregister contact from session.
   */
  private function unregisterCurrentLoggedInContactFromSession() {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', NULL);
  }

}
