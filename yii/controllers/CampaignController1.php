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

class CampaignController extends \yii\web\Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'getdashboarddata' => ['get'],
                    'browserchartdata' => ['post'],
                    'visitorchartdata' => ['post'],
                    'getrealtimedata' => ['get'],
                    'getrealtimedata' => ['get'],
                    'getcampaignbyname' => ['post'],
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
     * Users List API.
     * @return mixed
     */
    public function actionGetdashboarddata() {

        $campaign_name = $_GET['name'];
        $campaign = Campaign::find()->where(['slug' => $campaign_name])->asArray()->one();

        $rs['total_keys'] = $campaign['no_of_keys'];
        $query = new Query;
        $query
                ->from('visitors')
                ->where([ 'campaign_id' => $campaign['id']])
                ->select("id");
        $command = $query->createCommand();
        $visitors = $command->queryAll();

        $rs['total_visits'] = ($visitors) ? count($visitors) : 0;

        $query = new Query;
        $query
                ->from('visitors')
                ->where(['campaign_id' => $campaign['id']])
                ->groupBy(["DATE_FORMAT(last_visited, '%Y-%m-%d'),uid"])
                ->select("id");
        $command = $query->createCommand();
        $unique = $command->queryAll();

        $rs['unique_visits'] = ($unique) ? count($unique) : 0;



        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    public function actionVisitorchartdata() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $filters = Yii::$app->params['filterDates'];
        $filter = $request->filter;

        $days = array_search($filter, $filters) - 1;
        $remaining_days = $days - $request->days;

        $campaign_name = $request->campaign;
        $campaign = Campaign::find()->where(['slug' => $campaign_name])->asArray()->one();




        $totalVisit = array();
        $uniqueVisit = array();
        $totalUser = array();

        $start_date = date("Y-m-d", strtotime($days . " days ago")); //Get data from start date to current date
        $visitors = Visitor::find()->select(['COUNT(id) AS count', "DATE_FORMAT(last_visited, '%Y-%m-%d') as date"])->where(["campaign_id" => $campaign['id']])->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $start_date])->groupBy(["DATE_FORMAT(last_visited, '%Y-%m-%d')"])->asArray()->all();

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
            $count = Visitor::find()->where(["campaign_id" => $campaign['id']])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $date])->groupBy('uid')->count();
            array_push($uniqueVisit, array($date, (int) $count));
        }


        $visitors = Visitor::find()->select(['COUNT(id) AS count', "DATE_FORMAT(last_visited, '%Y-%m-%d') as date"])->where(["campaign_id" => $campaign['id']])->andWhere(["!=", "visitor_id", 0])->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $start_date])->groupBy('visitor_id')->asArray()->all();

        for ($i = $days; $i >= 0; $i--) {
            if ($i == $remaining_days)
                break;
            $date = date("Y-m-d", strtotime($i . " days ago"));
            $key = array_search($date, array_column($visitors, 'date'));
            if (is_numeric($key))
                array_push($totalUser, array($date, (int) $visitors[$key]['count']));
            else
                array_push($totalUser, array($date, 0));
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'totalVisit' => $totalVisit, 'uniqueVisit' => $uniqueVisit, 'totalUser' => $totalUser, JSON_PRETTY_PRINT));
    }

    /**
     * Users List API.
     * @return mixed
     */
    public function actionBrowserchartdata() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $campaign_name = $request->campaign;
        $campaign = Campaign::find()->where(['slug' => $campaign_name])->asArray()->one();

        $filters = Yii::$app->params['filterDates'];
        $filter = $request->filter;
        $days = array_search($filter, $filters);

        $remaining_days = $days - $request->days;

        $start_date = date("Y-m-d 00:00:00", strtotime($days . " days ago"));
        $end_date = date('Y-m-d 23:59:59', strtotime($remaining_days . " days ago"));


        $cnd = [ 'and', "campaign_id=" . $campaign['id'], "last_visited>='$start_date'", "last_visited<='$end_date'"];

        $browserData = Visitor::find()
                ->select(['COUNT(id) as count,browser_type'])
                ->where($cnd)
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
                ->andWhere(['device_type' => 'desktop'])
                ->groupBy(['os_type'])
                ->asArray()
                ->all();
        $mobileData = Visitor::find()
                ->select(['COUNT(id) as count,os_type,device_type'])
                ->where($cnd)
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

    public function actionGetrealtimedata() {
        $campaign_name = $_GET['name'];

        $campaign = Campaign::find()->where(['slug' => $campaign_name])->asArray()->one();
        $campaign_id = $campaign['id'];
        $current_time = date("Y-m-d H:i:s");
        $total_points = 102;
        if (isset($_GET['load']) && $_GET['load'] == 'init') {
            $initial_time = date("Y-m-d H:i:s", strtotime("-$total_points seconds"));
            $visitors = Visitor::find()
                    ->select(['COUNT(id) as count,last_visited'])
                    ->where(['campaign_id' => $campaign_id])
                    ->andWhere(['>=', 'last_visited', $initial_time])
                    ->groupBy(['last_visited'])
                    ->asArray()
                    ->all();

            if ($visitors) {
                $i = 0;
                for ($second = strtotime($initial_time); $second <= strtotime($current_time); $second++) {
                    $key = array_search(date("Y-m-d H:i:s", $second), array_column($visitors, 'last_visited'));
                    if (is_numeric($key))
                        $visitorsCount[$i++] = $visitors[$key]['count'];
                    else
                        $visitorsCount[$i++] = 0;
                }
            } else {
                for ($i = $total_points; $i > 0; $i--) {
                    $visitorsCount[] = 0;
                }
            }
            $this->setHeader(200);
            return json_encode(array('status' => 2, 'data' => $visitorsCount, JSON_PRETTY_PRINT));
        } else {
            $visitorCount = Visitor::find()->where(['campaign_id' => $campaign_id, 'last_visited' => $current_time])->groupBy(['last_visited'])->count();
            $this->setHeader(200);
            return json_encode(array('status' => 1, 'data' => (int) $visitorCount, JSON_PRETTY_PRINT));
        }
    }

    public function actionGetcampaignbyname() {
        $postData = file_get_contents("php://input");
        $request = json_decode($postData);
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $time = new \DateTime('now');
        $today = $time->format('Y-m-d');
        $campaign = Campaign::find()
                ->with('user')
                ->where(['slug' => $request->name])
                ->andWhere(['>=', 'end_date', $today])
                ->asArray()
                ->one();

//        echo $uid = Yii::$app->commoncomponent->slugUnique($campaign['name'], 'campaign', 'slug');
//
//        print_r($campaign);
//        exit;

        if (!empty($campaign)) {
            $campaign_keys = (new Query())
                    ->from('campaign_keys')
                    ->where(['campaign_id' => $campaign['id']])
                    ->count();
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($campaign['user_id']);
            if ($request->action == 'cancel') {
                if ($campaign_keys == 0) {

                    Yii::$app->mailer->compose('cancel_campaign', [
                                'campaign_name' => $campaign['name'],
                                'start_date' => date('M d, Y', strtotime($campaign['start_date'])),
                                'end_date' => date('M d, Y', strtotime($campaign['end_date'])),
                                'username' => $campaign['user']['admin_name'],
                            ])
                            ->setFrom(Yii::$app->params['adminEmail'])
                            ->setTo($campaign['user']['email_id'])
                            ->setSubject('Your campaign has been canceled.')
                            ->send();
                } else {
                    $status = 0;
                    $response_code = 200;
                    $message = "Campaign keys already confirmed.";
                }
            } elseif (($request->action == 'confirm' && $campaign_keys == 0) || ($campaign['on_the_fly'] == 1 && $campaign_keys == 0)) {
//                if ($campaign['key_generate_type'] == "sequential") {
//                    for ($i = 1; $i <= $campaign['no_of_keys']; $i++) {
//                        $insert = array();
//                        $insert['campaign_id'] = $campaign['id'];
//                        $insert['key'] = $campaign['key_start_no'];
//                        $campaign['key_start_no'] += 1;
//                        Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
//                    }
//                } else {
//                    for ($i = 1; $i <= $campaign['no_of_keys']; $i++) {
//                        $insert = array();
//                        $insert = array();
//                        $insert['campaign_id'] = $campaign['id'];
//                        $randomString = Yii::$app->commoncomponent->generateRandomString(8, $campaign['id']);
//                        $insert['key'] = $randomString;
//                        Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
//                    }
//                }

                if ($campaign['key_generate_type'] == "sequential") {
                    $rows = array();
                    for ($i = 1; $i <= $campaign['no_of_keys']; $i++) {
                        $rows[$i]['campaign_id'] = $campaign['id'];
                        $rows[$i]['key'] = $campaign['key_start_no'];
                        $campaign['key_start_no'] += 1;
                        // Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                    }
                    Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                } else {

                    $rows = array();
                    for ($i = 1; $i <= $campaign['no_of_keys']; $i++) {
                        $rows[$i]['campaign_id'] = $campaign['id'];
                        //$randomString = Yii::$app->commoncomponent->generateRandomString(8, $campaign['id']);
                        $rows[$i]['key'] = uniqid();
                        //Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                    }

                    Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                }
                Yii::$app->mailer->compose('confirm_campaign', [
                            'campaign_name' => $campaign['name'],
                            'start_date' => date('M d, Y', strtotime($campaign['start_date'])),
                            'end_date' => date('M d, Y', strtotime($campaign['end_date'])),
                            'username' => $campaign['user']['admin_name'],
                            'no_of_keys' => $campaign['no_of_keys'],
                        ])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo($campaign['user']['email_id'])
                        ->setSubject('Your campaign has been confirmed.')
                        ->send();

                $status = 1;
                $response_code = 200;
                $message = "Campaign keys successfully generated.";
            } else {
                $status = 0;
                $response_code = 200;
                $message = "Campaign keys already generated.";
            }
        } else {
            $status = 0;
            $response_code = 200;
            $message = "Campaign Expires";
        }
        $this->setHeader(200);
        return json_encode(array('status' => $status, 'code' => $response_code, 'message' => $message), JSON_PRETTY_PRINT);
    }

    function generateRandomString($length = 8, $id = 0) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $randomString = $id . $randomString;
        $res = CampaignKeys::find()->where(['key' => $randomString])->asArray()->one();
        if (!empty($res)) {
            $this->generateRandomString(8, $id);
        }
        return $randomString;
    }

}
