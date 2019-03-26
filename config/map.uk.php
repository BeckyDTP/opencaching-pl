<?php

/**
 * $map['jsConfig'] constains JS code with configuration for openlayers
 * Be sure that all changes here are well tested.
 */
$map['jsConfig'] = "
{
  OSM: new ol.layer.Tile ({
    source: new ol.source.OSM(),
  }),

  BingMap: new ol.layer.Tile ({
    source: new ol.source.BingMaps({
      key: '{Key-BingMap}',
      imagerySet: 'Road'
    })
  }),

  BingSatelite: new ol.layer.Tile ({
    source: new ol.source.BingMaps({
      key: '{Key-BingMap}',
      imagerySet: 'Aerial'
    })
  }),

}
";

/**
 * Bing map key is set in node-local config file
 */
$map['keys']['BingMap'] = 'NEEDS-TO-BE-SET-IN-LOCAL-CONFIG-FILE';


/**
 * This is function which is called to inject keys from "local" config
 * to default node-configurations map configs.
 *
 * Here this is only a simple stub.
 *
 * @param array complete configureation merged from default + node-default + local configs
 * @return true on success
 */
$map['keyInjectionCallback'] = function(array &$mapConfig){

    // change string {Key-BingMap} to proper key value

    $mapConfig['jsConfig'] = str_replace(
        '{Key-BingMap}',
        $mapConfig['keys']['BingMap'],
        $mapConfig['jsConfig']);

    return true;
};

/**
 * Coordinates of the default map center - used by default by many maps in service
 * Format: float number
 */
$map['mapDefaultCenterLat'] = 54.1;
$map['mapDefaultCenterLon'] = -4.0;
$map['mapDefaultZoom'] = 5;

/**
 * Zoom of the static map from startPage
 */
$map['startPageMapZoom'] = 4;

/**
 * Dimensions of the static map from startPage[width,height]
 */
$map['startPageMapDimensions'] = [200, 240];
