{crmStyle ext=uk.co.compucorp.certificate file=css/style.css}

<div id="bootstrap-theme">
  <div class="panel panel-default certificate__create-form-panel"">
    <div class=" panel-body">
    <div class="form-hoizontal">
      {foreach from=$elementNames item=elementName}
      <div class="form-group row">
        <label class="col-sm-2 control-label">{$form.$elementName.label}</label>
        <div class="col-sm-7 col-md-5">
          {$form.$elementName.html}
        </div>
      </div>
      {/foreach}
    </div>
  </div>

  <div class="crm-submit-buttons panel-footer">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
</div>

<script language="javascript" type="text/javascript">

  let ref = { $entityRefs }
  let statusRef = { $entityStatusRefs }

  { literal }
  CRM.$(function ($) {
    /**
     * if entity is selected we want to populate the 
     * linked_to (entity type) and status (entity status) entity reference field
     * with the right values
     */
    CRM.$('[name=type]').on('change', function (e) {
      if (e.target.value > 0) {

        $('[name=linked_to]').val('');
        $('[name=statuses]').val('');

        $('[name=linked_to]')
          .attr('placeholder', ref[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(ref[e.target.value])

        $('[name=statuses]')
          .attr('placeholder', statusRef[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(statusRef[e.target.value])
      }
    })

    //this is to trigger the entity ref, when value of certifcate type is set from the backend
    if ($('[name=type]')[0].value) {
      $('[name=type]').change();
    }
  });
  { /literal}
</script>