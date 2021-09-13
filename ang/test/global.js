/* eslint-env jasmine */
/* eslint no-param-reassign: "error" */

((CRM) => {
  CRM.civicase = {};
  CRM['civicase-base'] = {};
  CRM.angular = { requires: {} };

  /**
   * Dependency Injection for certificate module, defined in ang/prospect.ang.php
   * For unit testing they needs to be mentioned here
   */
  CRM.angular.requires.certificate = ['civicase-base'];
})(CRM);
