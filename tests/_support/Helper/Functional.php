<?php
namespace Helper;

use Yii;
// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module
{
    /**
     * Define custom actions here
     */

    public function requestPage($verb = 'POST', $page, $bodyParams = [])
    {
        if (is_array($page)) {
            $page = Yii::$app->getUrlManager()->createUrl($page);
        }

        $responseData = $this->getModule('Yii2')->_request($verb, $page, $bodyParams);
        return $responseData;
    }

    public function requestRoute($verb = 'POST', $route, array $params = [], $bodyParams = [])
    {
        array_unshift($params, $route);
        return $this->requestPage($verb, $params, $bodyParams);
    }
}
