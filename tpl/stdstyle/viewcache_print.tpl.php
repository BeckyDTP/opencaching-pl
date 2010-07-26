<?php
/***************************************************************************
											./tpl/stdstyle/viewcache.tpl.php
															-------------------
		begin                : June 24 2004
		copyright            : (C) 2004 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

	***************************************************************************/

/***************************************************************************
	*
	*   This program is free software; you can redistribute it and/or modify
	*   it under the terms of the GNU General Public License as published by
	*   the Free Software Foundation; either version 2 of the License, or
	*   (at your option) any later version.
	*
	***************************************************************************/
?>

<!-- Text container -->

		<div class="content2-container line-box">

			<div class="">

				<div class="content-title-noshade-size1">
					<img src="{icon_cache}" class="icon32" id="viewcache-cacheicon" alt="{cachetype}" title="{cachetype}"/>{cachename} 
					<img src="tpl/stdstyle/images/free_icons/arrow_in.png" class="icon16" alt="" title="" align="middle" />&nbsp;<b>{oc_waypoint} 
					<img src="tpl/stdstyle/images/blue/kompas.png" class="icon16" alt="" title="" />{coords}</b>
				</div>
				{difficulty_icon_diff} {difficulty_icon_terr} {short_desc} {{hidden_by}} <a href="viewprofile.php?userid={userid_urlencode}">{owner_name}</a>

				<img src="tpl/stdstyle/images/free_icons/package.png" class="icon16" alt="" title="" align="middle" />&nbsp;<b>{cachesize}</b>
				{hidetime_start}<img src="tpl/stdstyle/images/free_icons/time.png" class="icon16" alt="" title="" align="middle" />&nbsp; {search_time}&nbsp;&nbsp;<img src="tpl/stdstyle/images/free_icons/arrow_switch.png" class="icon16" alt="" title="" align="middle" />&nbsp; {way_length} {hidetime_end}		
				{score_icon}<b><font color="{scorecolor}">{score}</font></b>

			</div>
					
		</div>
<!-- End Text Container -->

					<?php
global $usr, $lang, $hide_coords;			

?>


<!-- Text container -->
			{start_rr_comment}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					
					<img src="tpl/stdstyle/images/blue/crypt.png" class="icon32" alt="" />
					{{rr_comment_label}}
				</p>
				</div>
				<div class="content2-container">
				<p><br/>
				{rr_comment}
				</p>
			</div>
			{end_rr_comment}
<!-- End Text Container -->

<!-- Text container -->
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/describe.png" class="icon32" alt="" />
					{{descriptions}}&nbsp;{cache_attributes}{password_req}
				</p></div>
				<div class="content2-container">
				<div id='branding'>{branding}</div>
				<div id="description">
					<div id="viewcache-description">
						{desc}
					</div>
				</div>
			</div>
<!-- End Text Container -->

{waypoints_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/compas.png" class="icon32" alt="" />
					{{additional_waypoints}}
				</p></div>
				<p>
					{waypoints_content}
				</p><br />
			<div class="notice" id="viewcache-attributesend"><a class="links" href="http://wiki.opencaching.pl/index.php/Dodatkowe_waypointy_w_skrzynce" target="_blank">Zobacz opis i rodzaje dodatkowych waypointów</a></div>
{waypoints_end}
<!-- Text container -->

{hidehint_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/crypt.png" class="icon32" alt="" />
					<b>{{additional_hints}}</b>&nbsp;&nbsp;
				</p>
			</div>
					<div class="content2-container">
						<div id="viewcache-hints">
							{hints}
						</div>

					<div style="width:200px;align:right;float:right">
						{decrypt_table_start}
						<font face="Courier" size="2" style="font-family : 'Courier New', FreeMono, Monospace;">A|B|C|D|E|F|G|H|I|J|K|L|M</font><br>
						<font face="Courier" size="2" style="font-family : 'Courier New', FreeMono, Monospace;">N|O|P|Q|R|S|T|U|V|W|X|Y|Z</font>
						{decrypt_table_end}
					</div>
				</div>

{hidehint_end}
<!-- End Text Container -->

<!-- Text container -->
{hidepictures_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/picture.png" class="icon32" alt="" />
					{{images}}
				</p></div>
				<div class="content2-container">
				<div id="viewcache-pictures">
					{pictures}
				</div>
			</div>
{hidepictures_end}
<!-- End Text Container -->

<!-- Text container -->
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/logs.png" class="icon32" alt=""/>
					{{log_entries}}
					&nbsp;&nbsp;
					{found_icon} {founds}x
					{notfound_icon} {notfounds}x
					{note_icon} {notes}x
				</p>
			</div>
			<div class="content2-container" id="viewcache-logs">
					{logs}
			</div>
<!-- End Text Container -->

<hr noshade="noshade" />