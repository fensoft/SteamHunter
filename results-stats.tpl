<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/css/theme.blue.css">
<table class="table table-dark tablesorter">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
  {foreach $results->playerstats->stats as $key=>$value}
    <tr>
      <td>
        {$value->name}
      </td>
      <td>
        {$value->value}
      </td>
    </tr>
  {/foreach}
</table>
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/jquery.tablesorter.js"></script>
<script>$("table").tablesorter();</script>