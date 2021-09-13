{crmStyle ext=uk.co.compucorp.certificate file=css/style.css}

<div id="bootstrap-theme">
  <div class="certificate__action-link">
    <a href="/civicrm/admin/certificates/add?reset=1" class="btn btn-primary crm-popup">
      <span class="btn-icon"><i class="fa fa-plus"></i></span> {ts}New Certificate{/ts}
    </a>
  </div>

  <div class="panel panel-default">
    {include file="CRM/common/enableDisableApi.tpl"}
    {include file="CRM/common/jsortable.tpl"}
    <table class="table">
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