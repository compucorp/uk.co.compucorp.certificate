/* eslint-env jasmine */

describe('DownloadCertificateCaseAction', () => {
  let DownloadCertificateCaseAction, CasesMockData, $window, civicaseCrmUrl,
    caseObj;

  beforeEach(module('certificate', 'civicase.data', ($provide) => {
    $provide.value('$window', jasmine.createSpyObj('$window', ['open']));
    $provide.value('civicaseCrmUrl', jasmine.createSpy('civicaseCrmUrl'));
  }));

  beforeEach(inject((_DownloadCertificateCaseAction_, _CasesData_,
    _civicaseCrmUrl_, _$window_) => {
    DownloadCertificateCaseAction = _DownloadCertificateCaseAction_;
    CasesMockData = _CasesData_;
    civicaseCrmUrl = _civicaseCrmUrl_;
    $window = _$window_;
  }));

  describe('visibility', () => {
    var returnValue;

    describe('when case is not selected', () => {
      beforeEach(() => {
        returnValue = DownloadCertificateCaseAction.isActionAllowed({}, []);
      });

      it('hides the action', () => {
        expect(returnValue).toBeFalsy();
      });
    });

    describe('when case is selected but not used inside case details section', () => {
      beforeEach(() => {
        caseObj = CasesMockData.get().values[0];
        returnValue = DownloadCertificateCaseAction.isActionAllowed(
          {},
          [caseObj],
          { mode: 'not-case-details' }
        );
      });

      it('hides the action', () => {
        expect(returnValue).toBeFalsy();
      });
    });

    describe('when case is selected and used inside case details section', () => {
      describe('certificate is available', () => {
        beforeEach(() => {
          caseObj = CasesMockData.get().values[0];
          caseObj.is_download_certificate_available = true;

          returnValue = DownloadCertificateCaseAction.isActionAllowed(
            {},
            [caseObj],
            { mode: 'case-details' }
          );
        });

        it('shows the action', () => {
          expect(returnValue).toBeTruthy();
        });
      });

      describe('certificate is not available', () => {
        beforeEach(() => {
          caseObj = CasesMockData.get().values[0];
          caseObj.is_download_certificate_available = false;

          returnValue = DownloadCertificateCaseAction.isActionAllowed(
            {},
            [caseObj],
            { mode: 'case-details' }
          );
        });

        it('hides the action', () => {
          expect(returnValue).toBeFalse();
        });
      });
    });
  });

  describe('when action is clicked', () => {
    beforeEach(() => {
      caseObj = CasesMockData.get().values[0];
      civicaseCrmUrl.and.returnValue('CRM Mock URL');
      DownloadCertificateCaseAction.doAction([caseObj]);
    });

    it('downloads the certificate', () => {
      expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/certificates/case', {
        case_id: '141',
        contact_id: '170'
      });
      expect($window.open).toHaveBeenCalledWith('CRM Mock URL', '_blank');
    });
  });
});
