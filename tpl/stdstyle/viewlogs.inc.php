<?php

/***************************************************************************
	*                                         				                                
	*   This program is free software; you can redistribute it and/or modify  	
	*   it under the terms of the GNU General Public License as published by  
	*   the Free Software Foundation; either version 2 of the License, or	    	
	*   (at your option) any later version.
	*
	***************************************************************************/

/****************************************************************************
	        
   Unicode Reminder ??
                                 				                                
	 set template specific language variables
	
 ****************************************************************************/

	$functions_start = '<br/><img src="images/trans.gif" alt="" title="" class="icon16" />&nbsp;';
	$functions_middle = '&nbsp;';
	$functions_end = '';

	$decrypt_log = '<img src="tpl/stdstyle/images/free_icons/lock_open.png" class="icon16" alt="" title=""/>&nbsp;<a class="links" href="viewcache-test.php?cacheid={cacheid}&amp;nocryptlog=1#{decrypt_log_id}" onclick="var hint=document.getElementById(\'{decrypt_log_id}\');hint.innerHTML=convertROTStringWithBrackets(hint.innerHTML);void(0); return false;">'.tr('decrypt').'</a>';	
	$nodecrypt_log = '&nbsp;<img src="tpl/stdstyle/images/free_icons/lock.png" class="icon16" alt="" title=""/>&nbsp;<span style="font-weight;">.'tr('encyrpt_log').'</span>';	

	$edit_log = '<img src="tpl/stdstyle/images/free_icons/pencil.png" class="icon16" alt="" title=""/>&nbsp;<a class="links" href="editlog.php?logid={logid}">Edycja</a>&nbsp;';
	$remove_log = '<img src="tpl/stdstyle/images/free_icons/cross.png" class="icon16" alt="" title=""/>&nbsp;<a class="links" href="removelog.php?logid={logid}">Usuń</a>&nbsp;';
	$upload_picture = '<img src="tpl/stdstyle/images/action/16x16-addimage.png" class="icon16" alt="" title=""/>&nbsp;<a class="links" href="newpic.php?objectid={logid}&amp;type=1">Dodaj obrazek</a>&nbsp;';
	
	$remove_picture = ' <span class="removepic"><img src="tpl/stdstyle/images/log/16x16-trash.png" class="icon16" alt="" title=""/><a class="links" href="removepic.php?uuid={uuid}">Usuń</a></span>';

	$rating_picture = '<img src="images/rating-star.png" alt="Rekomendacja" /> '
?>
