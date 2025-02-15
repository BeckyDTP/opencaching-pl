<?php

namespace src\Controllers;

use src\Models\ApplicationContainer;
use src\Models\User\User;
use src\Models\OcConfig\OcConfig;
use src\Utils\View\View;
use src\Utils\Uri\Uri;

require_once(__DIR__.'/../../lib/common.inc.php');

abstract class BaseController
{
    const HTTP_STATUS_OK = 200;

    const HTTP_STATUS_BAD_REQUEST = 400;
    const HTTP_STATUS_FORBIDEN = 403;
    const HTTP_STATUS_NOT_FOUND = 404;
    const HTTP_STATUS_CONFLICT = 409;

    const HTTP_STATUS_INTERNAL_ERROR = 500;

    /**
     * Every ctrl should have index method
     * which is called by router as a default action
     */
    abstract public function index();

    /**
     * This method is called by router to be sure that given action is allowed
     * to be called by router (it is possible that ctrl has public method which
     * shouldn't be accessible on request).
     *
     * @param string $actionName - method which router will call
     * @return boolean - TRUE if given method can be call from router
     */
    abstract public function isCallableFromRouter($actionName);

    /** @var View $view */
    protected $view = null;

    /** @var ApplicationContainer $applicationContainer */
    protected $applicationContainer = null;

    /** @var User */
    protected $loggedUser = null;

    /** @var OcConfig $ocConfig */
    protected $ocConfig = null;

    protected function __construct()
    {
        $this->view = tpl_getView();

        $this->applicationContainer = ApplicationContainer::Instance();
        $this->loggedUser = $this->applicationContainer->getLoggedUser();
        $this->ocConfig = $this->applicationContainer->getOcConfig();

        // there is no DB access init - DB operations should be performed in models/objects
    }


    protected function redirectToLoginPage()
    {
        $this->view->redirect(
            Uri::setOrReplaceParamValue('target', Uri::getCurrentUri(), '/login.php'));
        exit();
    }

    protected function isUserLogged()
    {
        return !is_null($this->loggedUser);
    }

    protected function ajaxJsonResponse($response, $statusCode=null)
    {
        if(is_null($statusCode)){
            $statusCode = self::HTTP_STATUS_OK;
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        print (json_encode($response));
        exit;
    }
    protected function ajaxSuccessResponse($message=null, array $additionalResponseData=null){
        $response = [
            'status' => 'OK'
        ];

        if(!is_null($message)){
            $response['message'] = $message;
        }

        if(is_array($additionalResponseData)){
            $response = array_merge($additionalResponseData, $response);
        }

        $this->ajaxJsonResponse($response);
    }
    protected function ajaxErrorResponse($message=null, $statusCode=null, array $additionalResponseData=null){
        $response = [
            'status' => 'ERROR'
        ];
        if(!is_null($message)){
            $response['message'] = $message;
        }
        if(is_null($statusCode)){
            $statusCode = self::HTTP_STATUS_BAD_REQUEST;
        }
        if(is_array($additionalResponseData)){
            $response = array_merge($additionalResponseData, $response);
        }
        $this->ajaxJsonResponse($response, $statusCode);
    }

    /**
     * This method can be used to just exit and display error page to user
     *
     * @param string $message - simple message to be displayed (in english)
     * @param integer $httpStatusCode - http status code to return in response
     */
    public function displayCommonErrorPageAndExit($message = null, $httpStatusCode = null)
    {
        $this->view->setTemplate('error/commonFatalError');
        if ($httpStatusCode) {
            switch ($httpStatusCode) {
                case 404:
                    header("HTTP/1.0 404 Not Found");
                    break;
                case 403:
                    header("HTTP/1.0 403 Forbidden");
                    break;
                default:
                    //TODO...
            }
        }

        $this->view->setVar('message', $message);
        $this->view->buildOnlySelectedTpl();
        exit();
    }

    /**
     * Simple redirect not logged users to login page
     */
    protected function redirectNotLoggedUsers()
    {
        if (! $this->isUserLogged()) {
            $this->redirectToLoginPage();
            exit();
        }
    }

    /**
     * Check if user is logged. If not - generates 401 AJAX response
     */
    protected function checkUserLoggedAjax()
    {
        if (! $this->isUserLogged()) {
            $this->ajaxErrorResponse('User not logged', 401);
            exit();
        }
    }

}
