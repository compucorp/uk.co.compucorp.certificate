(function (angular) {
  var module = angular.module('certificate');

  module.service('DownloadCertificateCaseAction', DownloadCertificateCaseAction);

  /**
   * @param {object} civicaseCrmUrl civicrm url service
   * @param {object} $window window object
   */
  function DownloadCertificateCaseAction (civicaseCrmUrl, $window) {
    /**
     * Checks if the Action is allowed
     *
     * @param {object} action action
     * @param {Array} cases cases
     * @param {object} attributes - item attributes.
     * @returns {boolean} if action is allowed
     */
    this.isActionAllowed = function (action, cases, attributes) {
      if (!cases[0] || attributes.mode !== 'case-details') {
        return;
      }

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
        contact_id: selectedCase.client[0].contact_id,
        case_id: selectedCase.id
      });
      $window.open(url, '_blank');
    };
  }
})(angular);
