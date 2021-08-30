<?php

/**
 * Class CRM_Certificate_Service_CaseIdFromUrl.
 *
 * Service for detecting the case id from a Url.
 */
class CRM_Certificate_Service_CaseIdFromUrl {

  /**
   * Gets the caseId from the URL.
   * @see https://github.com/civicrm/org.civicrm.casetokens/blob/master/casetokens.php#L133
   *
   * @return int|null
   */
  public function get() {
    $caseId = NULL;
    // Hack to get case id from the url
    if (!empty($_GET['caseid'])) {
      $caseId = (int) $_GET['caseid'];
    }

    return $caseId;
  }

}
