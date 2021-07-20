<div class="crm-block crm-form-block crm-uf-field-form-block">
  {* HEADER *}

  <table class="form-layout-compressed">
    {foreach from=$elementNames item=elementName}
    <tr class="crm-uf-field-form-block-field_name">
      <td class="label">{$form.$elementName.label}</td>
      <td>{$form.$elementName.html}</td>
    </tr>
    {/foreach}
  </table>

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
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