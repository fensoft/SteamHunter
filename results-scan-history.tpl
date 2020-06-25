{foreach $history as $key=>$value}
  <a href="?action=results-scan&appid={$smarty.request.appid}&language={$value.language}&user={$smarty.request.user}&snapshot={$value.time}">{$value.time|date_format:"%d/%m/%y %H:%M:%S"}</a><br/>
{/foreach}