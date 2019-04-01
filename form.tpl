Please enter the steamid or nickname of all players.<br/>
You will then have a report of all common achievements to do !<br/>
# is the number of completed achievement for specified players<br/>
<br/>

<form action="?" method="get">
  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label">
      <a href="https://steamdb.info/apps/" target="_blank">App ID</a> or game name
    </label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="AppID" name="appid" value="PAYDAY 2">
    </div>

    {if !isset($request.advanced)}
      <div style="display: none">
    {/if}

    <label for="inputPassword" class="col-sm-2 col-form-label">Language</label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="Language" name="language" value="english">
    </div>

    <label for="inputPassword" class="col-sm-2 col-form-label">Minimum # of achievements</label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="Minimum" name="min" value="">
    </div>

    <label for="inputPassword" class="col-sm-2 col-form-label">Maximum # of achievements</label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="Maximum" name="max" value="0">
    </div>

    <label for="inputPassword" class="col-sm-2 col-form-label">Filter achievements</label>
    <div class="col-sm-10">
      <input type="input" class="form-control" placeholder="List of space separated achievement_id" name="filter" value="">
    </div>

    <label for="inputPassword" class="col-sm-2 col-form-label">Filter preset</label>
    <div class="col-sm-10">
      <select class="form-control" name="filters">
        <option value="">None</option>
        <option value="diamonds_are_forever uno_1 kosugi_2 charliesierra_5 uno_2 armored_2 armored_1 uno_3 axe_3 uno_9 lets_do_this melt_3 trk_af_3 uno_6 moon_5 uno_8 lord_of_war halloween_2 doctor_fantastic i_wasnt_even_there bob_3 bigbank_5 pig_2 cac_26 payback_2 bat_2 kenaz_4 cow_10 cow_4 uno_7 live_2 pal_2 green_6 dark_3 cac_13 peta_3 cane_2 fort_4 born_5 cac_9 spa_5 fish_5 man_2 farm_3 berry_2 jerry_4 run_10 uno_4 wwh_9 dah_9 rvd_11 brb_8 tag_10 uno_5 sah_10 bph_11 nmh_10">Payday 2 Secrets</option>
      </select>
    </div>
    {if !isset($request.advanced)}
      </div>
    {/if}
    {for $i=1 to 9}
      <label for="inputPassword" class="col-sm-2 col-form-label">Player {$i}</label>
      <div class="col-sm-10">
        <input type="input" class="form-control" placeholder="SteamID" name="user{$i}">
      </div>
    {/for}
  </div>
  <button type="submit" class="btn btn-primary mb-2">Confirm</button>
  {if !isset($request.advanced)}
    <a href="?advanced=1" class="btn btn-warning mb-2">Advanced</a>
  {/if}
</form>

Statistics: {$visits} visits, {$ips} visitors, {$appids} games, {$users} steamids<br/>
<a href="https://github.com/fensoft/SteamHunter">fork me on github</a>
