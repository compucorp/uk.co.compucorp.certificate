(function (angular) {
  var module = angular.module('certificate');

  module.run(function () {
    (function init () {
      CRM.civicase.caseActions = getCaseActionsForCertificate().concat(CRM.civicase.caseActions);
    }());

    /**
     * Get the Case Actions for Certificate.
     *
     * @returns {Array} case actions
     */
    function getCaseActionsForCertificate () {
      return [
        {
          title: 'Download Certificate',
          action: 'DownloadCertificate',
          icon: 'fa-download'
        }
      ];
    }
  });
})(angular);
