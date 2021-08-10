(function (angular) {
  var module = angular.module('certificate');

  module.service('DownloadCertificateCaseAction', DownloadCertificateCaseAction);

  /**
   * @param {object} civicaseCrmUrl civicrm url service
   */
  function DownloadCertificateCaseAction (civicaseCrmUrl) {
    /**
     * Checks if the Action is allowed
     *
     * @param {object} action action
     * @param {Array} cases cases
     * @returns {boolean} if action is allowed
     */
    this.isActionAllowed = function (action, cases) {
      return cases[0].is_download_certificate_available;
    };

    /**
     * Click event handler for the Action
     *
     * @param {Array} cases cases
     * @param {object} action action
     * @param {Function} callbackFn call back function
     */
    this.doAction = function (cases, action, callbackFn) {
      var selectedCase = cases[0];
      var url = civicaseCrmUrl('civicrm/certificates/case', {
        id: selectedCase.client[0].contact_id,
        cid: selectedCase.id
      });
      var win = window.open(url, '_blank');

      win.focus();
    };
  }
})(angular);
