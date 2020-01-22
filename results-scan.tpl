<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/css/theme.blue.css">
<table class="table table-dark tablesorter">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Date</th>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
    </tr>
  </thead>
  <tbody>
  {foreach $results as $key=>$value}
    <tr apiname="{$value->apiname}" class="{$value->class}">
      <td style="font-size: 50%;">
        {if isset($value->code)}
          <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#{$value->apiname}">#</button>
        {/if}
        {$value->apiname}
        {if isset($value->code)}
          <div class="modal fade" id="{$value->apiname}" tabindex="-1" role="dialog" aria-labelledby="{$value->apiname}Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="{$value->apiname}Label">Code JSON</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  ,{$value->code}
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        {/if}
      </td>
      <td>{$value->unlocktime|date_format:"%d/%m/%y %H:%M:%S"}</td>
      <td>{$value->name}</td>
      <td>{$value->description}</td>
    </tr>
  {/foreach}
</table>
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/jquery.tablesorter.js"></script>
<script>$("table").tablesorter();</script>