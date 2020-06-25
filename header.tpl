<html>
  <head>
    <title>Steam Achievement Hunter</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#">Steam Achievement Hunter</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="?">Home <span class="sr-only">(current)</span></a>
          </li>
          {if isset($smarty.request.appid) and isset($smarty.request.language) and isset($smarty.request.user)}
            <li class="nav-item {if $smarty.request.action == 'results-scan-history'}active{/if}">
              <a class="nav-link" href="?action=results-scan-history&appid={$smarty.request.appid|appid}&language={$smarty.request.language}&user={$smarty.request.user|steamid}">History <span class="sr-only">(current)</span></a>
            </li>
          {/if}
        </ul>
      </div>
    </nav>
