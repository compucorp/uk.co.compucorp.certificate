var waitForElement = function ($, elementPath, callBack) {
  window.setTimeout(function () {
    if ($(elementPath).length) {
      callBack($, $(elementPath));
    } else {
      waitForElement($, elementPath, callBack);
    }
  }, 500)
}

var getDownloadLink = function () {
  let a = document.createElement('a');
  a.classList.add('action-item', 'certificate__case-download-link')
  a.innerHTML = CRM.utils.formatIcon('fa-download', ts('Download Certificate'))
  a.append(ts('  Download Certificate'))
  a.setAttribute('href', CRM.vars.certificate.download_url);
  return a
}

CRM.$(function ($) {
  if (CRM.vars.certificate.type && CRM.vars.certificate.type == 'cases') {
    waitForElement($, 'div.case-control-panel > div:nth-child(2) > p', function ($, elements) {
      elements[0].append(getDownloadLink())
    })
  }
});
