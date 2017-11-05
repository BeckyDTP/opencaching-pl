<?php

/**
 * This is simple configuration of links presented in sidebar of the page
 * for authorized users only.
 *
 * This is a DEFAULT configuration for ALL nodes.
 *
 * If you want to customize footer for your node
 * create config for your node by copied this file and changing its name.
 *
 * Every record of $menu table should be table record in form:
 *
 *  '<translation-key-used-as-link-text>', '<url>',
 *
 */

// DON'T CHANGE $menu var name!
$menu = [
    /* 'translation key' => 'url' */
    'news'          => '/news.php',
    'new_caches'    => '/newcaches.php',
    'new_logs'      => '/newlogs.php',
    'incomming_events' => '/newevents.php',
    'cache_map'     => '/cachemap3.php',
    'cache_mapv2'   => '/cachemap2.php', //PL only!
    'Flopp_map'     => 'http://www.flopp-caching.de',
    'logmap_04'     => '/logmap.php',
    'search_cache'  => '/search.php',
    'recommended_caches' => '/cacheratings.php',
    'statistics'    => '/articles.php?page=stat',
    'rules'         => $links['wiki']['rules'],
    'gp_mainTitile' => '/powerTrail.php',
    'new_cache' => '/newcache.php',
    'my_caches' => '/mycaches.php',
    'my_neighborhood' => '/myneighborhood.php',
    'myroutes' => '/myroutes.php',
    'mycache_note' => '/mycache_notes.php',
    'my_statistics' => '/viewprofile.php',
    'Field_Notes' => '/log_cache_multi_send.php',
    'my_account' => '/myprofile.php',
    'settings_notifications' => 'mywatches.php?action=emailSettings',
    'collected_queries' => 'query.php',
    'watched_caches' => 'mywatches.php',
    'map_watched_caches' =>  'mywatches.php?action=map',
    'ignored_caches' => 'myignores.php',
    'my_recommendations' => 'mytop5.php',
    'adoption_cache' => 'chowner.php',
    'search_user' => 'searchuser.php',
    'openchecker_name' => 'openchecker.php', //ENABLE?!
    'okapi_apps' => 'okapi/apps/?langpref=' . $GLOBALS['lang'],
    'mnu_qrCode' => '/qrcode.php',
];
