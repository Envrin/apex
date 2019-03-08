
<h1>Cron Manager</h1>

<e:form>

<e:box>
	<e:box_header title="Crontab Jobs">
		<p>The below table lists all crontab jobs setup on the system, including their interval and next scheduled execution time.  You may change the time interval or turn any crontab job on / off by clicking the appropriate Manage button below.</p>
	</e:box_header>

	<e:function alias="display_table" table="core:crontab">

	<center>
	<e:submit value="run" label="Execute Checked Cron Jobs">
	</center><br />

</e:box><br />


