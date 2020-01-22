Please enter the steamid or nickname of the player.<br/>
You will then have a report of all stats !<br/>
<br/>

<form action="?" method="get">
  <input name="action" type="hidden" value="results-stats">
  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label">
      <a href="https://steamdb.info/apps/" target="_blank">App ID</a> or game name
    </label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="AppID" name="appid" value="PAYDAY 2">
    </div>

    <label for="inputPassword" class="col-sm-2 col-form-label">Player</label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="SteamID" name="user">
    </div>
  </div>
  <button type="submit" class="btn btn-primary mb-2">Show stats</button>
</form>
