<?php
namespace Controllers;

use Utils\Uri\Uri;
use lib\Objects\ChunkModels\DynamicMap\DynamicMapModel;
use lib\Objects\User\MultiUserQueries;
use lib\Objects\ChunkModels\DynamicMap\GuideMarkerModel;
use lib\Objects\User\User;
use Utils\Cache\OcMemCache;

class GuideController extends BaseController
{
    /** Maxiumum length of guide description passed to marker model */
    const MAX_DSCR_LEN = 100;

    public function __construct(){
        parent::__construct();
    }

    public function isCallableFromRouter($actionName)
    {
        return true;
    }

    public function index()
    {
        $this->redirectNotLoggedUsers();

        $this->view->setTemplate('guide/guides');
        $this->view->addLocalCss(
            Uri::getLinkWithModificationTime('/tpl/stdstyle/guide/guides.css'));

        $guidesList = OcMemCache::getOrCreate('currentGuides', 8*3600, function(){
            return MultiUserQueries::getCurrentGuidesList();
        });

        $this->view->setVar('guidesNumber', count($guidesList));

        $this->view->addHeaderChunk('openLayers5');
        $this->view->loadJQuery();

        $mapModel = new DynamicMapModel();
        $coords = $this->loggedUser->getHomeCoordinates();
        if (
            $coords
            && $coords->getLatitude() != null
            && $coords->getLongitude() != null
        ) {
            $mapModel->setCoords($coords);
            $mapModel->setZoom(11);
        } else {
            $mapModel->setZoom($this->ocConfig->getMainPageMapZoom());
        }

        $mapModel->addMarkersWithExtractor(GuideMarkerModel::class, $guidesList,
            function($row){
                return GuideMarkerModel::fromGuidesListRowFactory($row);
            }
        );

        $this->view->setVar('mapModel', $mapModel);


        $guideConfig = $this->ocConfig->getGuidesConfig();
        $this->view->setVar('requiredRecomCount', $guideConfig['guideGotRecommendations']);
        $this->view->setVar('requiredActivity', $guideConfig['guideActivePeriod']);

        $this->view->buildView();

    }
}
