<?php
namespace src\Models\OcConfig;

use src\Models\Coordinates\Coordinates;

/**
 * This trait group access to email settings stored in /config/email.* conf. files
 * BEWARE OF FUNCTIONS NAME COLLISION BETWEEN CONFIG TRAITS!
 */
trait MapConfigTrait {

    protected $mapConfig = null;

    /**
     * Returns key value from map keys config or null if there is no such key
     *
     * @return string|null
     */
    public static function getMapKey($keyName)
    {
        $keys = self::getMapVar('keys');
        if(is_array($keys) && isset($keys[$keyName]) && !empty($keys[$keyName])){
            return $keys[$keyName];
        }
        return null;
    }

    /**
     * Returns JS configuration of map layers
     *
     * @return string (JSON)
     */
    public static function getMapJsConfig()
    {
        $jsConfig = self::getMapVar('jsConfig');
        $keyInjectorFunc = self::getMapVar('keyInjectionCallback');

        if(!is_callable($keyInjectorFunc)) {
            throw new \Exception("Wrong keyInjectionCallback config value!");
        }

        if ( !$keyInjectorFunc($jsConfig) ) {
            throw new \Exception('MapConfig key injector init failed!');
        }
        return $jsConfig;
    }

    /**
     * Returns nodeId from config
     * Possible values: @see https://wiki.opencaching.eu/index.php?title=Node_IDs
     *
     * @return Coordinates
     */
    public static function getMapDefaultCenter()
    {
        $lat = self::getMapVar('mapDefaultCenterLat');
        $lon = self::getMapVar('mapDefaultCenterLon');

        return Coordinates::FromCoordsFactory($lat, $lon);
    }

    public static function getStartPageMapDiemnsions()
    {
        return self::getMapVar('startPageMapDimensions');
    }

    public static function getStartPageMapZoom()
    {
        return self::getMapVar('startPageMapZoom');
    }


    /**
     * Returns map properties
     *
     * @return array map properties
     */
    protected function getMapConfig()
    {
        if (!$this->mapConfig) {
            $this->mapConfig = self::getConfig("map", "map");
        }
        return $this->mapConfig;
    }

    /**
     * Get Var from map.* files
     *
     * @param string $varName
     * @throws \Exception
     * @return string|array
     */
    private static function getMapVar($varName)
    {
        $mapConfig = self::instance()->getMapConfig();
        if (!is_array($mapConfig)) {
            throw new \Exception("Invalid $varName setting: see /config/map.*");
        }
        return $mapConfig[$varName];
    }
}
