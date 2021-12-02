window.waitForElement = function ($, elementPath, callBack) {
  window.setTimeout(function () {
    if ($(elementPath).length) {
      callBack($, $(elementPath));
    } else {
      window.waitForElement($, elementPath, callBack);
    }
  }, 500);
};

window.downloadLink = function () {
  const btn = document.createElement('button');
  const ts = CRM.ts('uk.co.compucorp.certificate');
  btn.innerHTML = ts(
    `<span class="ui-button-icon ui-icon crm-i fa-print"></span>
    <span class="ui-button-icon-space">Print Certificate</span>`
  );
  btn.setAttribute('type', 'button');
  btn.setAttribute('onclick', "window.open('" + CRM.vars.certificate.download_url + "')");
  btn.setAttribute('target', '_blank');
  btn.classList.add('ui-button', 'ui-corner-all', 'ui-widget');
  btn.style.marginRight = '5px';
  return btn;
};
