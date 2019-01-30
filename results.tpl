<a type="button" class="btn btn-danger" href="?">Back</a><div class="alert alert-primary" role="alert">
  There is {count($results)} achievements
</div>
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
    </tr>
  </thead>
  <tbody>
  {foreach $results as $key=>$value}
    <tr apiname="{$value->apiname}">
      <td>{$value->total}</td>
      <td>{$value->name}</td>
      <td>{$value->description}</td>
    </tr>
  {/foreach}
</table>
