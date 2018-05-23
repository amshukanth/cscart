<script id="_agile_min_js" async type="text/javascript" src="https://{$addons.agilecrm.agile_domain}.agilecrm.com/stats/min/agile-min.js"> </script>
<script type="text/javascript" >
_agile.set_account('{$addons.agilecrm.agile_rest_api_key}', '{$addons.agilecrm.agile_domain}');

{if $addons.agilecrm.web_stats == "Y"}
	_agile.track_page_view();
{/if}

{if $addons.agilecrm.web_rules == "Y"}
	_agile_execute_web_rules();
{/if}

{if $auth.user_id}
	_agile.set_email("{$user_info.email}"); 
{/if}



</script>