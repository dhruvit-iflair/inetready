<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Visitor;
use app\models\Campaign;
use app\models\InstanceFiles;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;

/**
 * CityController implements the CRUD actions for City model.
 */
class UserController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'users' => ['get'],
                    'userall' => ['get'],
                    'getmaincookie' => ['get'],
                    'getcookie' => ['get'],
                    'getaccesstoken' => ['get'],
                    'getsubdomain' => ['post'],
                    'getusersbyrole' => ['get'],
                    'getusermeta' => ['get'],
                    'getprofile' => ['post'],
                    'getuser' => ['post'],
                    'updateuser' => ['post'],
                    'uploadcoverphoto' => ['post'],
                    'createuser' => ['post'],
                    'deleteuser' => ['get'],
                    'cloudinstancelist' => ['get'],
                    'searchcloudinstance' => ['post'],
                    'cloudinstancesortasc' => ['get'],
                    'cloudinstancesortdesc' => ['get'],
                    'signup' => ['post'],
                    'confirmation' => ['post'],
                    'masterfiles' => ['post'],
                    'getfiles' => ['post'],
                    'filesupload' => ['post'],
                    'masterfilelist' => ['post'],
                    'imagedelete' => ['get'],
                    'removefolder' => ['get'],
                    'sendfiles' => ['post'],
                    'sendfilesmultiple' => ['post'],
                    'getchilduser' => ['get'],
                    'getchildusers' => ['get'],
                    'getuserdrive' => ['get'],
                    'cloudinstancefiles' => ['post'],
                    'cloudfilelist' => ['post'],
                    'downloadfile' => ['get'],
                    'sharedfiles' => ['post'],
                    'setcookie' => ['get'],
                    'getdashboarddata' => ['get'],
                    'getcampaignvisitor' => ['get'],
                    'addfolder' => ['post'],
                    'getdashboardvisits' => ['get'],
                    'downloadzip' => ['get'],
                    'downloadnow' => ['get'],
                    'removezip' => ['get'],
                    'getdrive' => ['post'],
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

    /* creates a compressed zip file */

    private function create_zip($files = array(), $destination = '', $overwrite = false) {
        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) {
            return false;
        }
        //vars
        $valid_files = array();
        //if files were passed in...
        if (is_array($files)) {
            //cycle through each file
            foreach ($files as $file) {
                //make sure the file exists
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }
        //if we have good files...
        if (count($valid_files)) {
            //create the archive
            $zip = new \ZipArchive();
            if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //add the files
            foreach ($valid_files as $file) {
                $zip->addFile($file, basename($file));
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            //close the zip -- done!
            $zip->close();

            //check to make sure the file exists
            return file_exists($destination);
        } else {
            return false;
        }
    }

    /**
     * Users List API.
     * @return mixed
     */
    public function actionUsers() {

        $user_id = $_GET['user_id'];

        $user = User::findOne([
                    'id' => $user_id,
        ]);
        if ($user->role == 'admin') {
            $users = User::find()
                    ->orderBy('id DESC')
                    ->limit(50)
                    ->all();
        } else {

            $usersID = User::find()
                    ->orderBy('id DESC')
                    ->limit(50)
                    ->select('id')
                    ->where(['parent_id' => $user->id])
                    ->all();
            if ($user->role == 'reseller') {
                foreach ($usersID as $client) {
                    $usersID = array_merge($usersID, User::find()
                                    ->orderBy('id DESC')
                                    ->limit(50)
                                    ->select('id')
                                    ->where(['parent_id' => $client->id])
                                    ->all());
                }
            }
            if ($usersID) {
                foreach ($usersID as $userid) {
                    $user_ids[] = $userid->id;
                }
            } else {
                $user_ids = 0;
            }
// print_r($user_ids);exit;
            $users = User::find()
                    ->orderBy('id DESC')
                    ->limit(50)
                    ->andFilterWhere(['IN', 'id', $user_ids])
// ->where(['parent_id' => $user->id])
                    ->all();
        }


        $rs = array();
        $i = 0;
        foreach ($users as $user) {
            switch ($user->attributes["user_status"]) {
                case 0:
                    $status = 'Enabled';
                    break;
                case 1:
                    $status = 'Disabled';
                    break;
                case 2:
                    $status = 'Suspended';
                    break;
                case 3:
                    $status = 'Archived';
                    break;
            }
            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $user->profile_image;
            $rs[$i]['index'] = $i;
            $rs[$i]['profile_image'] = $user->profile_image ? $img_path : "";
            $rs[$i]['Name'] = $user->attributes['admin_name'];
            $rs[$i]['Emailid'] = $user->attributes['email_id'];
            $rs[$i]['Adminid'] = $user->attributes['id'];
            $rs[$i]['Role'] = ucfirst($user->attributes['role']);
            $rs[$i]['instance_name'] = $user->attributes['instance_name'];
            $rs[$i++]['Status'] = $status;
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Users List API.
     * @return mixed
     */
    public function actionUserall() {

        $user_id = $_GET['user_id'];

        $user = User::findOne([
                    'id' => $user_id,
        ]);
        if ($user->role == 'admin') {
            $users = User::find()
                    ->all();
        } else {

            $usersID = User::find()
                    ->select('id')
                    ->where(['parent_id' => $user->id])
                    ->all();
            if ($user->role == 'reseller') {
                foreach ($usersID as $client) {
                    $usersID = array_merge($usersID, User::find()
                                    ->select('id')
                                    ->where(['parent_id' => $client->id])
                                    ->all());
                }
            }
            if ($usersID) {
                foreach ($usersID as $userid) {
                    $user_ids[] = $userid->id;
                }
            } else {
                $user_ids = 0;
            }
            $users = User::find()
                    ->andFilterWhere(['IN', 'id', $user_ids])
                    ->all();
        }
        $rs = array();
        $i = 0;
        if (isset($users)) {
            foreach ($users as $user) {
                switch ($user->attributes["user_status"]) {
                    case 0:
                        $status = 'Enabled';
                        break;
                    case 1:
                        $status = 'Disabled';
                        break;
                    case 2:
                        $status = 'Suspended';
                        break;
                    case 3:
                        $status = 'Archived';
                        break;
                }

                $img_url = Yii::getAlias('@web') . "/uploads/users/";
                $img_path = $img_url . $user->profile_image;
                $rs[$i]['index'] = $i;
                $rs[$i]['profile_image'] = $user->profile_image ? $img_path : "";
                $rs[$i]['Name'] = $user->attributes['admin_name'];
                $rs[$i]['Emailid'] = $user->attributes['email_id'];
                $rs[$i]['Adminid'] = $user->attributes['id'];
                $rs[$i]['Role'] = ucfirst($user->attributes['role']);
                $rs[$i]['instance_name'] = $user->attributes['instance_name'];
                $rs[$i]['instance_url'] = $user->attributes['instance_url'];
                $rs[$i++]['Status'] = $status;
            }
        }
//        $data = array(
//            array('Name' => 'parvez', 'Empid' => 11, 'Salary' => 101),
//            array('Name' => 'alam', 'Empid' => 1, 'Salary' => 102),
//            array('Name' => 'phpflow', 'Empid' => 21, 'Salary' => 103));


        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($rs),
            "iTotalDisplayRecords" => count($rs),
            "aaData" => $rs);
        /* while($row = $result->fetch_array(MYSQLI_ASSOC)){
          $results["data"][] = $row ;
          } */

        echo json_encode($results);
    }

    /**
     * Get Main Domain Cookie API.
     * @return mixed
     */
    public function actionGetmaincookie() {
       // $this->layout = 'ajax';
        $cookie_name = 'globals';
        $access_token = $_GET['access_token'];
        if (isset($_COOKIE[$cookie_name])) {

            $response = $_COOKIE[$cookie_name];

            $query = new Query;
            $query
                    ->from('access_token')
                    ->where(['access_token' => $access_token])
                    ->orderBy('id DESC')
                    ->select("cookie,id");

            $command = $query->createCommand();
            $cookie = $command->queryOne();
            if ($cookie) {
                $id = $cookie['id'];
                $update['access_token'] = $access_token;
                $update['cookie'] = $response;
                Yii::$app->db->createCommand()->update('access_token', $update, "id = $id")->execute();
            } else {
                $create['access_token'] = $access_token;
                $create['cookie'] = $response;
                Yii::$app->db->createCommand()->insert('access_token', $create)->execute();
            }

//            return $this->render('getmaincookie', [
//                        'response' => $response
//            ]);
        } else {
            Yii::$app->db->createCommand()->delete('access_token', 'access_token = "' . $access_token . '"')->execute();
        }
        //exit;
    }

    /**
     * Get Get Cookie API.
     * @return mixed
     */
    public function actionGetcookie() {

        $access_token = $_GET['access_token'];
        $query = new Query;
        $query
                ->from('access_token')
                ->where(['access_token' => $access_token])
                ->orderBy('id DESC')
                ->select("cookie");

        $command = $query->createCommand();
        $cookie = $command->queryOne();

        if ($cookie) {
            $status = 1;
            $response = $cookie;
        } else {
            $status = 0;
            $response = 'not login';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get Access Token API.
     * @return mixed
     */
    public function actionGetaccesstoken() {
        Yii::$app->session->open();
        $access_token = Yii::$app->session->getId();
        $this->setHeader(200);
        echo json_encode(array('status' => 1, 'data' => $access_token), JSON_PRETTY_PRINT);
    }

    /**
     * Get Subdomain API.
     * @return mixed
     */
    public function actionGetsubdomain() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);


        $instance_url = $request->instance_url;
        $pieces = parse_url($instance_url);
        if ($pieces['host']) {
            $parts = explode(".", $pieces['host']);
            if ($parts[0] == 'www') {
                $wwwUrl = $pieces['scheme'] . "://" . $pieces['host'];
                $nonwwwUrl = $pieces['scheme'] . "://" . $parts[1] . "." . $parts[2];
                $user = User::find()
                        ->where(['instance_url' => $wwwUrl])
                        ->orWhere(['instance_url' => $nonwwwUrl])
                        ->orWhere(['default_url' => $instance_url])
                        ->one();
            } else {
                $nonwwwUrl = $pieces['scheme'] . "://" . $pieces['host'];
                $user = User::find()
                        ->where(['instance_url' => $nonwwwUrl])
                        ->orWhere(['default_url' => $instance_url])
                        ->one();
            }
        }
        $visitor_id = $request->visitor_id;

        if ($user) {
            $query = new Query;
            $query
                    ->from('user_social_networks')
                    ->where(['user_id' => $user->id])
                    ->join('JOIN', 'social_networks', $on = 'user_social_networks.social_network_id = social_networks.id ')
                    ->select("user_social_networks.*,social_networks.name,social_networks.class_name,social_networks.fa");

            $command = $query->createCommand();
            $models = $command->queryAll();
            $response = $user->attributes;
            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $user->profile_image;
            $response['profile_image'] = $user->profile_image ? $img_path : "";
            $response['cover_photo'] = $user->cover_photo ? $img_url . $user->cover_photo : "";

            Yii::$app->session->open();
            $access_token = Yii::$app->session->getId();
            $response['access_token'] = $access_token;


            $response['likes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 1, 'profile_id' => $user->id])
                    ->count();

            $response['dislikes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 0, 'profile_id' => $user->id])
                    ->count();

            $response['visits'] = (new yii\db\Query())
                    ->from('visitors')
                    ->where(['user_id' => $user->id])
                    ->count();

            $response['followers'] = 0;

            $query = (new yii\db\Query())
                    ->from('comment')
                    ->where(['profile_id' => $user->id])
                    ->orderBy('comment.id DESC')
                    ->join('JOIN', 'user', $on = 'comment.user_id = user.id ')
                    ->select("comment.id,comment.user_id,comment.comment,user.admin_name,comment.created,user.profile_image");
            $command = $query->createCommand();
            $comments = $command->queryAll();
            $rs = array();
            $i = 0;
            foreach ($comments as $comment) {

                $img_url = Yii::getAlias('@web') . "/uploads/users/";
                $img_path = $img_url . $comment['profile_image'];

                $rs[$i]['id'] = $comment['id'];
                $rs[$i]['user_id'] = $comment['user_id'];
                $rs[$i]['profile_image'] = $comment['profile_image'] ? $img_path : "";
                $rs[$i]['name'] = $comment['admin_name'];
                $rs[$i]['comment'] = $comment['comment'];
                $rs[$i++]['time'] = date('F d, Y h:i A', strtotime($comment['created']));
            }
            $response['comments'] = $rs;
            $response['user_social_network'] = $models;
            if ($visitor_id) {
                $query = new Query;
                $query
                        ->from('follower')
                        ->where(['following_id' => $user->id, 'follower_id' => $visitor_id])
                        ->select("status");

                $command = $query->createCommand();
                $follow = $command->queryOne();

                $response['follow_status'] = $follow ? $follow['status'] : 0;
            } else {
                $response['follow_status'] = 0;
            }
        } else {
            $response = '';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get Users by Role API.
     * @return mixed
     */
    public function actionGetusersbyrole() {
        $role = $_GET ['role'];
        $cnd = array('role' => $role);
        if (isset($_GET ['parent_id'])) {
            $cnd['parent_id'] = $_GET ['parent_id'];
        }

        $users = User::find()->where($cnd)->all();
        $rs = array();
        $i = 0;
        foreach ($users as $user) {
            $rs[$i]['id'] = $user->attributes['id'];
            $rs[$i]['vanity_domain'] = $user->attributes['instance_url'];
            $rs[$i++]['name'] = $user->attributes['admin_name'];
        }

        $this->setHeader(200);

//echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Get Subdomain API.
     * @return mixed
     */
    public function actionGetusermeta() {

        $user_id = $_GET['user_id'];

        $user = User::findOne([
                    'id' => $user_id,
        ]);

        if ($user) {
            $url = "http://dev.squibdrive.net/profile/" . $user_id . "/";
            $title = $user->admin_name;
            $description = $user->about_me;

            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $user->profile_image;
            $image = $user->profile_image ? "http://dev.squibdrive.net" . $img_path : "http://dev.squibdrive.net/assets/img/logo-solo.png";
            echo $content = '<!DOCTYPE html>
                        <html>
                        <head>
                            <!--meta property="og:url" content="' . $url . '" /-->
                            <meta property="og:title" content="' . $title . '" />
                            <meta property="og:description" content="' . $description . '" />
                                <meta property="og:image" content="' . $image . '"/>
                                    <meta property="og:image:width" content="300" /> 
<meta property="og:image:height" content="300" />
<meta property="og:image:type" content="image/png" />
                                </head>
                        <body>
                            <p>"' . $description . '"</p>
                                <img src="' . $image . '">
                                    </body>
                        </html>';
            exit;
        } else {
            $response = '';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get Subdomain API.
     * @return mixed
     */
    public function actionGetprofile() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $user_id = $request->user_id;
        $visitor_id = $request->visitor_id;

        $user = User::findOne([
                    'id' => $user_id,
        ]);

        if ($user) {
// print_r($user);exit;
            $query = new Query;
            $query
                    ->from('user_social_networks')
                    ->where(['user_id' => $user->id])
                    ->join('JOIN', 'social_networks', $on = 'user_social_networks.social_network_id = social_networks.id ')
                    ->select("user_social_networks.*,social_networks.name,social_networks.class_name,social_networks.fa");

            $command = $query->createCommand();
            $models = $command->queryAll();

            $response = $user->attributes;
            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $user->profile_image;
            $response['profile_image'] = $user->profile_image ? $img_path : "";

            $response['likes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 1, 'profile_id' => $user->id])
                    ->count();

            $response['dislikes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 0, 'profile_id' => $user->id])
                    ->count();

            $response['visits'] = (new yii\db\Query())
                    ->from('visitors')
                    ->where(['user_id' => $user->id])
                    ->count();

            $response['followers'] = (new yii\db\Query())
                    ->from('follower')
                    ->where(['following_id' => $user->id, 'status' => 1])
                    ->count();

            $query = (new yii\db\Query())
                    ->from('comment')
                    ->where(['profile_id' => $user->id])
                    ->orderBy('comment.id DESC')
                    ->join('JOIN', 'user', $on = 'comment.user_id = user.id ')
                    ->select("comment.comment,user.admin_name,comment.created,user.profile_image");
            $command = $query->createCommand();
            $comments = $command->queryAll();
            $rs = array();
            $i = 0;
            foreach ($comments as $comment) {

                $img_url = Yii::getAlias('@web') . "/uploads/users/";
                $img_path = $img_url . $comment['profile_image'];
                $rs[$i]['profile_image'] = $comment['profile_image'] ? $img_path : "";
                $rs[$i]['name'] = $comment['admin_name'];
                $rs[$i]['comment'] = $comment['comment'];
                $rs[$i++]['time'] = date('F d, Y h:i A', strtotime($comment['created']));
            }
            $response['comments'] = $rs;
            $response['user_social_network'] = $models;

            if ($visitor_id) {
                $query = new Query;
                $query
                        ->from('follower')
                        ->where(['following_id' => $user->id, 'follower_id' => $visitor_id])
                        ->select("status");

                $command = $query->createCommand();
                $follow = $command->queryOne();

                $response['follow_status'] = $follow ? $follow['status'] : 0;
            } else {
                $response['follow_status'] = 0;
            }
        } else {
            $response = '';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get User API.
     * @return mixed
     */
    public function actionGetuser() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $user_id = $request->user_id;

        $user = User::findOne([
                    'id' => $user_id,
        ]);

        if ($user) {
// print_r($user);exit;
            $query = new Query;
            $query
                    ->from('user_social_networks')
                    ->where(['user_id' => $user_id])
                    ->join('JOIN', 'social_networks', $on = 'user_social_networks.social_network_id = social_networks.id ')
                    ->select("user_social_networks.*,social_networks.name");

            $command = $query->createCommand();
            $models = $command->queryAll();
            $response = $user->attributes;
            if ($user->role == 'client') {
                $response['reseller_id'] = $user->parent_id;
            } else if ($user->role == 'user') {
                $response['client_id'] = $user->parent_id;
                $parent = User::findOne([
                            'id' => $user->parent_id,
                ]);
                $response['reseller_id'] = $parent->parent_id;
            }

            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $user->profile_image;

            $response['profile_image'] = $user->profile_image ? $img_path : "";


            $response['cover_photo'] = $user->cover_photo ? $img_url . $user->cover_photo : "";
            $response['age'] = date_diff(date_create($user->dob), date_create('today'))->y;

            $query = new Query;
            $query
                    ->from('user_permissions')
                    ->where(['user_id' => $user_id])
                    ->andWhere(['module_id' => 5])
                    ->select("status");

            $command = $query->createCommand();
            $squibcard = $command->queryAll();


            $query = new Query;
            $query
                    ->from('squibcard')
                    ->where(['id' => 3])
                    ->select("id,status");

            $command = $query->createCommand();
            $squibcardStatus = $command->queryOne();

            $response['squib_movie'] = $squibcardStatus['status'] ? true : false;

//            if ($squibcard) {
//                $response['squibcard'] = ($squibcard[0]['status']) ? true : false;
//            } else {
//                $query = new Query;
//                $query
//                        ->from('module_permissions')
//                        ->where(['role' => $user->role])
//                        ->andWhere(['module_id' => 5])
//                        ->select("status");
//
//                $command = $query->createCommand();
//                $squibcard = $command->queryAll();
//                $response['squibcard'] = ($squibcard[0]['status']) ? true : false;
//            }
            $response['squibcard'] = $user->squibcard ? true : false;
            $response['user_social_network'] = $models;
        } else {
            $response = '';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Edit User API.
     * @return mixed
     */
    public function actionUpdateuser() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $id = $request->id;
        $passwd = $request->passwd;
        $user = User::findOne($id);
        $old_instance_url = $user->instance_url;
        $old_instance_name = $user->instance_name;

        $user->admin_name = $request->admin_name;
        $user->email_id = $request->email_id;
        $user->user_status = $request->user_status;
        $user->role = $request->role;
        $user->instance_name = isset($request->instance_name) ? $request->instance_name : "";
        $user->url_type = isset($request->url_type) ? $request->url_type : "";
        $user->instance_url = isset($request->instance_url) ? $request->instance_url : "";
        $user->organization = $request->organization;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->ip_address = $request->ip_address;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->country_code = $request->country_code;
        $user->squibkey_id = $request->squibkey_id;
        $user->squibcard_id = $request->squibcard_id;
        $user->sex = $request->sex;
        $user->dob = date("Y-m-d", strtotime($request->dob));
        $user->website = $request->website;
        $user->mobile_phone = $request->mobile_phone;
        $user->work_phone = $request->work_phone;
        $user->about_me = $request->about_me;
        $user->video_code = $request->video_code;
        $user->video_from = $request->video_from;
        $user->modified = date("Y-m-d H:i:s");
        $user->squibcard = $request->squibcard ? 1 : 0;
// adding iframe according to url
        $iframe = '';
        if ($request->video_code != '' && in_array($request->video_from, array(1, 2))) {

            $textDescription = $request->video_code;
            $parsed = parse_url($textDescription);
            $hostname = $parsed['host'];  // WWW.YOUTUBE.COM
            $query = '';
            $path = '';

            if (isset($parsed['query'])) {
                $query = $parsed['query']; // v=5sRDHnTApSw&feature=youtu.be.......to end of the string
            }
            if (isset($parsed['query'])) {
                $path = $parsed['path']; // this is for vimeo.com
            }
// for youtube
            if ($request->video_from == 1) {
// YOUTUBE DESCRIPTION EXAMPLE
                $Arr = explode('v=', $query);

// from video id, until to end of the string like 5sRDHnTApSw&feature=youtu.be The master Bob Haro......
                $videoIDwithString = $Arr[1];

                $videoID = substr($videoIDwithString, 0, 11);

//YOUTUBE.COM
                if ((isset($videoID)) && (isset($hostname))) {

                    $iframe = '<iframe style=" width:100%;" height="230" src="http://www.youtube.com/embed/' . $videoID . '" frameborder="0" allowfullscreen></iframe>';
                }
            } elseif ($request->video_from == 2) {

//VIMEO.COM
                if ((isset($hostname)) && $hostname == 'vimeo.com') {
                    $ArrV = explode('/', $textDescription); // from ID to end of the string

                    $videoID = substr(end($ArrV), 0, 9); // to get video ID
                    $vimdeoIDInt = intval($videoID); // ID must be integer

                    $iframe = '<iframe src="http://player.vimeo.com/video/' . $vimdeoIDInt . '" style=" width:100%;" height="230" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
                }
            }
        }
        $user->video_iframe = $iframe;



        if (isset($request->reseller_id) && $request->reseller_id != NULL && $request->role == 'client')
            $user->parent_id = $request->reseller_id;
        else if (isset($request->client_id) && $request->client_id != NULL && $request->role == 'user')
            $user->parent_id = $request->client_id;
        else
            $user->parent_id = $request->parent_id;

        if ($request->url_type && $user->parent_id != 0) {
            $parent = User::find()
                    ->select('instance_url')
                    ->where(['id' => $user->parent_id])
                    ->one();
            $user->default_url = str_replace("http://", "http://" . $request->instance_name . ".", $parent->instance_url);
        } else if ($request->url_type && $user->parent_id == 0) {
            $user->default_url = "http://" . $request->instance_name . ".squibdrive.net";
        } else {
            $user->default_url = $request->instance_url;
        }

        $address = urlencode($request->address . " " . $request->city . " " . $request->state . " " . $request->zip);
        $region = $request->country_code;

        $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
        $json = json_decode($json);
        if (isset($json) && !empty($json->{'results'})) {
            $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
            $lng = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        } else {
            $lat = 0;
            $lng = 0;
        }
        $user->lat = $lat;
        $user->lng = $lng;

        $existEmail = User::find()
                ->where('id != :id', [':id' => $id])
                ->andWhere('email_id = :email_id', [':email_id' => $user->email_id])
                ->count();
        if (isset($request->instance_name)) {
            $existInstance = User::find()
                    ->where('id != :id', [':id' => $id])
                    ->andWhere('instance_name = :instance_name', [':instance_name' => $user->instance_name])
                    ->count();
        }
        if (isset($request->instance_url)) {
            $existInstanceUrl = User::find()
                    ->where('id != :id', [':id' => $id])
                    ->andWhere('instance_url = :instance_url', [':instance_url' => $user->instance_url])
                    ->count();
        }
        if ($passwd != "") {
            $hash = Yii::$app->getSecurity()->generatePasswordHash($passwd);
            $user->passwd = $hash;
        }
        $domain_name = Yii::$app->commoncomponent->get_domain($user->instance_url);
//        echo $user->parent_id;
//        exit;


        if ($request->url_type && $request->instance_url && !$this->custom_domain_check($domain_name, $user->parent_id)) {
            $status = 0;
            $response = 'Please check Instance URL is pointing the same nameserver as listed.';
        } else if ($existEmail > 0) {
            $status = 0;
            $response = 'Email already exists';
        } else if ($existInstance > 0) {
            $status = 0;
            $response = 'Instance Name already taken';
        } else if ($existInstanceUrl > 0) {
            $status = 0;
            $response = 'Instance URL already exist';
        } else if ($user->update() !== false) {
// update successful
            if ($user->url_type && $this->list_addon_domain($domain_name)) {
                $this->create_addon_domain($domain_name, $request->instance_name);
            }

            $status = 1;
            $response = 'Updated Successfully';
            Yii::$app->db->createCommand()->delete('user_social_networks', 'user_id = ' . $id)->execute();
            foreach ($request->user_social_network as $sn) {
                if (isset($sn->social_network_id)) {
                    $create['user_id'] = $id;
                    $create['social_network_id'] = $sn->social_network_id;
                    $create['url'] = $sn->url;
                    Yii::$app->db->createCommand()->insert('user_social_networks', $create)->execute();
                }
            }

            if (isset($request->squibcard)) {
                $user_role = $request->role;

                $query = new Query;
                $query
                        ->from('user_permissions')
                        ->where(['user_id' => $id])
                        ->select("*");

                $command = $query->createCommand();
                $exist = $command->queryOne();

                if ($exist) {
                    Yii::$app->db->createCommand()->update('user_permissions', ['status' => 1], 'user_id =' . $id . ' AND module_id ="5"')->execute();
                } else {
                    $query = new Query;
                    $query
                            ->from('module_permissions')
                            ->where(['role' => $user_role])
                            ->join('JOIN', 'squib_module', $on = 'module_permissions.module_id = squib_module.id ')
                            ->orderBy('module_id ASC')
                            ->select("module_id,module_permissions.status,squib_module.name");

                    $command = $query->createCommand();
                    $modules = $command->queryAll();
                    foreach ($modules as $permission) {
                        $user_permissions = array();
                        $user_permissions['status'] = ($permission['module_id'] == 5) ? 1 : ( $permission['status'] ? 1 : 0);
                        $user_permissions['module_id'] = $permission['module_id'];
                        $user_permissions['user_id'] = $id;
                        Yii::$app->db->createCommand()->insert('user_permissions', $user_permissions)->execute();
                    }
                }
            }
        } else {
// update failed
            $status = 0;
            $response = 'not correct';
        }


        $this->setHeader(200);

        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Upload User Cover Photo API.
     * @return mixed
     */
    public function actionUploadcoverphoto() {

        if (isset($_REQUEST['user_id'])) {
            $user_id = $_REQUEST['user_id'];
            if ($user_id && isset($_FILES["file"])) {
                $tempPath = $_FILES['file']['tmp_name'];
                $timestamp = time();
                $file_type1 = basename($_FILES["file"]["name"]);
                $file_type = pathinfo($file_type1, PATHINFO_EXTENSION);
                $randno = rand(1000, 999999);
                $savd_file_name = "user_" . $timestamp . "_" . $randno . "." . $file_type;
                $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $savd_file_name;
                if (move_uploaded_file($tempPath, $uploadPath)) {
                    ///insert details in to database
                    $user_pic['cover_photo'] = $savd_file_name;
                    Yii::$app->db->createCommand()->update('user', $user_pic, 'id =' . $user_id)->execute();

                    $img_url = Yii::getAlias('@web') . "/uploads/users/";
                    $img_path = $img_url . $savd_file_name;

                    $response = array('answer' => 'File transfer completed', 'file' => $img_path);
                    $status = 1;
                } else {
                    $status = 0;
                    $response = 'Error';
                }
            }
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Create User API.
     * @return mixed
     */
    public function actionCreateuser() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
//print_r($request);exit;
        $user = new User;
        $user->admin_name = $request->admin_name;
        $user->email_id = $request->email_id;
        $user->user_status = $request->user_status;
        $user->role = $request->role;
        $user->passwd = Yii::$app->getSecurity()->generatePasswordHash($request->passwd);
        $user->instance_name = isset($request->instance_name) ? $request->instance_name : "";
        $user->url_type = isset($request->url_type) ? $request->url_type : "";
        $user->instance_url = isset($request->instance_url) ? $request->instance_url : "";
        $user->organization = isset($request->organization) ? $request->organization : "";
        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->country_code = $request->country_code;
        $user->sex = $request->sex;
        $user->dob = date("Y-m-d", strtotime($request->dob));
        $user->website = $request->website;
        $user->mobile_phone = $request->mobile_phone;
        $user->work_phone = $request->work_phone;
        $user->about_me = $request->about_me;
        $user->created = date("Y-m-d H:i:s");
        $user->modified = date("Y-m-d H:i:s");
        $user->squibcard = $request->squibcard ? 1 : 0;

        if (isset($request->reseller_id) && $request->reseller_id != NULL && $request->role == 'client')
            $user->parent_id = $request->reseller_id;
        else if (isset($request->client_id) && $request->client_id != NULL && $request->role == 'user')
            $user->parent_id = $request->client_id;
        else
            $user->parent_id = $request->parent_id;


        if ($request->url_type && $user->parent_id != 0) {
            $parent = User::find()
                    ->select('instance_url')
                    ->where(['id' => $user->parent_id])
                    ->one();
            $user->default_url = str_replace("http://", "http://" . $request->instance_name . ".", $parent->instance_url);
        } else if ($request->url_type && $user->parent_id == 0) {
            $user->default_url = "http://" . $request->instance_name . ".squibdrive.net";
        } else {
            $user->default_url = $request->instance_url;
        }


        $userExist = User::findOne([
                    'email_id' => $request->email_id,
        ]);

        if (isset($request->instance_name)) {
            $existInstance = User::findOne([
                        'instance_name' => $request->instance_name,
            ]);
        }
        if (isset($request->instance_url)) {
            $existInstanceUrl = User::findOne([
                        'instance_url' => $request->instance_url,
            ]);
        }
        $domain_name = Yii::$app->commoncomponent->get_domain($user->instance_url);

        if ($userExist) {
            $response = 'already exist';
        } else if ($request->url_type && $request->instance_url && !$this->custom_domain_check($domain_name, $user->parent_id)) {
            $status = 0;
            $response = 'Please check Instance URL is pointing the same nameserver as listed.';
        } else if (isset($existInstance)) {
            $response = 'Instance Name already taken.';
        } else if (isset($existInstanceUrl)) {
            $response = 'Instance URL already exist.';
        } else {
            $address = urlencode($request->address . " " . $request->city . " " . $request->state . " " . $request->zip);
            $region = $request->country_code;

            $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
            $json = json_decode($json);
//print_r($json);exit;

            if (isset($json) && !empty($json->{'results'})) {
                $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $lng = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
            } else {
                $lat = 0;
                $lng = 0;
            }
            $user->lat = $lat;
            $user->lng = $lng;

            $user->insert();
            $last_id = $user->id;

            if ($user->url_type && $this->list_addon_domain($domain_name)) {
                $this->create_addon_domain($domain_name, $request->instance_name);
            }


            if (isset($request->squibcard)) {
                $user_role = $request->role;

                $query = new Query;
                $query
                        ->from('module_permissions')
                        ->where(['role' => $user_role])
                        ->join('JOIN', 'squib_module', $on = 'module_permissions.module_id = squib_module.id ')
                        ->orderBy('module_id ASC')
                        ->select("module_id,module_permissions.status,squib_module.name");

                $command = $query->createCommand();
                $modules = $command->queryAll();
                foreach ($modules as $permission) {
                    $user_permissions = array();
                    $user_permissions['status'] = ($permission['module_id'] == 5) ? 1 : ( $permission['status'] ? 1 : 0);
                    $user_permissions['module_id'] = $permission['module_id'];
                    $user_permissions['user_id'] = $last_id;
                    Yii::$app->db->createCommand()->insert('user_permissions', $user_permissions)->execute();
                }
            }
            $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $last_id;
            $masterFolder = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $last_id . DIRECTORY_SEPARATOR . "master";

            mkdir($uploadPath, 0777, true);
            chmod($uploadPath, 0777);

            mkdir($masterFolder, 0777, true);
            chmod($masterFolder, 0777);

            $link = Yii::$app->params['HTTP_URL'] . "/login";
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($user->parent_id);
            Yii::$app->mailer->compose('welcome_user', [
                        'email' => $request->email_id,
                        'password' => $request->passwd,
                        'link' => $link,
                        'username' => $request->admin_name,
                    ])
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo($user->email_id)
                    ->setSubject('Account created on ' . Yii::$app->params['SiteName'])
//->setTextBody($meesage)
                    ->send();

            $response = 'correct';
        }
        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Delete User API.
     * @return mixed
     */
    public function actionDeleteuser() {
        $id = $_GET ['id'];
        $delete = User::deleteAll('id = ' . $id);
        if ($delete) {

            $response = 'correct';
        } else {
            $response = 'Not correct';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    function custom_domain_check($domain, $user_id) {

//return true; // dns_get_record not working

        if ($domain != "squibdrive.net") {

            $valid = false;

            if ($user_id == 0)
                $user_id = 1;

            $query = new Query;
            $query
                    ->from('name_servers')
                    ->where(['user_id' => $user_id])
                    ->select("ip_address,name_server");

            $command = $query->createCommand();
            $records = $command->queryAll();


            $squibips_array = array();
            $squibdns_array = array();

            foreach ($records as $record) {
                $squibips_array[] = $record['ip_address'];
                $squibdns_array[] = $record['name_server'];
            }

            $squibips_array = array_unique($squibips_array);
            $squibdns_array = array_unique($squibdns_array);
            try {
                $results1 = dns_get_record($domain, DNS_A);
                $results2 = dns_get_record($domain, DNS_NS);
            } catch (yii\base\ErrorException $e) {
                return true;
            }


            if ($valid == false && !empty($squibips_array)) {
                for ($i = 0; $i < count($results1); $i++) {
                    if (in_array($results1[$i]['ip'], $squibips_array)) {
                        $valid = true;
                        break;
                    }
                }
            }

            if ($valid == false && !empty($squibdns_array)) {
                for ($i = 0; $i < count($results2); $i++) {
                    if (in_array($results2[$i]['target'], $squibdns_array)) {
                        $valid = true;
                        break;
                    }
                }
            }
            return $valid;
        } else {

            return false;
        }
    }

    function create_addon_domain($vanity_domain, $instance_name) {

        $whmusername = "root";
        $whmpassword = "u2N+W-?VGvW.w66";

        $query = "https://72.55.146.214:2087/json-api/cpanel?cpanel_jsonapi_user=squibdrvnet&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=AddonDomain&cpanel_jsonapi_func=addaddondomain&dir=public_html%2fdev&newdomain=" . $vanity_domain . "&subdomain=" . $instance_name;

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($whmusername . ":" . $whmpassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
// log error if curl exec fails
        }
        curl_close($curl);

        $out = json_decode($result);

        if (!isset($out->cpanelresult->error))
            $this->create_wildcard_domain($vanity_domain);
    }

    function create_wildcard_domain($domain) {

        $whmusername = "root";
        $whmpassword = "u2N+W-?VGvW.w66";

//$query = "https://72.55.146.214:2087/json-api/listaccts?api.version=1"; 
//https://hostname.example.com:2087/cpsess###########/json-api/cpanel?cpanel_jsonapi_user=user&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=AddonDomain&cpanel_jsonapi_func=addaddondomain&dir=addondomain%2Fhome%2Fdir&newdomain=addondomain.com&subdomain=subdomain.example.com

        $query = "https://72.55.146.214:2087/json-api/cpanel?cpanel_jsonapi_user=squibdrvnet&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SubDomain&cpanel_jsonapi_func=addsubdomain&dir=public_html%2fdev&domain=*&rootdomain=" . $domain . "&disallowdot=1";

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($whmusername . ":" . $whmpassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
// log error if curl exec fails
        }
        curl_close($curl);

//        print $result;
//        exit;
    }

    function list_addon_domain($vanity_domain) {

        $whmusername = "root";
        $whmpassword = "u2N+W-?VGvW.w66";
        $query = "https://72.55.146.214:2087/json-api/cpanel?cpanel_jsonapi_user=squibdrvnet&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=AddonDomain&cpanel_jsonapi_func=listaddondomains&regex=" . $vanity_domain;

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($whmusername . ":" . $whmpassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
// log error if curl exec fails
        }
        curl_close($curl);

        $out = json_decode($result);
//print_r($out->cpanelresult->data);
        if ($out->cpanelresult->data) {
            return 0;
        } else {
            return 1;
        }
    }

    function delete_addon_domain($vanity_domain, $instance_name) {

        $whmusername = "root";
        $whmpassword = "u2N+W-?VGvW.w66";

        $query = "https://72.55.146.214:2087/json-api/cpanel?cpanel_jsonapi_user=squibdrvnet&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=AddonDomain&cpanel_jsonapi_func=deladdondomain&domain=" . $vanity_domain . "&subdomain=" . $instance_name;

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($whmusername . ":" . $whmpassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
// log error if curl exec fails
        }
        curl_close($curl);

        $out = json_decode($result);
        print_r($out);
        exit;
    }

    /**
     * Cloud Instance List API.
     * @return mixed
     */
    public function actionCloudinstancelist() {
        $user_id = $_GET['user_id'];

        $user = User::findOne([
                    'id' => $user_id,
        ]);
        $query = new Query;
        if ($user->role == 'admin') {
            $query
                    ->from('user')
                    ->where("instance_name != ''")
                    ->select("id,instance_name");
        } else {

            $usersID = User::find()
                    ->select('id')
                    ->where(['parent_id' => $user->id])
                    ->all();
            if ($user->role == 'reseller') {
                foreach ($usersID as $client) {
                    $usersID = array_merge($usersID, User::find()
                                    ->select('id')
                                    ->where(['parent_id' => $client->id])
                                    ->all());
                }
            }
            if ($usersID) {
                foreach ($usersID as $userid) {
                    $user_ids[] = $userid->id;
                }
            } else {
                $user_ids = 0;
            }
            $query
                    ->from('user')
                    ->andFilterWhere(['IN', 'id', $user_ids])
                    ->orWhere(['id' => $user->id])
                    ->andWhere("instance_name != ''")
                    ->select("id,instance_name");
        }

        $command = $query->createCommand();
        $models = $command->queryAll();

        $this->setHeader(200);

//echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $models, JSON_PRETTY_PRINT));
    }

    /**
     * Visitor Signup API.
     * @return mixed
     */
    public function actionSignup() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->user_id) {
            $parent = User::findOne([
                        'id' => $request->user_id,
            ]);
        }

        $user = new User;
        $user->admin_name = $request->full_name;
        $user->email_id = $request->email_address;
        $user->user_status = 1;
        $user->role = (($parent->role == 'admin') ? 'reseller' : (($parent->role == 'reseller') ? 'client' : (($parent->role == 'client') ? 'user' : 'visitor')));
        $user->passwd = Yii::$app->getSecurity()->generatePasswordHash(rand(0, 999999));
        $user->instance_name = "";
        $user->url_type = "";
        $user->instance_url = "";
        $user->organization = "";
        $user->address = "";
        $user->city = "";
        $user->ip_address = $request->ip_address;
        $user->state = "";
        $user->zip = "";
        $user->country_code = "";
        $user->squibkey_id = "";
        $user->sex = "";
        $user->dob = "0000-00-00";
        $user->website = "";
        $user->mobile_phone = "";
        $user->work_phone = "";
        $user->about_me = "";
        $user->created = date("Y-m-d H:i:s");
        $user->modified = date("Y-m-d H:i:s");
        $user->parent_id = $parent->id;

        $userExist = User::findOne([
                    'email_id' => $request->email_address,
        ]);

        if ($userExist) {
            $status = 0;
            $response = 'Already Exist';
        } else {

            $lat = 0;
            $lng = 0;

            $user->lat = $lat;
            $user->lng = $lng;

            $user->insert();
            $last_id = $user->id;
            $link = Yii::$app->params['HTTP_URL'] . "/login/" . $last_id . "/" . md5($last_id) . "/" . $request->email_address;
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($user->parent_id);
            Yii::$app->mailer->compose('user_signup', [
                        'link' => $link,
                    ])
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo($request->email_address)
                    ->setSubject('Activate your account on ' . Yii::$app->params['SiteName'])
//->setTextBody($meesage)
                    ->send();

            $status = 1;
            $response = 'Signup Successful. Please check your email to activate your account.';

            $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $last_id;
            $masterFolder = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $last_id . DIRECTORY_SEPARATOR . "master";

//            mkdir($uploadPath, 0777, true);
//            chmod($uploadPath, 0777);
//
//            mkdir($masterFolder, 0777, true);
//            chmod($masterFolder, 0777);
        }
        $this->setHeader(200);

        echo json_encode(array('status' => $status, 'data' => $response, 'link' => $link), JSON_PRETTY_PRINT);
    }

    /**
     * Confirm account API.
     * @return mixed
     */
    public function actionConfirmation() {
        $postdata = file_get_contents("php://input");
//$_REQUEST = '{"emailid1":"arnavdots@dotsquares.com","password1":"Hello@123"}';
        $request = json_decode($postdata);

        if (md5($request->par1) == $request->par2) {

            $user = User::findOne([
                        'id' => $request->par1,
                        'email_id' => $request->par3
            ]);
            $parent = User::findOne([
                        'id' => $user->parent_id,
            ]);
            $domain_url = $parent->instance_url;

            if ($user->user_status == 0) {
                $status = 0;
                $response = 'Already Activated.';
            } else {
                $password = rand(0, 999999);
                $user->user_status = 0;

                $user->passwd = Yii::$app->getSecurity()->generatePasswordHash($password);

                $user->update();

                $link = Yii::$app->params['HTTP_URL'] . "/login";
                Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($user->parent_id);
                Yii::$app->mailer->compose('welcome_user', [
                            'email' => $user->email_id,
                            'password' => $password,
                            'link' => $link,
                            'username' => $user->admin_name,
                        ])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo($user->email_id)
                        ->setSubject('Account created on ' . Yii::$app->params['SiteName'])
//->setTextBody($meesage)
                        ->send();

                $status = 1;
                $response['message'] = 'Thank you for verifying your email address. Please check your email to login to your new SquibCard Account and start sharing with your friends today.';
                $response['domain_url'] = $domain_url;
            }
        } else {
            $status = 0;
            $response = 'The url is invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Cloud Master Files API.
     * @return mixed
     */
    public function actionMasterfiles() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $cinstence_id = $request->user_id;
        $master = $request->master;

        $rs = array();


        $query = new Query;
        $query
                ->from('instance_folder')
                ->where(['parent_id' => 0, 'user_id' => $cinstence_id, 'master' => $master])
                ->orderBy('name ASC')
                ->select("id,name,master");

        $command = $query->createCommand();
        $folders = $command->queryAll();
        $i = 0;
        $rs1 = array();
        if ($folders) {

            foreach ($folders as $folder) {
                $rs1[$i]['folder_id'] = $folder['id'];
                $rs1[$i]['master'] = $folder['master'];
                $rs1[$i++]['name'] = $folder["name"];
            }
        }

        $rs['folders'] = $rs1;

        $i = 0;
        $query = new Query;
        $query
                ->from('instance_files')
                ->where(['user_id' => $cinstence_id, 'master' => $master, 'folder_id' => 0])
                ->orderBy('orgnl_file_name ASC')
                ->select("file_id,orgnl_file_name,savd_file_name,file_type,master");

        $command = $query->createCommand();
        $models = $command->queryAll();
        $rs2 = array();
        if ($models) {
            foreach ($models as $file) {
                $orgnl_name = $file['orgnl_file_name'];

                if ($file['master']) {
                    $file_path = $cinstence_id . "/master/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($cinstence_id) . "master/";
                } else {
                    $file_path = $cinstence_id . "/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($cinstence_id);
                }

                $file_length = strlen($orgnl_name);
                $reduced_name = substr($orgnl_name, 0, 13);
                if ($file_length > 13) {
                    $dip_name = $reduced_name . "...";
                } else {
                    $dip_name = $reduced_name;
                }
                $img_path = '';
                if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                    $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                    $img_path = $img_url . $file_path;
                } else {

                    $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                    $file_type = $file["file_type"];
                    $img_name = "";
// Open a directory, and read its contents
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($filename = readdir($dh)) !== false) {
                                $explode_file = explode(".", $filename);
                                $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                                if (strcasecmp($file_type, $file_type_all) == 0) {
                                    $foldr_img = $filename;
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                    break;
                                } else {
                                    $foldr_img = "DOC.png";
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }

                $file_type_no = "";

                if ($file['file_type'] == "mp4") {
                    $file_type_no = 1;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif ($file['file_type'] == "mp3") {
                    $file_type_no = 2;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                    $file_type_no = 3;
                    $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                    $file_type_no = 4;
                    $view_path = $root_path . $file['savd_file_name'];
                } else {
                    $file_type_no = 5;
                    $view_path = $root_path . $file['savd_file_name'];
                }

                $rs2[$i]['file_id'] = $file['file_id'];
                $rs2[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
                $rs2[$i]['reduced_name'] = $dip_name;
                $rs2[$i]['file_type'] = $file['file_type'];
                $rs2[$i]['img_path'] = $img_path;
                $rs2[$i]['file_path'] = $file_path;
                $rs2[$i]['file_type_no'] = $file_type_no;
                $rs2[$i]['view_path'] = $view_path;
                $rs2[$i]['master'] = $file['master'];
                $rs2[$i++]['savd_file_name'] = $file["savd_file_name"];
            }
        }
        $rs['files'] = $rs2;
        $this->setHeader(200);

// echo json_encode(array('records' => $rs), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Cloud Master Files API.
     * @return mixed
     */
    public function actionGetfiles() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $cinstence_id = $request->user_id;
        $folder_id = $request->folder_id;
        $master = $request->master;

        $rs = array();


        $query = new Query;
        $query
                ->from('instance_folder')
                ->where(['parent_id' => $folder_id, 'user_id' => $cinstence_id, 'master' => $master])
                ->orderBy('name ASC')
                ->select("id,name,master");

        $command = $query->createCommand();
        $folders = $command->queryAll();
        $i = 0;
        $rs1 = array();
        if ($folders) {
            foreach ($folders as $folder) {
                $rs1[$i]['folder_id'] = $folder['id'];
                $rs1[$i]['master'] = $folder['master'];
                $rs1[$i++]['name'] = $folder["name"];
            }
        }

        $rs['folders'] = $rs1;

        $i = 0;
        $query = new Query;
        $query
                ->from('instance_files')
                ->where(['user_id' => $cinstence_id, 'master' => $master, 'folder_id' => $folder_id])
                ->orderBy('orgnl_file_name ASC')
                ->select("file_id,orgnl_file_name,savd_file_name,file_type,master");

        $command = $query->createCommand();
        $models = $command->queryAll();
        $rs2 = array();
        if ($models) {

            foreach ($models as $file) {
                $orgnl_name = $file['orgnl_file_name'];

                if ($file['master']) {
                    $file_path = $cinstence_id . "/master/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($cinstence_id) . "master/";
                } else {
                    $file_path = $cinstence_id . "/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($cinstence_id);
                }

                $file_length = strlen($orgnl_name);
                $reduced_name = substr($orgnl_name, 0, 13);
                if ($file_length > 13) {
                    $dip_name = $reduced_name . "...";
                } else {
                    $dip_name = $reduced_name;
                }
                $img_path = '';
                if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                    $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                    $img_path = $img_url . $file_path;
                } else {

                    $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                    $file_type = $file["file_type"];
                    $img_name = "";
// Open a directory, and read its contents
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($filename = readdir($dh)) !== false) {
                                $explode_file = explode(".", $filename);
                                $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                                if (strcasecmp($file_type, $file_type_all) == 0) {
                                    $foldr_img = $filename;
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                    break;
                                } else {
                                    $foldr_img = "DOC.png";
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }

                $file_type_no = "";

                if ($file['file_type'] == "mp4") {
                    $file_type_no = 1;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif ($file['file_type'] == "mp3") {
                    $file_type_no = 2;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                    $file_type_no = 3;
                    $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                    $file_type_no = 4;
                    $view_path = $root_path . $file['savd_file_name'];
                } else {
                    $file_type_no = 5;
                    $view_path = $root_path . $file['savd_file_name'];
                }

                $rs2[$i]['file_id'] = $file['file_id'];
                $rs2[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
                $rs2[$i]['reduced_name'] = $dip_name;
                $rs2[$i]['file_type'] = $file['file_type'];
                $rs2[$i]['img_path'] = $img_path;
                $rs2[$i]['file_path'] = $file_path;
                $rs2[$i]['file_type_no'] = $file_type_no;
                $rs2[$i]['view_path'] = $view_path;
                $rs2[$i]['master'] = $file['master'];
                $rs2[$i++]['savd_file_name'] = $file["savd_file_name"];
            }
        }
        $rs['files'] = $rs2;
        $this->setHeader(200);

// echo json_encode(array('records' => $rs), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Upload Master Files API.
     * @return mixed
     */
    public function actionFilesupload() {
        ini_set('max_execution_time', 5 * 60);
        ini_set('memory_limit', -1);
        ini_set('max_input_time ', -1);

       
        if (!empty($_FILES)) {
            $cinstence_id = $_POST['user_id'];
            $master = $_POST['master'];
            $folder_id = $_POST['folder_id'];

            $tempPath = $_FILES['file']['tmp_name'];
            $orgnl_file_name = $_FILES["file"]["name"];
            $timestamp = time();
            $file_type1 = basename($_FILES["file"]["name"]);
            $file_type = pathinfo($file_type1, PATHINFO_EXTENSION);
            $randno = rand(1000, 999999);
            $savd_file_name = $cinstence_id . "_" . $timestamp . "_" . $randno . "." . $file_type;
            if ($master)
                $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $cinstence_id . DIRECTORY_SEPARATOR . "master" . DIRECTORY_SEPARATOR . $savd_file_name;
            else
                $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $cinstence_id . DIRECTORY_SEPARATOR . $savd_file_name;


            if (move_uploaded_file($tempPath, $uploadPath)) {
///insert details in to database
//                $created_date = date("Y-m-d");
//                $file_id = "";
//                $model = new InstanceFiles();
//                $model->user_id = $cinstence_id;
//                $model->folder_id = $folder_id;
//                $model->orgnl_file_name = $orgnl_file_name;
//                $model->savd_file_name = $savd_file_name;
//                $model->file_type = $file_type;
//                $model->master = $master;
//                $model->created_date = $created_date;
//                
//                print_r($model);
//                $model->save();
                
                 $share_file = array();
                        $share_file['user_id'] = $cinstence_id;
                        $share_file['folder_id'] = $folder_id;
                        $share_file['orgnl_file_name'] = $orgnl_file_name;
                        $share_file['savd_file_name'] = $savd_file_name;
                        $share_file['file_type'] = $file_type;
                        $share_file['master'] = $master;
                        $share_file['created_date'] = $created_date;
                        Yii::$app->db->createCommand()->insert('instance_files', $share_file)->execute();
print_r($model);
                $answer = array('answer' => 'File transfer completed');
                $json = json_encode($answer);

                echo $json;
            }
            exit;
        } else {
            echo 'No files';
        }
    }

    protected function getWebRootPath($cinstence_id) {
        $base_dir = __DIR__; // Absolute path to your installation, ex: /var/www/mywebsite
        $doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
        $base_url = preg_replace("!^${doc_root}!", '', $base_dir); # ex: '' or '/mywebsite'
        $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $port = $_SERVER['SERVER_PORT'];
        $disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
        $domain = $_SERVER['SERVER_NAME'];
//$root_path = "${protocol}://${domain}${disp_port}${base_url}";
        $root_path = "${protocol}://${domain}${disp_port}" . Yii::getAlias('@web');
        return $root_path . "/uploads/instances/" . $cinstence_id . "/";
    }

    public function actionImagedelete() {

        $fileid = $_GET ['fileid'];

        $root_path = Yii::getAlias('@webroot');

        $result = InstanceFiles::findOne($fileid);

        $qry = $result->delete();

        if ($qry) {
            if ($result->master) {
                $path = $root_path . "/uploads/instances/" . $result->user_id . "/master/" . $result->savd_file_name;
            } else {
                $path = $root_path . "/uploads/instances/" . $result->user_id . "/" . $result->savd_file_name;
            }
            unlink($path);
            // echo 'correct';
            return json_encode(array('status' => 1, 'data' => 'correct', JSON_PRETTY_PRINT));
        } else {
            // echo 'Not correct';
            return json_encode(array('status' => 1, 'data' => 'Not correct', JSON_PRETTY_PRINT));
        }
    }

    public function actionRemovefolder() {

        $folder_id = $_GET ['folder_id'];

        $root_path = Yii::getAlias('@webroot');
        $child_ids = $this->getFolderChilds($folder_id);
        $folder_ids = array_merge(array($folder_id), $child_ids);

        $query = new Query;
        $query
                ->from('instance_files')
                ->where(['folder_id' => $folder_ids])
                ->select("file_id,user_id,savd_file_name,master");

        $command = $query->createCommand();
        $files = $command->queryAll();
        if ($files) {
            foreach ($files as $file) {
                if ($file['master']) {
                    $path = $root_path . "/uploads/instances/" . $file['user_id'] . "/master/" . $file['savd_file_name'];
                } else {
                    $path = $root_path . "/uploads/instances/" . $file['user_id'] . "/" . $file['savd_file_name'];
                }
                unlink($path);
            }
            Yii::$app->db->createCommand()->delete('instance_files', ['folder_id' => $folder_ids])->execute();
        }


        $qry = Yii::$app->db->createCommand()->delete('instance_folder', ['id' => $folder_ids])->execute();

        if ($qry) {
            // echo 'correct';
            return json_encode(array('status' => 1, 'data' => 'correct', JSON_PRETTY_PRINT));
        } else {
            // echo 'Not correct';
            return json_encode(array('status' => 1, 'data' => 'Not correct', JSON_PRETTY_PRINT));
        }
    }

    public function getFolderChilds($ids) {
        $query = new Query;
        $query
                ->from('instance_folder')
                ->where(['parent_id' => $ids])
                ->select("id");

        $command = $query->createCommand();
        $result = $command->queryAll();
        //$result = User::find()->where(['parent_id' => $ids])->asArray()->all();
        if (!empty($result)) {
            $ids = array_column($result, 'id');
            if (!empty($ids)) {
                return array_merge($ids, $this->getFolderChilds($ids));
            }
        } else {
            return array();
        }
    }

    /**
     * Create User API.
     * @return mixed
     */
    public function actionSendfiles() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        if (isset($request)) {

            if ($request->type == 'file') {
                $count = (new Query())
                        ->from('shared_files')
                        ->where(['file_id' => $request->item_id])
                        ->count();
                if ($count)
                    Yii::$app->db->createCommand()->delete('shared_files', 'file_id = ' . $request->item_id)->execute();
                if ($request->to && $request->users) {
                    foreach ($request->users as $user) {
                        $share_file = array();
                        $share_file['src_id'] = $request->src_id;
                        $share_file['file_id'] = $request->item_id;
                        $share_file['dest_id'] = $user->id;
                        $share_file['to'] = $request->to;
                        $share_file['created'] = date('Y-m-d H:i:s');
                        Yii::$app->db->createCommand()->insert('shared_files', $share_file)->execute();
                    }
                } else {
                    $share_file = array();
                    $share_file['src_id'] = $request->src_id;
                    $share_file['file_id'] = $request->item_id;
                    $share_file['dest_id'] = 0;
                    $share_file['to'] = $request->to;
                    $share_file['created'] = date('Y-m-d H:i:s');
                    Yii::$app->db->createCommand()->insert('shared_files', $share_file)->execute();
                }
                $response = 'You have shared this file.';
            } else if ($request->type == 'folder') {
                $query = new Query;
                $query
                        ->from('instance_files')
                        ->where(['user_id' => $request->src_id, 'folder_id' => $request->item_id])
                        ->orderBy('orgnl_file_name ASC')
                        ->select("file_id");

                $command = $query->createCommand();
                $models = $command->queryAll();
                if ($models) {
                    foreach ($models as $file) {
                        $count = (new Query())
                                ->from('shared_files')
                                ->where(['file_id' => $file['file_id']])
                                ->count();
                        if ($count)
                            Yii::$app->db->createCommand()->delete('shared_files', 'file_id = ' . $file['file_id'])->execute();
                        if ($request->to && $request->users) {
                            foreach ($request->users as $user) {
                                $share_file = array();
                                $share_file['src_id'] = $request->src_id;
                                $share_file['file_id'] = $file['file_id'];
                                $share_file['dest_id'] = $user->id;
                                $share_file['to'] = $request->to;
                                $share_file['created'] = date('Y-m-d H:i:s');
                                Yii::$app->db->createCommand()->insert('shared_files', $share_file)->execute();
                            }
                        } else {
                            $share_file = array();
                            $share_file['src_id'] = $request->src_id;
                            $share_file['file_id'] = $file['file_id'];
                            $share_file['dest_id'] = 0;
                            $share_file['to'] = $request->to;
                            $share_file['created'] = date('Y-m-d H:i:s');
                            Yii::$app->db->createCommand()->insert('shared_files', $share_file)->execute();
                        }
                    }
                }
                $response = 'You have shared this folder files.';
            }
        } else {
            $response = 'Error Occured';
        }
        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get On Level Child User API.
     * @return mixed
     */
    public function actionGetchilduser() {
        $user_id = $_GET ['user_id'];

        $cnd = array('parent_id' => $user_id);

        $users = User::find()->where($cnd)->all();
        $rs = array();
        $i = 0;
        foreach ($users as $user) {
            $rs[$i]['id'] = $user->attributes['id'];
            $rs[$i]['vanity_domain'] = $user->attributes['instance_url'];
            $rs[$i++]['name'] = $user->attributes['admin_name'];
        }

        $this->setHeader(200);

//echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Get All Level Child User API.
     * @return mixed
     */
    public function actionGetchildusers() {
        $user_id = $_GET ['user_id'];
        $child_ids = $this->getChilds($user_id);
        $child_ids = array_merge(array($user_id), $child_ids);

        $cnd = array('id' => $child_ids);

        $users = User::find()->where($cnd)->all();
        $rs = array();
        $i = 0;
        foreach ($users as $user) {
            $rs[$i]['id'] = $user->attributes['id'];
            $rs[$i]['name'] = $user->attributes['admin_name'];
            $rs[$i]['email'] = $user->attributes['email_id'];
            $rs[$i++]['role'] = $user->attributes['role'];
        }

        $this->setHeader(200);

//echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Get User Drive API.
     * @return mixed
     */
    public function actionGetuserdrive() {
        $user_id = $_GET ['user_id'];
        $folder_type = $_GET ['folder_type'];
        if ($folder_type == 'master') {
            $master = 1;
        } else {
            $master = 0;
        }
        //echo "<pre>";
        $query = new Query;
        $query
                ->from('instance_folder')
                ->where(['user_id' => $user_id, 'master' => $master])
                ->orderBy('name ASC')
                ->select("id,name,parent_id");

        $command = $query->createCommand();
        $folders = $command->queryAll();

        // print_r($folders);

        $query = new Query;
        $query
                ->from('instance_files')
                ->where(['user_id' => $user_id, 'master' => $master])
                ->orderBy('orgnl_file_name ASC')
                ->select("file_id as id,orgnl_file_name as name,folder_id");

        $command = $query->createCommand();
        $files = $command->queryAll();
        $tree = array();
        $index = 0;
        $child_ids = array_merge(array(0), array_column($folders, 'id'));
        //print_r($child_ids);exit;
        foreach ($child_ids as $folder_id) {
            //$folder_id = $folder_m['id'];
            if (!empty($folders)) {
                foreach ($folders as $folder) {
                    $key = array_search($folder_id, array_column($folders, 'parent_id'));
                    if (is_numeric($key)) {
                        if ($folder_id == 0) {
                            $folders[$key]['level'] = $index;
                            $folders[$key]['type'] = 'folder';
                            $tree[] = $folders[$key];
                        } else {
                            $keyT = array_search($folder_id, array_column($tree, 'id'));
                            $folders[$key]['level'] = $tree[$keyT]['level'] + 1;
                            $folders[$key]['type'] = 'folder';
                            array_splice($tree, $keyT, 0, array($folders[$key]));
                        }
                        unset($folders[$key]);
                        $folders = (array_values($folders));
                    }
                }
            }
            if (!empty($files)) {
                foreach ($files as $keys) {
                    $key = array_search($folder_id, array_column($files, 'folder_id'));
                    if (is_numeric($key)) {

                        if ($folder_id == 0) {
                            $files[$key]['level'] = $index;
                            $files[$key]['type'] = 'file';
                            $tree[] = $files[$key];
                        } else {
                            $keyT = array_search($folder_id, array_column($tree, 'id'));
                            $files[$key]['level'] = $tree[$keyT]['level'] + 1;
                            $files[$key]['type'] = 'file';
                            array_splice($tree, $keyT + 1, 0, array($files[$key]));
                        }

                        unset($files[$key]);
                        $files = (array_values($files));
                    }
                }
                //$files=array('rtr');
            }
        }

        $i = 0;
        $result = array();
        if ($tree) {
            foreach ($tree as $branch) {
                $result[$i]['id'] = $branch['id'];
                $result[$i]['type'] = $branch['type'];
                $result[$i]['treelevel'] = $branch['level'];
                $result[$i++]['name'] = $branch["name"];
            }
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $result, JSON_PRETTY_PRINT));
    }

    public function actionCloudinstancefiles() {


        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $cinstence_id = $request->cinstence_id;
        $user = User::findOne([
                    'id' => $cinstence_id,
        ]);

//        $query = new Query;
//        $query
//                ->from('shared_files')
//                ->where(['dest_id' => $cinstence_id, 'src_id' => $user->parent_id, 'to' => 1])
//                ->select("file_id");
//
//        $command = $query->createCommand();
//        $private_shared_files = $command->queryAll();
//
//        $query = new Query;
//        $query
//                ->from('shared_files')
//                ->where(['dest_id' => 0, 'src_id' => $user->parent_id, 'to' => 0])
//                ->select("file_id");
//
//        $command = $query->createCommand();
//        $public_shared_files = $command->queryAll();
//
//        $shared_files_id = array_merge($private_shared_files, $public_shared_files);
//


        $query = new Query;
        $query
                ->from('instance_files')
                ->where(['master' => 0])
                ->andFilterWhere(['like', 'user_id', $cinstence_id])
                ->orderBy('orgnl_file_name ASC')
                ->select("file_id,orgnl_file_name,savd_file_name,file_type,master");

        $command = $query->createCommand();
        $all_files = $command->queryAll();

//        if ($shared_files_id) {
//            $query = new Query;
//            $query
//                    ->from('instance_files')
//                    ->andFilterWhere(['IN', 'file_id', $shared_files_id])
//                    ->orderBy('orgnl_file_name ASC')
//                    ->select("file_id,orgnl_file_name,savd_file_name,file_type,master");
//            $command = $query->createCommand();
//            $shared_files = $command->queryAll();
//            $all_files = array_merge($all_files, $shared_files);
//        }

        $rs = array();
        $i = 0;
        foreach ($all_files as $file) {
            $orgnl_name = $file['orgnl_file_name'];
            if ($file['master']) {
                $file_path = $user->parent_id . "/master/" . $file['savd_file_name'];
                $root_path = $this->getWebRootPath($user->parent_id) . "master/";
            } else {
                $file_path = $cinstence_id . "/" . $file['savd_file_name'];
                $root_path = $this->getWebRootPath($cinstence_id);
            }
            $file_length = strlen($orgnl_name);
            $reduced_name = substr($orgnl_name, 0, 13);
            if ($file_length > 13) {
                $dip_name = $reduced_name . "...";
            } else {
                $dip_name = $reduced_name;
            }
            $img_path = '';
            if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                if ($file['master']) {
                    $img_path = $img_url . $user->parent_id . "/master/" . $file['savd_file_name'];
                } else {
                    $img_path = $img_url . $cinstence_id . "/" . $file['savd_file_name'];
                }
            } else {

                $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                $file_type = $file["file_type"];
                $img_name = "";
// Open a directory, and read its contents
                if (is_dir($dir)) {
                    if ($dh = opendir($dir)) {
                        while (($filename = readdir($dh)) !== false) {
                            $explode_file = explode(".", $filename);
                            $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                            if (strcasecmp($file_type, $file_type_all) == 0) {
                                $foldr_img = $filename;
                                $img_url = "assets/img/avatars/";
                                $img_path = $img_url . $foldr_img;
                                break;
                            } else {
                                $foldr_img = "DOC.png";
                                $img_url = "assets/img/avatars/";
                                $img_path = $img_url . $foldr_img;
                            }
                        }
                        closedir($dh);
                    }
                }
            }

            $file_type_no = "";

            if ($file['file_type'] == "mp4") {
                $file_type_no = 1;
                $view_path = $root_path . $file['savd_file_name'];
            } elseif ($file['file_type'] == "mp3") {
                $file_type_no = 2;
                $view_path = $root_path . $file['savd_file_name'];
            } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                $file_type_no = 3;
                $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
            } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                $file_type_no = 4;
                $view_path = $root_path . $file['savd_file_name'];
            } else {
                $file_type_no = 5;
                $view_path = $root_path . $file['savd_file_name'];
            }

            $rs[$i]['file_id'] = $file['file_id'];
            $rs[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
            $rs[$i]['reduced_name'] = $dip_name;
            $rs[$i]['file_type'] = $file['file_type'];
            $rs[$i]['img_path'] = $img_path;
            $rs[$i]['file_path'] = $file_path;
            $rs[$i]['file_type_no'] = $file_type_no;
            $rs[$i]['view_path'] = $view_path;
            $rs[$i]['master'] = $file['master'];
            $rs[$i++]['savd_file_name'] = $file["savd_file_name"];
        }

        $this->setHeader(200);

// echo json_encode(array('records' => $rs), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    public function actionDownloadfile() {

        ignore_user_abort(true);
        set_time_limit(0); // disable the time limit for this script

        $fileid = $_REQUEST['fileid'];

        $root_path = getcwd();
        $path = $root_path . "/uploads/instances/";

        $result = InstanceFiles::find()->where(['file_id' => $fileid])->orderBy(['orgnl_file_name' => SORT_ASC])->One();
        if ($result->master) {
            $file_path = $result->user_id . "/master/" . $result->savd_file_name;
        } else {
            $file_path = $result->user_id . "/" . $result->savd_file_name;
        }

        $fullPath = $path . $file_path;

        if ($fd = fopen($fullPath, "r")) {
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                    header("Content-type: application/pdf");
                    header("Content-Disposition: attachment; filename=\"" . $result->orgnl_file_name . "\""); // use 'attachment' to force a file download
                    break;
// add more headers for other content types here
                default;
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: filename=\"" . $result->orgnl_file_name . "\"");
                    break;
            }

            header("Cache-control: private"); //use this to open files directly
            while (!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose($fd);
        exit;
    }

    public function actionSharedfiles() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $cinstence_id = $request->cinstence_id;
        $user = User::findOne([
                    'id' => $cinstence_id,
        ]);

        $query = new Query;
        $query
                ->from('shared_files')
                ->where(['dest_id' => $cinstence_id, 'src_id' => $user->parent_id, 'to' => 1])
                ->select("file_id");

        $command = $query->createCommand();
        $private_shared_files = $command->queryAll();

        $query = new Query;
        $query
                ->from('shared_files')
                ->where(['dest_id' => 0, 'src_id' => $user->parent_id, 'to' => 0])
                ->select("file_id");

        $command = $query->createCommand();
        $public_shared_files = $command->queryAll();

        $shared_files_id = array_merge($private_shared_files, $public_shared_files);

        $rs = array();

        if ($shared_files_id) {
            $query = new Query;
            $query
                    ->from('instance_files')
                    ->andFilterWhere(['IN', 'file_id', $shared_files_id])
                    ->orderBy('orgnl_file_name ASC')
                    ->select("file_id,orgnl_file_name,savd_file_name,file_type,master");
            $command = $query->createCommand();
            $shared_files = $command->queryAll();


            $i = 0;
            foreach ($shared_files as $file) {
                $orgnl_name = $file['orgnl_file_name'];
                if ($file['master']) {
                    $file_path = $user->parent_id . "/master/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($user->parent_id) . "master/";
                } else {
                    $file_path = $cinstence_id . "/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($cinstence_id);
                }
                $file_length = strlen($orgnl_name);
                $reduced_name = substr($orgnl_name, 0, 13);
                if ($file_length > 13) {
                    $dip_name = $reduced_name . "...";
                } else {
                    $dip_name = $reduced_name;
                }
                $img_path = '';
                if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                    $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                    if ($file['master']) {
                        $img_path = $img_url . $user->parent_id . "/master/" . $file['savd_file_name'];
                    } else {
                        $img_path = $img_url . $cinstence_id . "/" . $file['savd_file_name'];
                    }
                } else {

                    $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                    $file_type = $file["file_type"];
                    $img_name = "";
// Open a directory, and read its contents
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($filename = readdir($dh)) !== false) {
                                $explode_file = explode(".", $filename);
                                $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                                if (strcasecmp($file_type, $file_type_all) == 0) {
                                    $foldr_img = $filename;
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                    break;
                                } else {
                                    $foldr_img = "DOC.png";
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }

                $file_type_no = "";

                if ($file['file_type'] == "mp4") {
                    $file_type_no = 1;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif ($file['file_type'] == "mp3") {
                    $file_type_no = 2;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                    $file_type_no = 3;
                    $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                    $file_type_no = 4;
                    $view_path = $root_path . $file['savd_file_name'];
                } else {
                    $file_type_no = 5;
                    $view_path = $root_path . $file['savd_file_name'];
                }

                $rs[$i]['file_id'] = $file['file_id'];
                $rs[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
                $rs[$i]['reduced_name'] = $dip_name;
                $rs[$i]['file_type'] = $file['file_type'];
                $rs[$i]['img_path'] = $img_path;
                $rs[$i]['file_path'] = $file_path;
                $rs[$i]['file_type_no'] = $file_type_no;
                $rs[$i]['view_path'] = $view_path;
                $rs[$i]['master'] = $file['master'];
                $rs[$i++]['savd_file_name'] = $file["savd_file_name"];
            }
        }


        $this->setHeader(200);

// echo json_encode(array('records' => $rs), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Users List API.
     * @return mixed
     */
    public function actionGetdashboarddata() {

        $user_id = $_GET['user_id'];
        $user = User::findOne([
                    'id' => $user_id,
        ]);
        if ($user->role == 'admin') {
            $start_time = date('Y-m-01 00:00:00'); // hard-coded '01' for first day
            $end_time = date('Y-m-t 23:59:59');
            $role = 'reseller';
        } else if ($user->role == 'reseller') {
            $start_time = date('Y-m-d 00:00:00', strtotime('this week'));
            $end_time = date('Y-m-d 23:59:59', strtotime('this week +6 days'));
            $role = 'client';
        } else {
            $start_time = date('Y-m-d 00:00:00');
            $end_time = date('Y-m-d 23:59:59');
            $role = 'user';
        }

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("SELECT count(*) as visits, user.admin_name as username, user.profile_image as userimage FROM `visitors` JOIN user on visitors.user_id=user.id where user.role='" . $role . "' and user.parent_id='" . $user_id . "' and (visitors.last_visited >='" . $start_time . "' OR visitors.last_visited <='" . $end_time . "') group by visitors.user_id order by visits DESC LIMIT 1");

        $result = $command->queryOne();

        $rs['top_user'] = ($result) ? $result : "0";

        if ($result) {
            $img_url = Yii::getAlias('@web') . "/uploads/users/";
            $img_path = $img_url . $result['userimage'];
            $rs['top_user']['userimage'] = $result['userimage'] ? $img_path : "";
        }

        $id_list = $this->getChilds($user_id);
        $today = date('Y-m-d');
        $rs['total_users'] = User::find()->andFilterWhere(['IN', 'id', $id_list])->count();
        $rs['new_users'] = User::find()->andFilterWhere(['IN', 'id', $id_list])->andWhere([ "DATE_FORMAT(created, '%Y-%m-%d')" => $today])->count();

        $users_widget = User::find()->select(['COUNT(id) AS count'])->where(["id" => $id_list])->groupBy(["DATE_FORMAT(created, '%Y-%m-%d')"])->limit(13)->asArray()->all();
        $usersData = array();
        foreach ($users_widget as $count) {
            array_push($usersData, (int) $count['count']);
        }
        $rs['users_widget'] = $usersData;

        $visitors_widget = Visitor::find()->select(['COUNT(id) AS count'])->where(["user_id" => $id_list])->groupBy(["DATE_FORMAT(last_visited, '%Y-%m-%d')"])->asArray()->limit(13)->all();

        $visitorsData = array();
        foreach ($visitors_widget as $count) {
            array_push($visitorsData, (int) $count['count']);
        }
        $rs['visitors_widget'] = $visitorsData;
        $rs['current_month'] = date('F');

        $rs['this_month_users'] = Visitor::find()->andFilterWhere(["user_id" => $id_list])->andWhere([ "DATE_FORMAT(last_visited, '%Y-%m')" => date('Y-m')])->count();


        $id_list = array_merge(array($user_id), $id_list);

        $query = new Query;
        $query
                ->from('visitors')
                ->where(['user_id' => $id_list])
                ->select("COUNT(id)");
        $command = $query->createCommand();
        $visitors = $command->queryScalar();

        $rs['total_visits'] = $visitors;


        //andWhere([ 'and', "end_date>='$today'", "start_date<='$today'"])
        $rs['total_campaigns'] = Campaign::find()->where(["user_id" => $id_list])->andWhere([ 'and', "status='1'", "end_date>='$today'", "start_date<='$today'"])->count();

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    public function actionGetcampaignvisitor() {

        $campaign_name = $_GET['name'];
        $campaign = Campaign::find()->where(['slug' => $campaign_name])->asArray()->one();

        $subQueryVisitors = Visitor::find()
                ->select(['MAX(id) as id'])
                ->where(['campaign_id' => $campaign['id']])
                ->groupBy(['visitor_id'])
                ->asArray()
                ->all();
        $idArray = array_column($subQueryVisitors, 'id');

        if ($_GET['order'][0]['column']) {
            $dir = $_GET['order'][0]['dir'];
            $order = ($_GET['columns'][$_GET['order'][0]['column']]);
        } else {
            $dir = "DESC";
            $order['data'] = "id";
        }
        if (isset($_GET['search'])) {
            $search = $_GET['search']['value'];
        } else {
            $search = '';
        }


        $visitors = Visitor::find()
                ->with('users')
                ->with('campaign')
                ->with('keys')
                ->offset($_GET['start'])
                ->limit($_GET['length'])
                ->orderBy($order['data'] . " " . $dir)
                ->where(['id' => $idArray])
                ->andFilterWhere(['like', 'domain', $search])
                ->andFilterWhere(['like', 'city', $search])
                ->andFilterWhere(['like', 'region', $search])
                ->andFilterWhere(['like', 'zipcode', $search])
                ->andFilterWhere(['like', 'uid', $search])
                ->andFilterWhere(['like', 'ip_address', $search])
                ->asArray()
                ->all();

        $visitors_count = Visitor::find()
                ->where(['id' => $idArray])
                ->asArray()
                ->count();

        $rs = array();
        $i = 0;
        $img_url = '';

        $deviceIconList = array(
            'linux-desktop' => 'linux-desktop.png',
            'mac-desktop' => 'mac-desktop.png',
            'window-desktop' => 'windows-desktop.png',
            'iphone' => 'iphone.gif',
            'ipad' => 'ipad.png',
            'android' => 'android.gif',
            'unknown' => 'unknown.png'
        );
        $referralIconList = array(
            'firefox' => 'firefox.jpg   ',
            'chrome' => 'chrome.png',
            'safari' => 'safari.jpg',
            'ie' => 'ie.png',
            'opera' => 'Opera.png',
            'unknown' => 'unknown.png'
        );

        foreach ($visitors as $visitor) {
            $count = Visitor::find()->where(['visitor_id' => $visitor['visitor_id'], 'campaign_id' => $campaign['id']])->count();
            $rs[$i]['id'] = $visitor['id'];
            $rs[$i]['ip_address'] = $visitor['ip_address'];
            $rs[$i]['lat'] = $visitor['lat'];
            $rs[$i]['lng'] = $visitor['lng'];
            $rs[$i]['last_visit'] = $visitor['last_visited'];
            $rs[$i]['user_id'] = $visitor['user_id'];
            $rs[$i]['visitor_id'] = $visitor['visitor_id'];
            $rs[$i]['os'] = $visitor['os_type'];
            $rs[$i]['device'] = $visitor['device_type'];
            $rs[$i]['browser'] = 'assets/images/' . $referralIconList[$visitor['browser_type']];
            $rs[$i]['visit'] = $count;
            //$rs[$i]['source'] = 'assets/images/direct.png';
            $rs[$i]['squibkey'] = isset($visitor['keys']['key']) ? $visitor['keys']['key'] : 'N/A';
            $rs[$i]['campaign'] = isset($visitor['campaign']['name']) ? $visitor['campaign']['name'] : 'N/A';
            $rs[$i]['last_visited'] = date('g:iA D, M d, Y', strtotime($visitor['last_visited']));
            $rs[$i]['date'] = date('Y-m-d', strtotime($visitor['last_visited']));
            $rs[$i]['domain'] = (!empty($visitor['domain'])) ? $visitor['domain'] : 'N/A';
            $rs[$i]['isp'] = (!empty($visitor['isp'])) ? $visitor['isp'] : 'N/A';
            $rs[$i]['admin_name'] = isset($visitor['users']['admin_name']) ? $visitor['users']['admin_name'] : 'Anonymous';
            $rs[$i]['organization'] = isset($visitor['users']['organization']) ? $visitor['users']['organization'] : 'N/A';

            $rs[$i]['city'] = (empty($visitor['users'])) ? $visitor['city'] : (empty($visitor['users']['city'])) ? $visitor['city'] : $visitor['users']['city'];
            $rs[$i]['region'] = (empty($visitor['users'])) ? $visitor['region'] : (empty($visitor['users']['state'])) ? $visitor['region'] : $visitor['users']['state'];
            $rs[$i]['zipcode'] = (empty($visitor['users'])) ? $visitor['zipcode'] : (empty($visitor['users']['zip'])) ? $visitor['zipcode'] : $visitor['users']['zip'];


            $rs[$i]['gender'] = isset($visitor['users']['sex']) ? $visitor['users']['sex'] : '';

            $rs[$i]['age'] = isset($visitor['users']['dob']) ? date_diff(date_create($visitor['users']['dob']), date_create('today'))->y : '0';
            $img_url = '';
            if (isset($visitor['users']['profile_image']) && !empty($visitor['users']['profile_image'])) {
                $img_url = Yii::getAlias('@web') . "/uploads/users/" . $visitor['users']['profile_image'];
            }
            $rs[$i]['profile_image'] = !empty($img_url) ? $img_url : 'assets/img/avatars/default_user.png';
            if ($visitor['device_type'] == 'desktop') {
                if ($visitor['os_type'] == 'mac') {
                    $rs[$i]['device_icon'] = 'assets/images/' . $deviceIconList['mac-desktop'];
                } else if ($visitor['os_type'] == 'linux') {
                    $rs[$i]['device_icon'] = 'assets/images/' . $deviceIconList['linux-desktop'];
                } else {
                    $rs[$i]['device_icon'] = 'assets/images/' . $deviceIconList['window-desktop'];
                }
            } else {
                $rs[$i]['device_icon'] = 'assets/images/' . $deviceIconList[$visitor['device_type']];
            }
            if ($visitor['source'] == '2') {
                $rs[$i]['source'] = 'assets/images/usb.jpg';
            } else if ($visitor['source'] == '1') {
                $rs[$i]['source'] = 'assets/images/webkey.png';
            } else {
                $rs[$i]['source'] = 'assets/images/direct.png';
            }


            if ($visitor['visitor_id'] == 0) {
                $rs[$i]['uid'] = $visitor['uid'];
            } else {
                $rs[$i]['uid'] = $visitor['users']['uid'];
            }
            
             if ($visitor['visitor_id']) {
                $subQueryHistory = Visitor::find()
                        ->select(['MAX(id) as id'])
                        ->where(['visitor_id' => $visitor['visitor_id'], 'campaign_id' => $campaign['id']])
                        ->groupBy(['domain'])
                        ->asArray()
                        ->all();
            }else{
                   $subQueryHistory = Visitor::find()
                        ->select(['MAX(id) as id'])
                        ->where(['visitor_id' => $visitor['visitor_id'], 'campaign_id' => $campaign['id']])
                        ->groupBy(['domain','uid'])
                        ->asArray()
                        ->all();
            }
            //print_r($subQueryVisitors);exit;
            $historyIdArray = array_column($subQueryHistory, 'id');


            $historyDetails = Visitor::find()
                    ->select(['*'])
                    ->with('users')
                    ->with('campaign')
                    ->with('keys')
                    ->orderBy("id DESC")
                    ->where(['id' => $historyIdArray])
                    ->asArray()
                    ->all();

//            $historyDetails = Visitor::find()
//                    ->select(['*', 'COUNT(visitors.visitor_id) as visit'])
//                    ->with('users')
//                    ->with('campaign')
//                    ->with('keys')
//                    ->groupBy(['domain', 'ip_address'])
//                    ->orderBy("id DESC")
//                    ->where(['visitor_id' => $visitor['visitor_id'], 'campaign_id' => $campaign['id']])
//                    ->asArray()
//                    ->all();

            if (!empty($historyDetails)) {
                $hs = array();
                foreach ($historyDetails as $k => $history) {
                       if($history['visitor_id'])
                    $count = Visitor::find()->where(['visitor_id' => $history['visitor_id'], 'domain' => $history['domain']])->andWhere(['campaign_id' => $campaign['id']])->count();
                    else
                    $count = Visitor::find()->where(['visitor_id' => $history['visitor_id'], 'domain' => $history['domain'], 'uid' => $history['uid']])->andWhere(['campaign_id' => $campaign['id']])->count();

                    $hs[$k]['id'] = $history['id'];
                    $hs[$k]['ip_address'] = $history['ip_address'];
                    $hs[$k]['lat'] = $history['lat'];
                    $hs[$k]['lng'] = $history['lng'];
                    $hs[$k]['last_visit'] = $history['last_visited'];
                    $hs[$k]['user_id'] = $history['user_id'];
                    $hs[$k]['visitor_id'] = $history['visitor_id'];
                    $hs[$k]['os'] = $history['os_type'];
                    $hs[$k]['device'] = $history['device_type'];
                    $hs[$k]['browser'] = 'assets/images/' . $referralIconList[$history['browser_type']];
                    //$hs[$k]['source'] = 'assets/images/direct.png';
                    $hs[$k]['visit'] = $count;
                    $hs[$k]['squibkey'] = isset($history['keys']['key']) ? $history['keys']['key'] : 'N/A';
                    $hs[$k]['campaign'] = isset($history['campaign']['name']) ? $history['campaign']['name'] : 'N/A';
                    $hs[$k]['last_visited'] = date('g:iA D, M d, Y', strtotime($history['last_visited']));
                    $hs[$k]['date'] = date('Y-m-d', strtotime($history['last_visited']));
                    $hs[$k]['domain'] = (!empty($history['domain'])) ? $history['domain'] : 'N/A';
                    $hs[$k]['isp'] = (!empty($history['isp'])) ? $history['isp'] : 'N/A';
                    $hs[$k]['admin_name'] = isset($history['users']['admin_name']) ? $history['users']['admin_name'] : 'Anonymous';
                    $hs[$k]['organization'] = isset($history['users']['organization']) ? $history['users']['organization'] : 'N/A';
                    $hs[$k]['gender'] = isset($history['users']['sex']) ? $history['users']['sex'] : '';
                    $city = (empty($history['users'])) ? $history['city'] : (empty($history['users']['city'])) ? $history['city'] : $history['users']['city'];
                    $hs[$k]['city'] = (empty($city)) ? '' : $city;
                    $region = (empty($history['users'])) ? $history['region'] : (empty($history['users']['state'])) ? $history['region'] : $history['users']['state'];
                    $hs[$k]['region'] = (empty($region)) ? '' : $region;
                    $zipcode = (empty($history['users'])) ? $history['zipcode'] : (empty($history['users']['zip'])) ? $history['zipcode'] : $history['users']['zip'];
                    $hs[$k]['zipcode'] = (empty($zipcode)) ? '' : $zipcode;
                    $hs[$k]['age'] = isset($history['users']['dob']) ? date_diff(date_create($history['users']['dob']), date_create('today'))->y : '0';
                    $img_url = '';
                    if (isset($history['users']['profile_image']) && !empty($history['users']['profile_image'])) {
                        $img_url = Yii::getAlias('@web') . "/uploads/users/" . $history['users']['profile_image'];
                    }
                    $hs[$k]['profile_image'] = !empty($img_url) ? $img_url : 'assets/img/avatars/default_user.png';
                    if ($history['device_type'] == 'desktop') {
                        if ($history['os_type'] == 'mac') {
                            $hs[$k]['device_icon'] = 'assets/images/' . $deviceIconList['mac-desktop'];
                        } else if ($history['os_type'] == 'linux') {
                            $hs[$k]['device_icon'] = 'assets/images/' . $deviceIconList['linux-desktop'];
                        } else {
                            $hs[$k]['device_icon'] = 'assets/images/' . $deviceIconList['window-desktop'];
                        }
                    } else {
                        $hs[$k]['device_icon'] = 'assets/images/' . $deviceIconList[$history['device_type']];
                    }
                    if ($history['source'] == '2') {
                        $hs[$k]['source'] = 'assets/images/usb.jpg';
                    } else if ($history['source'] == '1') {
                        $hs[$k]['source'] = 'assets/images/webkey.png';
                    } else {
                        $hs[$k]['source'] = 'assets/images/direct.png';
                    }

                    if ($history['visitor_id'] == 0) {
                        $hs[$k]['uid'] = $history['uid'];
                    } else {
                        $hs[$k]['uid'] = $visitor['users']['uid'];
                    }
                }
                $rs[$i]['details'] = $hs;
            }
            $i++;
        }

        /*
         * Output
         */
        $response = array(
            "draw" => isset($_GET['draw']) ?
                    intval($_GET['draw']) :
                    0,
            "recordsTotal" => $visitors_count,
            "recordsFiltered" => $visitors_count,
            "data" => $rs
        );
        echo json_encode($response);
        exit;

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'aaData' => $rs, 'campaign_name' => $campaign['name'], JSON_PRETTY_PRINT));
    }

    public function actionAddfolder() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {
            $query = new Query;
            $query
                    ->from('instance_folder')
                    ->where(['name' => $request->folder_name, 'user_id' => $request->user_id, 'parent_id' => $request->parent_id, 'master' => $request->master])
                    ->select("id");

            $command = $query->createCommand();
            $folder_exist = $command->queryAll();
            if ($folder_exist) {
                $status = 0;
                $rs = 'Folder Already Exist';
            } else {
                $create['name'] = $request->folder_name;
                $create['parent_id'] = $request->parent_id;
                $create['user_id'] = $request->user_id;
                $create['master'] = $request->master;
                Yii::$app->db->createCommand()->insert('instance_folder', $create)->execute();
                $status = 1;
                $rs = 'Folder Created.';
            }
        }

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Dashboard Visits List API.
     * @return mixed
     */
    public function actionGetdashboardvisits() {

        $user_id = $_GET['user_id'];

        $user = User::findOne([
                    'id' => $user_id,
        ]);

        $users = User::find()
                ->all();
// Current timestamp is assumed, so these find first and last day of THIS month
        $last_sunday = date('Y-m-d 23:59:59', strtotime('last sunday')); // hard-coded '01' for first day

        $visitsData = array();
        $last_week = 0;
        for ($i = 7; $i >= 0; $i--) {

            $date = date("Y-m-d", strtotime($i . " days ago", strtotime($last_sunday)));

            $count = Visitor::find()->where(["user_id" => $user_id])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $date])->count();
            array_push($visitsData, (int) $count);
            $last_week+=$count;
        }

        $yesterday = date('Y-m-d', strtotime('yesterday')); // hard-coded '01' for first day




        $rs['visitsData'] = $visitsData;
        $rs['currentDate'] = date('d F');
        $rs['totalVisit'] = Visitor::find()->where(["user_id" => $user_id])->count();
        $rs['lastWeekVisit'] = $last_week;
        $rs['yesterdayVisit'] = Visitor::find()->where(["user_id" => $user_id])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", $yesterday])->count();

        $rs['todayVisit'] = Visitor::find()->where(["user_id" => $user_id])->andWhere(["=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d')])->count();




        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Dashboard Visits List API.
     * @return mixed
     */
    public function actionDownloadzip() {

        ini_set('max_execution_time', 5 * 60);
        $folder_id = $_GET['folder_id'];
        $root_path = Yii::getAlias('@webroot');

        $query = new Query;
        $query
                ->from('instance_folder')
                ->where([ 'id' => $folder_id])
                ->select("id");

        $command = $query->createCommand();
        $folder_exist = $command->queryOne();

        if ($folder_exist) {
            $query = new Query;
            $query
                    ->from('instance_files')
                    ->where(['folder_id' => $folder_id])
                    ->orderBy('orgnl_file_name ASC')
                    ->select("file_id,orgnl_file_name,savd_file_name,user_id,master");

            $command = $query->createCommand();
            $files = $command->queryAll();

            if ($files) {
                foreach ($files as $file) {
                    if ($file['master']) {
                        $path = $root_path . "/uploads/instances/" . $file['user_id'] . "/master/" . $file['savd_file_name'];
                    } else {
                        $path = $root_path . "/uploads/instances/" . $file['user_id'] . "/" . $file['savd_file_name'];
                    }
                    $files_to_zip[] = $path;
                }
                $zip_name = 'download-' . $folder_id . '-' . time() . '.zip';
                $zip_path = $root_path . "/uploads/instances/" . $file['user_id'] . "/" . $zip_name;
                //if true, good; if false, zip creation failed
                $result = $this->create_zip($files_to_zip, $zip_path);

                if ($result) {
                    $status = 1;
                    header('Content-Disposition: attachment; filename=' . $zip_name);
                    header('Content-Tpe: application/zip');
                    if (file_exists($zip_path)) {
                        Yii::$app->response->sendFile($zip_path);
                        //unlink('D:/xampp/htdocs/squib_hub/yii/web/uploads/instances/2/download-15-1464945243.zip');                       
                    }
                } else {
                    $status = 0;
                    $data = "Zip not created.";
                }
            } else {
                $status = 0;
                $data = "Folder is empty.";
            }
        } else {
            $status = 0;
            $data = "Folder not exist.";
        }

        if ($status == 0) {
            $this->setHeader(200);
            return json_encode(array('status' => $status, 'data' => $data, JSON_PRETTY_PRINT));
        } else {
            // $this->setHeader(200);
            ignore_user_abort(true);
            if (connection_aborted()) {
                unlink($zip_path);
            }
            //unlink($zip_path);
            // return json_encode(array('status' => $status, 'data' => $file['user_id'] . "/" . $zip_name, JSON_PRETTY_PRINT));
        }
    }

    public function actionDownloadnow() {

        $path = Yii::getAlias('@webroot') . '/uploads';

        $file = $path . '/instances/1/download-1-1464943170.zip';
//        $zip = $_GET['zip'];
//        $root_path = Yii::getAlias('@webroot');
//        $zip_path = $root_path . "/uploads/instances/" . $zip;
        if (file_exists($file))
            Yii::$app->response->sendFile($file);
    }

    public function actionRemovezip() {

        $zip = $_GET['zip'];
        $root_path = Yii::getAlias('@webroot');
        $zip_path = $root_path . "/uploads/instances/" . $zip;
        if (file_exists($zip_path))
            unlink($zip_path);
    }

    /**
     * Cloud Drive Files API.
     * @return mixed
     */
    public function actionGetdrive() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $type = $request->type;
        $user_id = $request->user_id;
        $item_id = $request->item_id;
        $item_name = $request->item_name;

        $rs = array();

        $i = 0;
        if ($type == 'folder' && $item_id) {
            $query = new Query;
            $query
                    ->from('instance_files')
                    ->where(['user_id' => $user_id, 'folder_id' => $item_id])
                    ->orderBy('orgnl_file_name ASC')
                    ->select("file_id,orgnl_file_name,savd_file_name,file_type,master,user_id");

            $command = $query->createCommand();
            $models = $command->queryAll();
            $rs2 = array();
            if ($models) {

                foreach ($models as $file) {
                    $orgnl_name = $file['orgnl_file_name'];

                    if ($file['master']) {
                        $file_path = $file['user_id'] . "/master/" . $file['savd_file_name'];
                        $root_path = $this->getWebRootPath($file['user_id']) . "master/";
                    } else {
                        $file_path = $file['user_id'] . "/" . $file['savd_file_name'];
                        $root_path = $this->getWebRootPath($file['user_id']);
                    }

                    $file_length = strlen($orgnl_name);
                    $reduced_name = substr($orgnl_name, 0, 13);
                    if ($file_length > 13) {
                        $dip_name = $reduced_name . "...";
                    } else {
                        $dip_name = $reduced_name;
                    }
                    $img_path = '';
                    if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                        $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                        $img_path = $img_url . $file_path;
                    } else {

                        $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                        $file_type = $file["file_type"];
                        $img_name = "";
// Open a directory, and read its contents
                        if (is_dir($dir)) {
                            if ($dh = opendir($dir)) {
                                while (($filename = readdir($dh)) !== false) {
                                    $explode_file = explode(".", $filename);
                                    $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                                    if (strcasecmp($file_type, $file_type_all) == 0) {
                                        $foldr_img = $filename;
                                        $img_url = "assets/img/avatars/";
                                        $img_path = $img_url . $foldr_img;
                                        break;
                                    } else {
                                        $foldr_img = "DOC.png";
                                        $img_url = "assets/img/avatars/";
                                        $img_path = $img_url . $foldr_img;
                                    }
                                }
                                closedir($dh);
                            }
                        }
                    }

                    $file_type_no = "";

                    if ($file['file_type'] == "mp4") {
                        $file_type_no = 1;
                        $view_path = $root_path . $file['savd_file_name'];
                    } elseif ($file['file_type'] == "mp3") {
                        $file_type_no = 2;
                        $view_path = $root_path . $file['savd_file_name'];
                    } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                        $file_type_no = 3;
                        $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
                    } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                        $file_type_no = 4;
                        $view_path = $root_path . $file['savd_file_name'];
                    } else {
                        $file_type_no = 5;
                        $view_path = $root_path . $file['savd_file_name'];
                    }

                    $rs[$i]['file_id'] = $file['file_id'];
                    $rs[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
                    $rs[$i]['reduced_name'] = $dip_name;
                    $rs[$i]['file_type'] = $file['file_type'];
                    $rs[$i]['img_path'] = $img_path;
                    $rs[$i]['file_path'] = $file_path;
                    $rs[$i]['file_type_no'] = $file_type_no;
                    $rs[$i]['view_path'] = $view_path;
                    $rs[$i]['master'] = $file['master'];
                    $rs[$i++]['savd_file_name'] = $file["savd_file_name"];
                }
            }
        } else if ($type == 'file' && $item_id) {
            $query = new Query;
            $query
                    ->from('instance_files')
                    ->where(['user_id' => $user_id, 'file_id' => $item_id])
                    ->orderBy('orgnl_file_name ASC')
                    ->select("file_id,orgnl_file_name,savd_file_name,file_type,master,user_id");

            $command = $query->createCommand();
            $file = $command->queryOne();
            if ($file) {

                $orgnl_name = $file['orgnl_file_name'];

                if ($file['master']) {
                    $file_path = $file['user_id'] . "/master/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($file['user_id']) . "master/";
                } else {
                    $file_path = $file['user_id'] . "/" . $file['savd_file_name'];
                    $root_path = $this->getWebRootPath($file['user_id']);
                }

                $file_length = strlen($orgnl_name);
                $reduced_name = substr($orgnl_name, 0, 13);
                if ($file_length > 13) {
                    $dip_name = $reduced_name . "...";
                } else {
                    $dip_name = $reduced_name;
                }
                $img_path = '';
                if (($file["file_type"] == "jpg") || ($file["file_type"] == "png") || ($file["file_type"] == "PNG") || ($file["file_type"] == "jpeg") || ($file["file_type"] == "JPEG")) {
                    $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                    $img_path = $img_url . $file_path;
                } else {

                    $dir = $_SERVER['DOCUMENT_ROOT'] . "/squib_hub/assets/img/avatars";
                    $file_type = $file["file_type"];
                    $img_name = "";
// Open a directory, and read its contents
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($filename = readdir($dh)) !== false) {
                                $explode_file = explode(".", $filename);
                                $file_type_all = $explode_file[0];
//$same_file = strcasecmp($file_type,$file_type_all);
                                if (strcasecmp($file_type, $file_type_all) == 0) {
                                    $foldr_img = $filename;
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                    break;
                                } else {
                                    $foldr_img = "DOC.png";
                                    $img_url = "assets/img/avatars/";
                                    $img_path = $img_url . $foldr_img;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }

                $file_type_no = "";

                if ($file['file_type'] == "mp4") {
                    $file_type_no = 1;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif ($file['file_type'] == "mp3") {
                    $file_type_no = 2;
                    $view_path = $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "pdf") || ($file['file_type'] == "txt")) {
                    $file_type_no = 3;
                    $view_path = ($file['file_type'] == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $root_path . $file['savd_file_name'] : $root_path . $file['savd_file_name'];
                } elseif (($file['file_type'] == "jpg") || ($file['file_type'] == "png") || ($file['file_type'] == "PNG") || ($file['file_type'] == "jpeg") || ($file['file_type'] == "JPEG")) {
                    $file_type_no = 4;
                    $view_path = $root_path . $file['savd_file_name'];
                } else {
                    $file_type_no = 5;
                    $view_path = $root_path . $file['savd_file_name'];
                }

                $rs[$i]['file_id'] = $file['file_id'];
                $rs[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
                $rs[$i]['reduced_name'] = $dip_name;
                $rs[$i]['file_type'] = $file['file_type'];
                $rs[$i]['img_path'] = $img_path;
                $rs[$i]['file_path'] = $file_path;
                $rs[$i]['file_type_no'] = $file_type_no;
                $rs[$i]['view_path'] = $view_path;
                $rs[$i]['master'] = $file['master'];
                $rs[$i++]['savd_file_name'] = $file["savd_file_name"];
            }
        }
        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
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
