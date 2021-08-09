/* eslint-env jasmine */

describe('DownloadCertificateCaseAction', () => {
  let DownloadCertificateCaseAction;

  beforeEach(module('certificate'));

  beforeEach(inject((_DownloadCertificateCaseAction_) => {
    DownloadCertificateCaseAction = _DownloadCertificateCaseAction_;
  }));

  describe('visibility', () => {
    var returnValue;

    beforeEach(() => {
      returnValue = DownloadCertificateCaseAction.isActionAllowed({}, {});
    });

    it('shows the action', () => {
      expect(returnValue).toBe(true);
    });
  });
});
