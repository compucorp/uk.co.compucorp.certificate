<div class="crm-content-block crm-block">
  <div class="action-link">
    <a href="/civicrm/admin/certificates/add?reset=1" class="button crm-popup"><span><i class="crm-i fa-plus-circle"
          aria-hidden="true"></i> {ts}New{/ts}</span></a>
  </div>
  {include file="CRM/common/enableDisableApi.tpl"}
  {include file="CRM/common/jsortable.tpl"}
  <div id="all">
    <table class="row-highlight">
      <thead>
        <tr>
          <th id="sortable">{ts}Certificate Name{/ts}</th>
          <th>{ts}Type{/ts}</th>
          <th>{ts}Linked to{/ts}</th>
          <th>{ts}Status{/ts}</th>
          <th id="nosort">{ts}Action{/ts}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$rows item=row}
        <tr>
          <td>{$row.name}</td>
          <td>{$row.type}</td>
          <td>{$row.linked_to}</td>
          <td>{$row.status}</td>
          <td>{$row.action}</td>
        </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
</div>

<script language="javascript" type="text/javascript">
  { literal }
  CRM.$(function ($) {
    $('a.crm-popup').on('crmPopupFormSuccess', function (e) {
      CRM.refreshParent(e);
    });
  });
  { /literal}
</script>