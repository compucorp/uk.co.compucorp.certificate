<div class="crm-block crm-form-block crm-certificate-image-format-form-block">
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

{if $action eq 8}
  <div class="messages status no-popup">
      {icon icon="fa-info-circle"}{/icon}
        {ts 1=$formatName}WARNING: You are about to delete a certificate image format.{/ts}<p>{ts}This will remove the format from all certificate that use it. Do you want to continue?{/ts}</p>
  </div>
{else}

<div id="bootstrap-theme">
  <div class="panel panel-default certificate__create-form-panel"">
    <div class=" panel-body">
    <div class="form-hoizontal">
      {foreach from=$elementNames item=elementName}
      <div class="form-group row {$elementName}">
        <label class="col-sm-2 control-label">{$form.$elementName.label}</label>
        <div class="col-sm-7 col-md-5">
          {$form.$elementName.html}
        </div>
      </div>
      {/foreach}
    </div>
  </div>

{/if}
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
<script language="javascript" type="text/javascript">
  { literal }
  CRM.$(function ($) {
    ['#width', '#height', '#quality'].forEach((id) => $(id).attr('type', 'number'));
  });
  { /literal}
</script>
