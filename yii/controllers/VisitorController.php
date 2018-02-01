<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Visitor;
use app\models\Campaign;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;

class VisitorController extends \yii\web\Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'browserchartdata' => ['post'],
                    'visitorchartdata' => ['post'],
                    'campaignalert' => ['get'],
                ],
            ]
        ];
    }

    public function beforeAction($event) {
        $action = $event->id;

        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }
        $verb = Yii::$app->getRequest()->getMethod();

        $allowed = array_map('strtoupper', $verbs);

        if (!in_array($verb, $allowed)) {
            $this->setHeader(400);
            echo json_encode(array('status' => 0, 'error_code' => 400, 'message' => 'Method not allowed'), JSON_PRETTY_PRINT);
            exit;
        }

        return true;
    }

    protected function encrptString($jsonArray, $iv) {
        return Yii::$app->commoncomponent->encrptString($jsonArray, $iv);
    }

    private function setHeader($status) {
        Yii::$app->commoncomponent->setHeader($status);
    }

    private function _getStatusCodeMessage($status) {
        return Yii::$app->commoncomponent->_getStatusCodeMessage($status);
    }

    /**
     * Get Browser Data API.
     * @return mixed
     */
    public function actionBrowserchartdata() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $user_id = $request->user_id;
        $type = $request->type;

        $id_list = $this->getChilds($user_id);
        $id_list = array_merge(array($user_id), $id_list);

        $filters = Yii::$app->params['filterDates'];
        $filter = $request->filter;
        $days = array_search($filter, $filters);

        $remaining_days = $days - $request->days;

        $start_date = date("Y-m-d 00:00:00", strtotime($days . " days ago"));
        $end_date = date('Y-m-d 23:59:59', strtotime($remaining_days . " days ago"));

        if ($type) {
            $cnd = ['and', "type=$type", "last_visited>='$start_date'", "last_visited<='$end_date'"];
        } else {
            $cnd = [ 'and', "last_visited>='$start_date'", "last_visited<='$end_date'"];
        }

        $browserData = Visitor::find()
                ->select(['COUNT(id) as count,browser_type'])
                ->where($cnd)
                ->andWhere(["user_id" => $id_list])
                ->groupBy(['browser_type'])
                ->asArray()
                ->all();
        $i = 0;
        foreach ($browserData as $data) {
            $bdata[$i]['label'] = ucfirst($data['browser_type']);
            $bdata[$i++]['value'] = $data['count'];
        }
        $desktopData = Visitor::find()
                ->select(['COUNT(id) as count,os_type,device_type'])
                ->where($cnd)
                ->andWhere(["user_id" => $id_list])
                ->andWhere(['device_type' => 'desktop'])
                ->groupBy(['os_type'])
                ->asArray()
                ->all();
        $mobileData = Visitor::find()
                ->select(['COUNT(id) as count,os_type,device_type'])
                ->where($cnd)
                ->andWhere(["user_id" => $id_list])
                ->andWhere("device_type != 'desktop'")
                ->groupBy(['device_type'])
                ->asArray()
                ->all();
        $deviceData = array_merge($desktopData, $mobileData);
        $i = 0;
        foreach ($deviceData as $data) {
            if ($data['device_type'] == 'desktop')
                $ddata[$i]['label'] = ucwords($data['os_type'] . " " . $data['device_type']);
            else if ($data['device_type'] == 'iphone')
                $ddata[$i]['label'] = "iPhone";
            else if ($data['device_type'] == 'ipad')
                $ddata[$i]['label'] = "iPad";
            else
                $ddata[$i]['label'] = ucwords($data['device_type']);
            $ddata[$i++]['value'] = $data['count'];
        }
        $sourceData = Visitor::find()
                ->select(['id'])
                ->where($cnd)
                ->asArray()
                ->count();

        $response['browser'] = isset($bdata) ? $bdata : array(['label' => 'Browser', 'value' => 0]);
        $response['device'] = isset($ddata) ? $ddata : array(['label' => 'Device', 'value' => 0]);
        $response['source'] = $sourceData;

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $response, JSON_PRETTY_PRINT));
    }

    /**
     * Get Visitor Data API.
     * @return mixed
     */
    public function actionVisitorchartdata() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $filters = Yii::$app->params['filterDates'];
        $filter = $request->filter;

        $days = array_search($filter, $filters) - 1;
        $remaining_days = $days - $request->days;

        $id_list = $this->getChilds($request->id);
        $id_list = array_merge(array($request->id), $id_list);

        $totalVisit = array();
        $uniqueVisit = array();
        $totalCampaign = array();
        $start_date = date("Y-m-d", strtotime($days . " days ago")); //Get data from start date to current date
        if ($request->type == 0) {
            $visitors = Visitor::find()->select(['COUNT(id) AS count', "DATE_FORMAT(last_visited, '%Y-%m-%d') as date"])->where(["user_id" => $id_list])->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $start_date])->groupBy(["DATE_FORMAT(last_visited, '%Y-%m-%d')"])->asArray()->all();
        } else {
            $visitors = Visitor::find()->select(['COUNT(id) AS count', "DATE_FORMAT(last_visited, '%Y-%m-%d') as date"])->where(["user_id" => $id_list, "type" => $request->type])->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $start_date])->groupBy(["DATE_FORMAT(last_visited, '%Y-%m-%d')"])->asArray()->all();
        }
        for ($i = $days; $i >= 0; $i--) {
            if ($i == $remaining_days)
                break;
            $date = date("Y-m-d", strtotime($i . " days ago"));
            $key = array_search($date, array_column($visitors, 'date'));
            if (is_numeric($key))
                array_push($totalVisit, array($date, (int) $visitors[$key]['count']));
            else
                array_push($totalVisit, array($date, 0));
        }

        for ($i = $days; $i >= 0; $i--) {
            if ($i == $remaining_days)
                break;
            $date = date("Y-m-d", strtotime($i . " days ago"));
            $key = array_search($date, array_column($visitors, 'date'));

            if ($request->type == 0) {
                if (is_numeric($key)) {
                    $count = Visitor::find()->where(["user_id" => $id_list])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $date])->groupBy('uid')->count();
                    array_push($uniqueVisit, array($date, (int) $count));
                } else {
                    array_push($uniqueVisit, array($date, (int) 0));
                }
            } else {
                if (is_numeric($key)) {
                    $count = Visitor::find()->where(["user_id" => $id_list, "type" => $request->type])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $date])->groupBy('uid')->count();
                    array_push($uniqueVisit, array($date, (int) $count));
                } else {
                    array_push($uniqueVisit, array($date, (int) 0));
                }
            }
        }


//        if ($request->type == 0) {
//            $campaigns = Campaign::find()->select(['COUNT(id) AS count', "DATE_FORMAT(created, '%Y-%m-%d') as date"])->where(["user_id" => $request->id])->andWhere([">=", "DATE_FORMAT(created, '%Y-%m-%d')", $start_date])->groupBy(["DATE_FORMAT(created, '%Y-%m-%d')"])->count();
//        } else {
//            $campaigns = Campaign::find()->select(['COUNT(id) AS count', "DATE_FORMAT(created, '%Y-%m-%d') as date"])->where(["user_id" => $request->id, "campaign_type" => $request->type])->andWhere([">=", "DATE_FORMAT(created, '%Y-%m-%d')", $start_date])->groupBy(["DATE_FORMAT(created, '%Y-%m-%d')"])->count();
//        }


        for ($i = $days; $i >= 0; $i--) {
            if ($i == $remaining_days)
                break;
            $date = date("Y-m-d", strtotime($i . " days ago"));

            if ($request->type == 0) {
                $count = Campaign::find()->where(["user_id" => $id_list])->andWhere([ 'and', "status='1'", "end_date>='$date'", "start_date<='$date'"])->count();
            } else {
                $count = Campaign::find()->where(["user_id" => $id_list, "campaign_type" => $request->type])->andWhere(['and', "status='1'", "end_date>='$date'", "start_date<='$date'"])->count();
            }
            array_push($totalCampaign, array($date, (int) $count));
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'totalVisit' => $totalVisit, 'uniqueVisit' => $uniqueVisit, 'totalCampaign' => $totalCampaign, JSON_PRETTY_PRINT));
    }

    /**
     * Send Campaign Alert API.
     * @return mixed
     */
    public function actionCampaignalert() {
        $visitor_id = $_GET['visitor_id'];

        $visitor = Visitor::find()
                ->with('users')
                ->with('campaign')
                ->with('keys')
                ->where(['id' => $visitor_id])
                ->asArray()
                ->one();



        if ($visitor) {
            $manager_name = isset($visitor['campaign']['manager_name']) ? $visitor['campaign']['manager_name'] : '';
            $manager_email = isset($visitor['campaign']['manager_email']) ? $visitor['campaign']['manager_email'] : '';

            if ($manager_email) {

                $address = (empty($visitor['users'])) ? $visitor['address'] : (empty($visitor['users']['address'])) ? $visitor['address'] : $visitor['users']['address'];
                $city = (empty($visitor['users'])) ? $visitor['city'] : (empty($visitor['users']['city'])) ? $visitor['city'] : $visitor['users']['city'];
                $region = (empty($visitor['users'])) ? $visitor['region'] : (empty($visitor['users']['state'])) ? $visitor['region'] : $visitor['users']['state'];
                $zip = (empty($visitor['users'])) ? $visitor['zipcode'] : (empty($visitor['users']['zip'])) ? $visitor['zipcode'] : $visitor['users']['zip'];
                $country = $visitor['country'];
                $uid = (empty($visitor['users'])) ? $visitor['uid'] : (empty($visitor['users']['uid'])) ? $visitor['uid'] : $visitor['users']['uid'];

                Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($visitor['campaign']['user_id']);
                Yii::$app->mailer->compose('campaign_alert', [
                            'campaign_name' => $visitor['campaign']['name'],
                            'manager_name' => $manager_name,
                            'squibkey' => $visitor['keys']['key'],
                            'uid' => $uid,
                            'organization' => isset($visitor['users']['organization']) ? $visitor['users']['organization'] : 'N/A',
                            'name' => isset($visitor['users']['admin_name']) ? $visitor['users']['admin_name'] : 'Anonymous',
                            'isp' => (!empty($visitor['isp'])) ? $visitor['isp'] : 'N/A',
                            'location' => $address . " " . $city . " " . $region . " " . $zip . " " . $country,
                            'latlng' => $visitor['lat'] . "," . $visitor['lng'],
                        ])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo($manager_email)
                        ->setSubject('Your Campaign URL was clicked')
                        ->send();
            }
        }
        echo "Email Sent";
        exit;

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => 'Email Sent', JSON_PRETTY_PRINT));
    }

    public function getChilds($ids) {
        $result = User::find()->where(['parent_id' => $ids])->asArray()->all();
        if (!empty($result)) {
            $ids = array_column($result, 'id');
            if (!empty($ids)) {
                return array_merge($ids, $this->getChilds($ids));
            }
        } else {
            return array();
        }
    }

}
