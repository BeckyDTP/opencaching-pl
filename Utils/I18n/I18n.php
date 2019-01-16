<?php
namespace Utils\I18n;

use Exception;
use Utils\Text\UserInputFilter;
use Utils\Uri\OcCookie;
use Utils\Uri\Uri;
use lib\Objects\OcConfig\OcConfig;
use Utils\Debug\Debug;

class I18n
{
    const FAILOVER_LANGUAGE = 'en';

    private $currentLanguage;
    private $trArray;

    private function __construct()
    {
        $this->trArray = [];

        $initLang = $this->getInitLang();
        // check if $requestedLang is supported by node
        if (!$this->isLangSupported($initLang)) {
            // requested language is not supported - display error...
            $this->handleUnsupportedLangAndExit($initLang);
        }

        $this->setCurrentLang($initLang);
        $this->loadLangFile($initLang);
        Languages::setLocale($initLang);
    }

    /**
     * Returns instance of itself.
     *
     * @return I18n object
     */
    public static function instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static(false);
        }
        return $instance;
    }

    /**
     * Retruns current language of tranlsations
     * @return string - two-char lang code - for example: 'pl' or 'en'
     */
    public static function getCurrentLang()
    {
        // temporary use external global var
        global $lang;
        return $lang;
        // return $this->currentLanguage;
    }

    /**
     * The only function to initilize I18n for OC code.
     * This should be called at the begining of every request.
     *
     * @return \Utils\I18n\I18n
     */
    public static function init()
    {
        // just be sure that instance of this class is created
        return self::instance();
    }

    /**
     * Main translate function
     *
     * @param string $translationId - id of the phrase
     * @param string $langCode - two-letter language code
     * @param boolean $skipPostprocess - if true skip "old-template" postprocessing
     * @return string -
     */
    public static function translatePhrase($translationId, $langCode=null, $skipPostprocess=null)
    {
        return self::instance()->translate($translationId, $langCode=null, $skipPostprocess=null);
    }

    /**
     * Allow to check if given phrase is present
     *
     * @param string $str - phrase to translate
     * @return boolean
     */
    public static function isTranslationAvailable($str)
    {
        $instance = self::instance();
        $language = self::getCurrentLang();
        return isset($instance->trArray[$language][$str]) && $instance->trArray[$language][$str];
    }

    public static function getLanguagesFlagsData($currentLang=null){

        $instance = self::instance();
        $result = array();
        foreach ($instance->getSupportedTranslations() as $language) {
            if (!isset($currentLang) || $language != $currentLang) {
                $result[$language]['name'] = $language;
                $result[$language]['img'] = '/images/flags/' . $language . '.svg';
                $result[$language]['link'] = Uri::setOrReplaceParamValue('lang',$language);
            }
        }
        return $result;
    }

    /**
     * Returns language code which should be apply for current instance
     */
    private function getInitLang()
    {
        // first check if CrowdinInContext is enabled - then use pseudoLang
        CrowdinInContextMode::initHandler();
        if (CrowdinInContextMode::enabled()) {
            // CrowdinInContext mode is enabled => force loading crowdin "pseudo" lang
            return CrowdinInContextMode::getPseudoLang();
        }

        // language changed
        if (isset($_REQUEST['lang'])) {
            return $_REQUEST['lang'];
        } else {
            return OcCookie::getOrDefault('lang', $this->getDefaultLang());
        }
    }

    private function translate($str, $langCode=null, $skipPostprocess=null)
    {
        if(!$langCode){
            $langCode = self::getCurrentLang();
        }

        if (!isset($this->trArray[$langCode])) {
            $this->loadLangFile($langCode);
        }

        if (isset($this->trArray[$langCode][$str]) && $this->trArray[$langCode][$str]) {
            // requested phrase found
            if (!$skipPostprocess) {
                return $this->postProcessTr($this->trArray[$langCode][$str]);
            } else {
                return $this->trArray[$langCode][$str];
            }
        } else {
            if($langCode != self::FAILOVER_LANGUAGE){
                // there is no such phrase - try to handle it in failover language
                return $this->translate($str, self::FAILOVER_LANGUAGE, $skipPostprocess);
            }
        }

        // ups - no such phrase at all - even in failover language...
        Debug::errorLog('Unknown translation for id: '.$str);
        return "No translation available (id: $str)";
    }

    private function postProcessTr(&$ref)
    {
        if (strpos($ref, "{") !== false) {
            return tpl_do_replace($ref, true);
        } else {
            return $ref;
        }
    }

    /**
     * Load given translation file
     *
     * THIS METHOD SHOULD BE PRIVATE!
     *
     * @param string $langCode - two-letter language code
     */
    private function loadLangFile($langCode)
    {
        $languageFilename = __DIR__ . "/../../lib/languages/" . $langCode.'.php';
        if (!file_exists($languageFilename)) {
            throw new \Exception("Can't find translation file for requested language!");
            return;
        }

        // load selected language/translation file
        include ($languageFilename);
        $this->trArray[$langCode] = $translations;
    }

    private function setCurrentLang($languageCode)
    {
        // temporary use global var also
        global $lang;
        $lang = $languageCode;
        $this->currentLanguage = $languageCode;
    }

    /**
     * supported translations list is stored in i18n::$config['supportedLanguages'] var in config files
     * @return array of supported languags
     */
    private function getSupportedTranslations(){
        return OcConfig::instance()->getI18Config()['supportedLanguages'];
    }

    private function getDefaultLang()
    {
        return OcConfig::instance()->getI18Config()['defaultLang'];
    }

    private function isLangSupported($lang){

        if (CrowdinInContextMode::isSupportedInConfig()) {
            if ($lang == CrowdinInContextMode::getPseudoLang() ){
                return true;
            }
        }
        return in_array($lang, $this->getSupportedTranslations());
    }

    private function handleUnsupportedLangAndExit($requestedLang)
    {
        tpl_set_tplname('error/langNotSupported');
        $view = tpl_getView();

        $view->loadJQuery();
        $view->setVar("localCss",
            Uri::getLinkWithModificationTime('/tpl/stdstyle/error/error.css'));
        $view->setVar('requestedLang', UserInputFilter::purifyHtmlString($requestedLang));

        $this->setLang(self::FAILOVER_LANGUAGE);

        $view->setVar('allLanguageFlags', self::getLanguagesFlagsData());

        $this->loadLangFile(self::FAILOVER_LANGUAGE);

        tpl_BuildTemplate();
        exit;
    }

    // Methods for retrieving and maintaining old-style database translations.
    // This should become obsolete some time.

    // TODO: cache_atttrib

    public static function getTranslationTables()
    {
        return [
            'cache_size', 'cache_status', 'cache_type', 'log_types', 'waypoint_type',
            'countries', 'languages'
        ];
    }

    public static function getTranslationIdColumnName($table)
    {
        if ($table == 'countries' || $table == 'languages') {
            return 'short';
        } elseif ($table == 'cache_type') {
            return 'sort';  // not 'id' !
        } elseif (in_array($table, self::getTranslationTables())) {
            return 'id';
        } else {
            throw new Exception("unknown table: '".$table."'");
        }
    }

    public static function getTranslationKey($table, $id)
    {
        static $prefixes;

        if (!$prefixes) {
            $prefixes = [
                'cache_size' => 'cacheSize_',
                'cache_status' => 'cacheStatus_',
                'cache_type' => 'cacheType_',
                'countries' => '',
                'languages' => 'language_',
                'log_types' => 'logType',
                'waypoint_type' => 'wayPointType'
            ];
        }
        if (!isset($prefixes[$table])) {
            throw new Exception("unknown table: '".$table."'");
        }

        if ($table == 'cache_size') {
            static $sizeIds;
            if (!$sizeIds) {
                $sizeIds = ['other', 'micro', 'small', 'regular', 'large', 'xLarge', 'none', 'nano'];
            }
            if ($id < 1 || $id > count($sizeIds)) {
                throw new Exception('invalid size ID passed to getTranslationId(): '.$size);
            }
            $id = $sizeIds[$id - 1];
        };

        return $prefixes[$table] . $id;
    }
}
