<?php

use Utils\Database\XDb;
use Utils\Database\OcDb;

global $usr;

//prepare the templates and include all neccessary
if (!isset($rootpath))
    $rootpath = __DIR__ . DIRECTORY_SEPARATOR;
require_once('./lib/common.inc.php');

//Preprocessing
if ($error == false) {
    //set the template to process
    $tplname = 'start';

    // news
    require($stylepath . '/news.inc.php');

    $newscontent = '';

    $rs = XDb::xSql(
        'SELECT `news`.`date_posted` `date`, `news`.`content` `content` FROM `news`
        WHERE datediff(now(), news.date_posted) <= 31 AND `news`.`display`=1 AND `news`.`topic`=2
        ORDER BY `news`.`date_posted` DESC
        LIMIT 4');

    if ($r = XDb::xFetchArray($rs)) {
        $newscontent = '<div class="line-box">';
        $newscontent .= $tpl_newstopic_header;

        do{
            $news = '<div class="logs" style="width: 750px;">' . $tpl_newstopic_without_topic;
            $post_date = strtotime($r['date']);
            $news = mb_ereg_replace('{date}', fixPlMonth(htmlspecialchars(strftime("%d %B %Y", $post_date), ENT_COMPAT, 'UTF-8')), $news);
            $news = mb_ereg_replace('{message}', $r['content'], $news);
            $newscontent .= $news . "</div>\n";
        }while ($r = XDb::xFetchArray($rs));

        $newscontent .= "</div>\n";
    }
    tpl_set_var('display_news', $newscontent);

    XDb::xFreeResults($rs);

    global $dynstylepath;
    include ($dynstylepath . "totalstats.inc.php");

    // here is the right place to set up template replacements
    // example:
    // tpl_set_var('foo', 'myfooreplacement');
    // will replace {foo} in the templates
}

// diffrent oc server handling: display proper info depend on server running the code
$nodeDetect = substr($absolute_server_URI, -3, 2);
tpl_set_var('what_do_you_find_intro', tr('what_do_you_find_intro_' . $nodeDetect));

if ($powerTrailModuleSwitchOn)
    tpl_set_var('ptDisplay', 'block');
else
    tpl_set_var('ptDisplay', 'none');

if ($BlogSwitchOn)
    tpl_set_var('blogDisplay', 'block');
else
    tpl_set_var('blogDisplay', 'none');

/////////////////////////////////////////////////////
//Titled Caches
///////////////////////////////////////////////////

$usrid = -1;
$TitledCaches="";
$dbc = OcDb::instance();

if ( $usr != false )
    $usrid = $usr['userid'];

$query = "SELECT caches.cache_id, caches.name cacheName, adm1 cacheCountry, adm3 cacheRegion, caches.type cache_type,
        caches.user_id, user.username userName, cache_titled.date_alg, cache_logs.text, cache_desc.short_desc,
        logUser.user_id logUserId, logUser.username logUserName
        FROM cache_titled
            JOIN caches ON cache_titled.cache_id = caches.cache_id
            LEFT JOIN cache_desc ON caches.cache_id = cache_desc.cache_id and language=:1
            JOIN cache_location ON caches.cache_id = cache_location.cache_id
            JOIN user ON caches.user_id = user.user_id
            JOIN cache_logs ON cache_logs.id = cache_titled.log_id
            JOIN user logUser ON logUser.user_id = cache_logs.user_id
        ORDER BY date_alg DESC
        LIMIT 1";

$s = $dbc->multiVariableQuery($query, $lang);

$pattern = "<br><span style='font-size:13px'><img src='{cacheIcon}' class='icon16' alt='Cache' title='Cache' />
        <a href='viewcache.php?cacheid={cacheId}'><b>{cacheName}</b></a></span>

        <span style='font-size:11px'> ".tr('hidden_by'). "</span>
        <span style='font-size:13px'><a href='viewprofile.php?userid={userId}'><b>{userName}</b></a></span><br>

        <span style='font-size:11px;font-style:italic'>{cacheShortDesc}</span><br>

        <span class='content-title-noshade' style='font-size:11px'>{country} > {region}</span>
        <br><br>
        <table class='CacheTitledLog' >
                <tr><td>{logText}
                <br><br><img src='images/rating-star.png' alt=''> Autor: <a href='viewprofile.php?userid={logUserId}'><b>{logUserName}</b></a></td></tr>
        </table>";

while( $rec = $dbc->dbResultFetch($s) ) {

   $line = $pattern;

   $line = mb_ereg_replace('{cacheIcon}', myninc::checkCacheStatusByUser($rec, $usrid), $line );
   $line = mb_ereg_replace('{dateAlg}', $rec[ "date_alg" ], $line );
   $line = mb_ereg_replace('{cacheName}', $rec[ "cacheName" ], $line );
   $line = mb_ereg_replace('{userId}', $rec[ "user_id" ], $line );
   $line = mb_ereg_replace('{userName}', $rec[ "userName" ], $line );
   $line = mb_ereg_replace('{cacheId}', $rec[ "cache_id" ], $line );
   $line = mb_ereg_replace('{country}', $rec[ "cacheCountry" ], $line );
   $line = mb_ereg_replace('{region}', $rec[ "cacheRegion" ], $line );
   $line = mb_ereg_replace('{cacheShortDesc}', $rec[ "short_desc" ], $line );
   $line = mb_ereg_replace('{logUserId}', $rec[ "logUserId" ], $line );
   $line = mb_ereg_replace('{logUserName}', $rec[ "logUserName" ], $line );

   $text = mb_ereg_replace( '<p>', '', $rec[ "text" ]);
   $text = mb_ereg_replace( '</p>', '<br>', $text );

   $line = mb_ereg_replace('{logText}', $text, $line );

   $TitledCaches .= $line;
}

$is_titled = ( $dbc->rowCount($s)? '1' : '0' );
if ($is_titled == '0' ) $TitledCaches = '';

tpl_set_var('TitledCaches', $TitledCaches );
tpl_set_var('is_titled', $is_titled );


//make the template and send it out
tpl_BuildTemplate(false);
