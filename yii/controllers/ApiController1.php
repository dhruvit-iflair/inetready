<?php

namespace app\controllers;

use yii\web\Session;
use Yii;
use app\models\User;
use app\models\Visitor;
use app\models\Campaign;
use app\models\DeleteLogs;
use app\models\Comment;
use app\models\Share;
use app\models\Like;
use app\models\CampaignKeys;
use app\models\InstanceFiles;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\web\UploadedFile;
use GeoIp2\Database\Reader;

class ApiController extends Controller {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'login' => ['post'],
                    'forgotpassword' => ['get'],
                    'resetpassword' => ['post'],
                    'confirmpassword' => ['post'],
                    'users' => ['get'],
                    'getsubdomain' => ['post'],
                    'getuser' => ['post'],
                    'updateuser' => ['post'],
                    'createuser' => ['post'],
                    'deleteuser' => ['get'],
                    'cloudinstancefiles' => ['post'],
                    'filesupload' => ['post'],
                    'cloudfilelist' => ['post'],
                    'imagedelete' => ['get'],
                    'modulepermissions' => ['get'],
                    'setpermissions' => ['post'],
                    'getsquibmodules' => ['get'],
                    'userpermissions' => ['get'],
                    'setuserpermissions' => ['post'],
                    'deleteuserpermission' => ['get'],
                    'userall' => ['get'],
                    'getcountries' => ['get'],
                    'getsquibcards' => ['get'],
                    'socialnetworks' => ['get'],
                    'getnameservers' => ['get'],
                    'savenameservers' => ['post'],
                    'uploadphoto' => ['post'],
                    'private_branding' => ['post'],
                    'getipaddress' => ['post'],
                    'getlatlng' => ['post'],
                    'savevisitor' => ['post'],
                    'getvisitor' => ['get'],
                    'download' => ['get'],
                    'getvisitorcount' => ['post'],
                    'getdevicetype' => ['get'],
                    'getvisitorfortable' => ['get'],
                    'savecampaign' => ['post'],
                    'getcampaignlist' => ['get'],
                    'getarchivedcampaignlist' => ['get'],
                    'getallcampaignlist' => ['get'],
                    'getactivecampaignlist' => ['get'],
                    'deletecampaign' => ['get'],
                    'getcampaignbyid' => ['post'],
                    'updatecampaign' => ['post'],
                    'checkkeyrange' => ['post'],
                    'getcampaigndetail' => ['get'],
                    'check_campaign_activate' => ['post'],
                    'add_visitor' => ['post'],
                    'get_social_statistics' => ['post'],
                    'clear_campaign' => ['post'],
                    'getclearedcampaigndata' => ['get'],
                    'getsquibkeydata' => ['get'],
                    'getsquibcarddata' => ['get'],
                    'get_campaign_name' => ['get'],
                    'sitepreferences' => ['get'],
                    'getcampaignkeys' => ['get'],
                    'removebrand' => ['get'],
                    'usermodulepermission' => ['get'],
                    'campaignbrand' => ['get'],
                ],
            ]
        ];
    }

    /* Function called before any webservice to check valid access token */

    public function beforeAction($event) {
        $headers = Yii::$app->request->headers;
        $action = yii::$app->controller->id . "/" . yii::$app->controller->action->id;
        $actionList = array('api/login', 'api/forgotpassword', 'api/resetpassword', 'api/confirmpassword', 'api/getsubdomain', 'api/savevisitor', 'api/download', 'api/getlatlng', 'api/getcampaignbyname', 'api/check_campaign_activate', 'api/add_visitor', 'api/getallcampaignlist', 'api/getclearedcampaigndata', 'api/getvisitorfortable', 'api/get_campaign_name', 'api/get_data', 'api/getcampaigndetail', 'api/sitepreferences', 'api/campaignbrand');

        if (!in_array($action, $actionList)) {
            $auth = explode(" ", $headers['authorization']);
            $encrypted_string = base64_decode($auth[1]);
            $iv = base64_decode($auth[2]);
            $secret_key = 'valid_secret_key';
            $decrypted_string = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret_key, $encrypted_string, MCRYPT_MODE_CBC, $iv);
            $result = json_decode(trim($decrypted_string));
            if (!is_object($result) || time() > trim($result->expireTime) || trim($result->expireTime) == '') {
                echo json_encode(array('status' => 0, 'message' => 'logout'), JSON_PRETTY_PRINT);
                die;
            } //else{ echo 'login';}
        }
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

    /* Function to print any test data (Debugging Only) */

    public function dump($data) {
        if (is_object($data)) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else if (is_array($data)) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo $data;
        }
    }

    /* Some Common Fuctions used to check access token and format response */

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
     * Login API.
     * @return mixed
     */
    public function actionLogin() {
        $postdata = file_get_contents("php://input");
        //$_REQUEST = '{"emailid1":"arnavdots@dotsquares.com","password1":"Hello@123"}';
        $request = json_decode($postdata);

        $encrypted_string = $iv = '';
        $emailid1 = $request->emailid1;
        $password1 = $request->password1;
        $user_status = 0;

        $user = User::findOne([
                    'email_id' => $emailid1,
        ]);

        if ($user) {
            $hash = $user->attributes['passwd'];
            if (Yii::$app->getSecurity()->validatePassword($password1, $hash)) {
                // all good, logging user in

                if ($user->user_status == 0) {

                    $userId = $user->id;
                    $expireTime = strtotime('+2 days');
                    $jsonArray = json_encode(array('userID' => trim($userId), 'expireTime' => trim($expireTime)));
                    // Create the initialization vector for added security.
                    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
                    $encrypted_string = $this->encrptString($jsonArray, $iv);

                    $img_url = Yii::getAlias('@web') . "/uploads/";
                    $img_path = $img_url . "users/" . $user->profile_image;

                    $data['encrypted_string'] = base64_encode($encrypted_string);
                    $data['iv'] = base64_encode($iv);
                    $data['user_id'] = $user->id;
                    $data['user_role'] = $user->role;
                    $data['user_name'] = $user->admin_name;
                    $data['profile_image'] = $user->profile_image ? $img_path : "";
                    $data['instance_url'] = $user->instance_url;

                    $query = new Query;
                    $query
                            ->from('private_branding')
                            ->where(['user_id' => $user->id])
                            ->select("*");

                    $command = $query->createCommand();
                    $brand = $command->queryOne();

                    $data['site_name'] = $brand['site_name'] ? $brand['site_name'] : Yii::$app->params['SiteName'];
                    $data['logo'] = $brand['logo'] ? $img_url . "private_brand/" . $brand['logo'] : "";
                    $data['favicon'] = $brand['favicon'] ? $img_url . "private_brand/" . $brand['favicon'] : "";

                    $user_log['uid'] = $userId;
                    $user_log['ip_address'] = $request->ip_address;
                    Yii::$app->db->createCommand()->insert('user_login_history', $user_log)->execute();

                    if ($user->first_login == 0) {
                        $user->first_login = 1;
                        if (empty($user->uid)) {
                            $user->uid = Yii::$app->commoncomponent->generateUserID();
                        }
                        $user->update();
                    }


                    $data['uid'] = (string) $user->uid;
                    $response = $data;
                    $status = 1;
                    $visitors = Visitor::find()->where(['uid' => $user->uid])->asArray()->all();
                    if (!empty($visitors)) {
                        foreach ($visitors as $k => $v) {
                            $id = $v['id'];
                            $update['visitor_id'] = $user->id;
                            $update['uid'] = 0;
                            Yii::$app->db->createCommand()->update('visitors', $update, "id = $id")->execute();
                        }
                    }

                    // ANNONYMOUS USER CHANGED TO THE LOGGED IN USER
                    if (isset($request->prevUID)) {
                        $annonymous = Visitor::find()->where(['uid' => $request->prevUID])->asArray()->all();
                        if (!empty($annonymous)) {
                            foreach ($annonymous as $k => $v) {
                                $id = $v['id'];
                                $annonymous_user['visitor_id'] = $user->id;
                                $annonymous_user['uid'] = 0;
                                Yii::$app->db->createCommand()->update('visitors', $annonymous_user, "id = $id")->execute();
                            }
                        }
                    }
                } else {
                    $response = 'Your account is not active.';
                    $status = 0;
                }
            } else {
                // wrong password
                $response = 'Invalid Email/Password.';
                $status = 0;
            }
        } else {
            $response = 'No User Found';
            $status = 0;
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Forgot password API.
     * @return mixed
     */
    public function actionForgotpassword() {
        $email = $_GET ['email'];

        $user = User::findOne([
                    'email_id' => $email,
        ]);

        if ($user) {
            $link = Yii::$app->params['HTTP_URL'] . "/resetPassword/" . $user->id . "/" . md5($user->id) . "/" . $email . "/" . md5($email);
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($user->id);
            Yii::$app->mailer->compose('reset_password', [
                        'link' => $link,
                    ])
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo($email)
                    ->setSubject('Reset Password on ' . Yii::$app->params['SiteName'])
                    ->send();

            $user->for_pwd_status = 1;
            $user->update();

            $status = 1;
            $response = 'Please check your email to reset your password.';
        } else {
            $status = 0;
            $response = 'No User Found';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Reset password API.
     * @return mixed
     */
    public function actionResetpassword() {
        $postdata = file_get_contents("php://input");
        //$_REQUEST = '{"emailid1":"arnavdots@dotsquares.com","password1":"Hello@123"}';
        $request = json_decode($postdata);

        if (md5($request->par1) == $request->par2 && md5($request->par3) == $request->par4) {

            $user = User::findOne([
                        'id' => $request->par1,
                        'email_id' => $request->par3
            ]);

            if ($user && $user->for_pwd_status == 0) {
                $status = 0;
                $response = 'You have already used this URL to reset your password.';
            } else {

                $status = 1;
                $response = 'Please reset your password.';
            }
        } else {
            $status = 0;
            $response = 'The url is invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Confirm password API.
     * @return mixed
     */
    public function actionConfirmpassword() {
        $postdata = file_get_contents("php://input");
        //$_REQUEST = '{"emailid1":"arnavdots@dotsquares.com","password1":"Hello@123"}';
        $request = json_decode($postdata);

        if (md5($request->par1) == $request->par2 && md5($request->par3) == $request->par4) {

            $user = User::findOne([
                        'id' => $request->par1,
                        'email_id' => $request->par3
            ]);


            $user->for_pwd_status = 0;
            $user->passwd = Yii::$app->getSecurity()->generatePasswordHash($request->password);


            if ($user->update() !== false) {
                // update successful
                $status = 1;
                $response = 'Password Updated.';
            } else {
                // update failed
                $status = 0;
                $response = 'URL invalid.';
            }
        } else {
            $status = 0;
            $response = 'The url is invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Country List API.
     * @return mixed
     */
    public function actionGetcountries() {
        $query = new Query;
        $query
                ->from('country')
                ->select("code, name");

        $command = $query->createCommand();
        $models = $command->queryAll();

        $this->setHeader(200);

        // echo json_encode($models, JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $models, JSON_PRETTY_PRINT));
    }

    /**
     * Social Networks List API.
     * @return mixed
     */
    public function actionSocialnetworks() {
        $query = new Query;
        $query
                ->from('social_networks')
                ->select("id, name");

        $command = $query->createCommand();
        $models = $command->queryAll();

        $this->setHeader(200);

        // echo json_encode($models, JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $models, JSON_PRETTY_PRINT));
    }

    /**
     * Get Name Servers API.
     * @return mixed
     */
    public function actionGetnameservers() {
        $user_id = $_GET['user_id'];
        $query = new Query;
        $query
                ->from('name_servers')
                ->where(['user_id' => $user_id])
                ->select("id, name_server, ip_address");

        $command = $query->createCommand();
        $models = $command->queryAll();

        $this->setHeader(200);

        // echo json_encode($models, JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $models, JSON_PRETTY_PRINT));
    }

    /**
     * Save Name Servers API.
     * @return mixed
     */
    public function actionSavenameservers() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);


        if (isset($request->id)) {
            $update['name_server'] = $request->name_server;
            $update['ip_address'] = $request->ip_address;
            Yii::$app->db->createCommand()->update('name_servers', $update, 'id =' . $request->id)->execute();
            $status = 1;
            $response = 'Updated Successful';
        } else if (isset($request->name_server)) {
            $create['user_id'] = $request->user_id;
            $create['name_server'] = $request->name_server;
            $create['ip_address'] = $request->ip_address;
            Yii::$app->db->createCommand()->insert('name_servers', $create)->execute();
            $status = 1;
            $response = 'Name Server Added.';
        } else {
            $status = 0;
            $response = 'Error Occured';
        }

        $this->setHeader(200);

        // echo json_encode($models, JSON_PRETTY_PRINT);
        return json_encode(array('status' => $status, 'data' => $response, JSON_PRETTY_PRINT));
    }

    /**
     * Cloud Instance Files API.
     * @return mixed
     */
    public function actionCloudinstancefiles() {

        $session = Yii::$app->session;
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $cinstence_id = $request->cinstence_id;
        $session['cinstence_id'] = $cinstence_id;

        $query = new Query;
        $query
                ->from('instance_files')
                ->where([ 'user_id' => $cinstence_id])
                ->orderBy('orgnl_file_name ASC')
                ->select("file_id,orgnl_file_name,savd_file_name,file_type");

        $command = $query->createCommand();
        $models = $command->queryAll();

        $rs = array();
        $i = 0;
        foreach ($models as $file) {
            $orgnl_name = $file['orgnl_file_name'];
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
                $img_path = $img_url . $cinstence_id . "/" . $file['savd_file_name'];
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

            $rs[$i]['file_id'] = $file['file_id'];
            $rs[$i]['orgnl_file_name'] = $file['orgnl_file_name'];
            $rs[$i]['reduced_name'] = $dip_name;
            $rs[$i]['file_type'] = $file['file_type'];
            $rs[$i]['img_path'] = $img_path;
            $rs[$i++]['savd_file_name'] = $file["savd_file_name"];
        }

        $this->setHeader(200);

        // echo json_encode(array('records' => $rs), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Cloud Instance File Upload API.
     * @return mixed
     */
    public function actionFilesupload() {
        if (!empty($_FILES)) {
            $session = Yii::$app->session;
            $cinstence_id = $session['cinstence_id'];

            $tempPath = $_FILES['file']['tmp_name'];
            $orgnl_file_name = $_FILES["file"]["name"];
            $timestamp = time();
            $file_type1 = basename($_FILES["file"]["name"]);
            $file_type = pathinfo($file_type1, PATHINFO_EXTENSION);
            $randno = rand(1000, 999999);
            $savd_file_name = $cinstence_id . "_" . $timestamp . "_" . $randno . "." . $file_type;
            $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instances' . DIRECTORY_SEPARATOR . $cinstence_id . DIRECTORY_SEPARATOR . $savd_file_name;


            if (move_uploaded_file($tempPath, $uploadPath)) {
                ///insert details in to database
                $created_date = date("Y-m-d");
                $file_id = "";
                $model = new InstanceFiles();
                $model->user_id = $cinstence_id;
                $model->orgnl_file_name = $orgnl_file_name;
                $model->savd_file_name = $savd_file_name;
                $model->file_type = $file_type;
                $model->created_date = $created_date;
                $model->save();
                ///    
                $answer = array('answer' => 'File transfer completed');
                $json = json_encode($answer);

                echo $json;
            }
        } else {
            echo 'No files';
        }
    }

    /**
     * Get Document Root Path of a instance (protected function).
     * @return string
     */
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

    /**
     * Cloud Instance Files List API.
     * @return mixed
     */
    public function actionCloudfilelist() {
        $session = Yii::$app->session;
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $result_arr = array();
        //////Respective file preview
        $fileid = $request->fileid;
        $cinstence_id = $session['cinstence_id'];

        $result = InstanceFiles::find()->where([ 'file_id' => $fileid])->orderBy([ 'orgnl_file_name' => SORT_ASC])->One();
        /* $result = $conn->query("SELECT 	file_id, orgnl_file_name,savd_file_name,file_type FROM instance_files where  file_id=$fileid order by orgnl_file_name ASC");
          $rs = $result->fetch_array(MYSQLI_ASSOC); */
        $file_type_no = "";

        if ($result->file_type == "mp4") {
            $file_type_no = 1;
            $path = $this->getWebRootPath($cinstence_id);
            $img_path = $path . $result->savd_file_name;
        } elseif ($result->file_type == "mp3") {
            $file_type_no = 2;
            $path = $this->getWebRootPath($cinstence_id);
            $img_path = $path . $result->savd_file_name;
        } elseif (($result->file_type == "pdf") || ($result->file_type == "txt")) {
            $file_type_no = 3;
            $path = $this->getWebRootPath($cinstence_id);
            $img_path = ($result->file_type == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $path . $result->savd_file_name : $path . $result->savd_file_name;
        } elseif (($result->file_type == "jpg") || ($result->file_type == "png") || ($result->file_type == "PNG") || ($result->file_type == "jpeg") || ($result->file_type == "JPEG")) {
            $file_type_no = 4;
            $img_url = Yii::getAlias('@web') . "/uploads/instances/";
            $img_path = $img_url . $cinstence_id . "/" . $result->savd_file_name;
        } else {
            $file_type_no = 5;
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
            $file_type = $result->file_type;
            $img_name = "";
            // Open a directory, and read its contents
            if (is_dir($dir)) {

                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {

                        $explode_file = explode(".", $file);
                        $file_type_all = $explode_file[0];

                        if (strcasecmp($file_type, $file_type_all) == 0) {
                            $foldr_img = $file;
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


        $result_arr[0]['file_id'] = $result->file_id;
        $result_arr[0]['orgnl_file_name'] = $result->orgnl_file_name;
        $result_arr[0]['file_type'] = $result->file_type;
        $result_arr[0]['file_type_no'] = $file_type_no;
        $result_arr[0]['img_path'] = $img_path;
        $result_arr[0]['savd_file_name'] = $result->savd_file_name;

        //////////////////////////////

        $results = InstanceFiles::find()->where('user_id = :cinstence_id AND file_id != :file_id', [':cinstence_id' => $cinstence_id, ':file_id' => $fileid])->orderBy([ 'orgnl_file_name' => SORT_ASC])->all();
        $i = 1;
        foreach ($results as $key => $result) {

            if ($result->file_type == "mp4") {
                $file_type_no = 1;
                $path = $this->getWebRootPath($cinstence_id);
                $img_path = $path . $result->savd_file_name;
            } elseif ($result->file_type == "mp3") {
                $file_type_no = 2;
                $path = $this->getWebRootPath($cinstence_id);
                $img_path = $path . $result->savd_file_name;
            } elseif (($result->file_type == "pdf") || ($result->file_type == "txt")) {
                $file_type_no = 3;
                $path = $this->getWebRootPath($cinstence_id);
                $img_path = ($result->file_type == "pdf") ? "https://drive.google.com/viewerng/viewer?embedded=true&url=" . $path . $result->savd_file_name : $path . $result->savd_file_name;
            } elseif (($result->file_type == "jpg") || ($result->file_type == "png") || ($result->file_type == "PNG") || ($result->file_type == "jpeg") || ($result->file_type == "JPEG")) {
                $file_type_no = 4;
                $img_url = Yii::getAlias('@web') . "/uploads/instances/";
                $img_path = $img_url . $cinstence_id . "/" . $result->savd_file_name;
            } else {
                $file_type_no = 5;
                $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/avatars";
                $file_type = $result->file_type;
                $img_name = "";
                // Open a directory, and read its contents
                if (is_dir($dir)) {
                    if ($dh = opendir($dir)) {
                        while (($file = readdir($dh)) !== false) {
                            $explode_file = explode(".", $file);
                            $file_type_all = $explode_file[0];
                            //$same_file = strcasecmp($file_type,$file_type_all);
                            if (strcasecmp($file_type, $file_type_all) == 0) {
                                $foldr_img = $file;
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

            $result_arr[$i]['file_id'] = $result->file_id;
            $result_arr[$i]['orgnl_file_name'] = $result->orgnl_file_name;
            $result_arr[$i]['file_type'] = $result->file_type;
            $result_arr[$i]['file_type_no'] = $file_type_no;
            $result_arr[$i]['img_path'] = $img_path;
            $result_arr[$i++]['savd_file_name'] = $result->savd_file_name;
        }
        //echo($outp);
        return json_encode(array('status' => 1, 'data' => $result_arr, JSON_PRETTY_PRINT));
    }

    /**
     * Cloud Image Delete API.
     * @return mixed
     */
    public function actionImagedelete(/* $instanceid, $filename, $fileid */) {
        $instanceid = $_GET ['instanceid'];

        $filename = $_GET ['filename'];

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
            //echo $path;exit;
            unlink($path);
            // echo 'correct';
            return json_encode(array('status' => 1, 'data' => 'correct', JSON_PRETTY_PRINT));
        } else {
            // echo 'Not correct';
            return json_encode(array('status' => 1, 'data' => 'Not correct', JSON_PRETTY_PRINT));
        }
    }

    /**
     * Module Permissions Settings API.
     * @return mixed
     */
    public function actionModulepermissions() {

        $query = new Query;
        $query
                ->from('squib_module')
                ->where(['status' => 1])
                ->orderBy('id ASC')
                ->select("id,name");

        $command = $query->createCommand();
        $modules = $command->queryAll();
        $module_permissions = array();
        $i = 0;
        foreach ($modules as $module) {
            $query = new Query;
            $query
                    ->from('module_permissions')
                    ->where(['module_id' => $module['id']])
                    ->select("status,role");

            $command = $query->createCommand();
            $permissions = $command->queryAll();
            //print_r($permissions);exit;

            $module_permissions[$i]['module_id'] = $module['id'];
            $module_permissions[$i]['module'] = $module['name'];
            $module_permissions[$i]['admin'] = false;
            $module_permissions[$i]['reseller'] = false;
            $module_permissions[$i]['client'] = false;
            $module_permissions[$i]['user'] = false;
            foreach ($permissions as $permission) {
                $module_permissions[$i][$permission['role']] = $permission['status'] ? true : false;
            }
            $i++;
        }


        $this->setHeader(200);

        //echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $module_permissions, JSON_PRETTY_PRINT));
    }

    /**
     * Set Permissions API.
     * @return mixed
     */
    public function actionSetpermissions() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {
            foreach ($request as $permission) {

                $query = new Query;
                $query
                        ->from('module_permissions')
                        ->where([ 'module_id' => $permission->module_id])
                        ->select("*");

                $command = $query->createCommand();
                $permissions = $command->queryAll();
//                print_r($permissions);
//                exit;
                foreach ($permissions as $Dpermission) {
                    $role = $Dpermission['role'];
                    $module_permissions = array();

                    $module_permissions['status'] = $permission->$role ? 1 : 0;
                    Yii::$app->db->createCommand()->update('module_permissions', $module_permissions, 'id =' . $Dpermission['id'])->execute();
                }
            }
            $status = 1;
            $response = 'Permissions Updated.';
        } else {
            $status = 0;
            $response = 'Request invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get Squib Modules API.
     * @return mixed
     */
    public function actionGetsquibmodules() {

        $query = new Query;
        $query
                ->from('squib_module')
                ->andFilterWhere(['status' => 1])
                ->orderBy('id ASC')
                ->select("name,id");

        $command = $query->createCommand();
        $modules = $command->queryAll();
        $squib_modules = array();
        $i = 0;
        foreach ($modules as $module) {
            $squib_modules[$i]['id'] = $module['id'];
            $squib_modules[$i]['name'] = $module['name'];
            $squib_modules[$i++]['status'] = false;
        }

        $this->setHeader(200);

        //echo json_encode(array('records' => $models), JSON_PRETTY_PRINT);
        return json_encode(array('status' => 1, 'data' => $squib_modules, JSON_PRETTY_PRINT));
    }

    /**
     * Get User Permissions Settings API.
     * @return mixed
     */
    public function actionUserpermissions() {
        $user_role = $_GET ['role'];
        $query = new Query;
        $query
                ->from('module_permissions')
                ->where(['role' => $user_role])
                ->join('JOIN', 'squib_module', $on = 'module_permissions.module_id = squib_module.id ')
                ->orderBy('module_id ASC')
                ->select("module_id,module_permissions.status,squib_module.name");

        $command = $query->createCommand();
        $modules = $command->queryAll();

        $user_permissions = array();
        $i = 0;
        foreach ($modules as $permission) {
            $user_permissions[$i]['id'] = $permission['module_id'];
            $user_permissions[$i]['name'] = $permission['name'];
            $user_permissions[$i++]['status'] = $permission['status'] ? true : false;
        }

        $user_id = $_GET ['user_id'];
        $query = new Query;
        $query
                ->from('user_permissions')
                ->where(['user_id' => $user_id])
                ->join('JOIN', 'squib_module', $on = 'user_permissions.module_id = squib_module.id ')
                ->select("user_permissions.status,module_id,squib_module.name");

        $command = $query->createCommand();
        $permissions = $command->queryAll();
        //print_r($permissions);exit;
        if ($permissions) {
            $i = 0;

            foreach ($permissions as $permission) {
                $key = array_search($permission['name'], array_column($user_permissions, 'name'));
                $user_permissions[$key]['status'] = $permission['status'] ? true : false;
            }
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $user_permissions, JSON_PRETTY_PRINT));
    }

    /**
     * Get User Module Permission Settings API.
     * @return mixed
     */
    public function actionUsermodulepermission() {

        $module_id = $_GET ['module_id'];
        $user_id = $_GET ['user_id'];

        $query = new Query;
        $query
                ->from('user_permissions')
                ->where(['user_id' => $user_id, 'module_id' => $module_id])
                ->select("user_permissions.status");

        $command = $query->createCommand();
        $permission = $command->queryOne();

        if ($permission) {
            $module_status = $permission['status'];
        } else {

            $parent = User::find()
                    ->select('role')
                    ->where(['id' => $user_id])
                    ->one();

            $query = new Query;
            $query
                    ->from('module_permissions')
                    ->where(['role' => $parent->role, 'module_id' => $module_id])
                    ->select("module_permissions.status");

            $command = $query->createCommand();
            $permission = $command->queryOne();

            $module_status = $permission['status'];
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $module_status, JSON_PRETTY_PRINT));
    }

    /**
     * Set User Permissions Settings API.
     * @return mixed
     */
    public function actionSetuserpermissions() {

        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {
            $user_id = $request->user_id;
            foreach ($request->permissions as $permission) {
                $query = new Query;
                $query
                        ->from('user_permissions')
                        ->where(['module_id' => $permission->id])
                        ->andWhere(['user_id' => $user_id])
                        ->select("*");

                $command = $query->createCommand();
                $exist = $command->queryOne();

                $user_permissions['status'] = $permission->status ? 1 : 0;
                if ($exist) {
                    Yii::$app->db->createCommand()->update('user_permissions', $user_permissions, 'user_id =' . $user_id . ' AND module_id =' . $permission->id)->execute();
                } else {
                    $user_permissions['module_id'] = $permission->id;
                    $user_permissions['user_id'] = $user_id;
                    Yii::$app->db->createCommand()->insert('user_permissions', $user_permissions)->execute();
                }
            }
            $status = 1;
            $response = 'Permissions Updated.';
        } else {
            $status = 0;
            $response = 'Request invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Upload User Profile Photo API.
     * @return mixed
     */
    public function actionUploadphoto() {

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
                    $user_pic['profile_image'] = $savd_file_name;
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
     * Delete User Permission API.
     * @return mixed
     */
    public function actionDeleteuserpermission() {
        $user_id = $_GET ['user_id'];
        $delete = Yii::$app->db->createCommand()->delete('user_permissions', 'user_id = ' . $user_id)->execute();
        if ($delete) {
            $response = 'correct';
        } else {
            $response = 'Not correct';
        }

        $this->setHeader(200);

        echo json_encode(array('status' => 1, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Download File API.
     * @return mixed
     */
    public function actionDownload() {

        ignore_user_abort(true);
        set_time_limit(0); // disable the time limit for this script

        $instanceid = $_REQUEST['instanceid'];
        $filepath = urldecode($_REQUEST['filepath']);
        $filename = $_REQUEST['filename'];
        $filetype = $_REQUEST['filetype'];
        $org_name = $_REQUEST['org_name'];

        $root_path = getcwd();

        $path = $root_path . "/uploads/instances/" . $instanceid . "/";


        $dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})", '', $filename); // simple file name validation
        $dl_file = filter_var($dl_file, FILTER_SANITIZE_URL); // Remove (more) invalid characters
        $fullPath = $path . $dl_file;
//echo $fullPath;die;

        if ($fd = fopen($fullPath, "r")) {
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                    header("Content-type: application/pdf");
                    header("Content-Disposition: attachment; filename=\"" . $org_name . "\""); // use 'attachment' to force a file download
                    break;
                // add more headers for other content types here
                default;
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: filename=\"" . $org_name . "\"");
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

    /**
     * Finds the Any model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return City the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {

            $this->setHeader(400);
            echo json_encode(array('status' => 0, 'error_code' => 400, 'message' => 'Bad request'), JSON_PRETTY_PRINT);
            exit;
            // throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Save Visitor API.
     * @return mixed
     */
    public function actionSavevisitor() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if (isset($request->ip_address)) {
            $ip_address = $request->ip_address;
            //$ip_address = '23.127.156.217';
            $result = array();

            /* MAXMIND DATABASE INCLUDE */
            $reader = new Reader(Yii::getAlias('@vendor') . '/maxminddb/GeoLite2-City.mmdb');
            $record = $reader->city($ip_address);
//            print_r($record);
//            exit;
//            print($record->country->isoCode . "\n"); // 'US'
//            print($record->country->name . "\n"); // 'United States'
//            print($record->country->names['zh-CN'] . "\n"); // '美国'
//            print($record->mostSpecificSubdivision->name . "\n"); // 'Minnesota'
//            print($record->mostSpecificSubdivision->isoCode . "\n"); // 'MN'
//            print($record->city->name . "\n"); // 'Minneapolis
//            print($record->location->latitude . "\n"); // 44.9733
//            print($record->location->longitude . "\n"); // -93.2323

            $city = $latitude = $longitude = $zipcode = $isp = $region = $country = '';
            if (isset($record)) {
                $city = $record->city->name;
                $latitude = $record->location->latitude;
                $longitude = $record->location->longitude;
                $zipcode = $record->postal->code;
                $country = $record->country->name;
            }

            if ($latitude == '' || $longitude == '') {
                $this->setHeader(200);
                return json_encode(array('status' => 0, 'message' => "Unable to find Latitude nad longitude.", JSON_PRETTY_PRINT));
            }

            /* FOR GET ISP */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "ipinfo.io/$ip_address");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, false);
            $response = curl_exec($ch);
            curl_close($ch);
            $ispData = json_decode($response);
            if (isset($ispData->org)) {
                $isp = (isset($ispData->org) && !empty($ispData->org)) ? $ispData->org : '';
                $region = (isset($ispData->region) && !empty($ispData->region)) ? $ispData->region : '';
            }

            $uid = 0;
            $update = array();
            if ($request->visitor_id == 0) {
                if ($request->uid) {
                    $user = User::find()->select(['id', 'city', 'state', 'zip'])->where(['uid' => $request->uid])->asArray()->one();
                    if (!empty($user)) {
                        $user_id = $request->visitor_id = $user['id'];
                        $unique_id = $request->uid;
                        $uid = 0;
                        if (empty($user['city'])) {
                            $update['city'] = (!empty($city)) ? $city : '';
                        }
                        if (empty($user['state'])) {
                            $update['state'] = (!empty($region)) ? $region : '';
                        }
                        if (empty($user['zip'])) {
                            $update['zip'] = (!empty($zipcode)) ? $zipcode : '';
                        }
                    } else {
                        $uid = $request->uid;
                        $unique_id = $request->uid;
                    }
                } else {
                    $uid = Yii::$app->commoncomponent->generateUserID();
                    $unique_id = $uid;
                }
            } else {
                $user = User::find()->select(['uid', 'city', 'state', 'zip', 'id'])->where(['id' => $request->visitor_id])->asArray()->one();
                if (!empty($user)) {
                    $user_id = $user['id'];
                    $unique_id = $user['uid'];
                    if (empty($user['city'])) {
                        $update['city'] = (!empty($city)) ? $city : '';
                    }
                    if (empty($user['state'])) {
                        $update['state'] = (!empty($region)) ? $region : '';
                    }
                    if (empty($user['zip'])) {
                        $update['zip'] = (!empty($zipcode)) ? $zipcode : '';
                    }
                }
            }

            if (!empty($update)) {
                Yii::$app->db->createCommand()->update('user', $update, "id = $user_id")->execute();
            }
            if (isset($request->type)) {
                $type = $request->type;
            } elseif ($request->campaign_id != 0) {
                $campaign = Campaign::find()->where(['id' => $request->campaign_id])->asArray()->one();
                $type = $campaign['campaign_type'];
                if ($request->uid == 0 && $campaign['on_the_fly']) {
                    $query = new Query;
                    $query
                            ->from('campaign_keys')
                            ->where(['campaign_id' => $campaign['id'], 'assigned' => 0])
                            //->orderBy(['id' => SORT_ASC])
                            ->select("id");
                    $command = $query->createCommand();
                    $campaign_key = $command->queryOne();
                    $request->key_id = $campaign_key['id'];
                }
            }

            if ($request->key_id) {
                $create['ip_address'] = $request->ip_address;
                $create['lat'] = $latitude;
                $create['lng'] = $longitude;
                $create['user_id'] = $request->user_id;
                $create['visitor_id'] = $request->visitor_id;
                $create['uid'] = $uid;
                $create['os_type'] = $request->os;
                $create['device_type'] = $request->device;
                $create['browser_type'] = $request->browser;
                $create['domain'] = $request->domain;
                $create['country'] = $country;
                $create['city'] = $city;
                $create['region'] = $region;
                $create['isp'] = $isp;
                $create['zipcode'] = $zipcode;
                $create['campaign_id'] = $request->campaign_id;
                $create['key_id'] = $request->key_id;
                $create['type'] = (!empty($type)) ? $type : 0;
                $create['source'] = (isset($request->source)) ? $request->source : 0;

                Yii::$app->db->createCommand()->insert('visitors', $create)->execute();
                $last_id = Yii::$app->db->getLastInsertID();

                if ($request->key_id != 0) {
                    $key = CampaignKeys::findOne($request->key_id);
                    $key->assigned = 1;
                    $key->update();
                }
                $status = 1;
                $response = 'Visitors Added.';
            } else {
                $status = 0;
                $response = 'Max. available keys are already assigned.';
            }
        } else {
            $status = 0;
            $response = 'IP Address not found.';
            $unique_id = '';
        }

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'data' => $response, 'uid' => (string) $unique_id, 'id' => $last_id, JSON_PRETTY_PRINT));
    }

    /**
     * Get Visitors Count API.
     * @return mixed
     */
    public function actionGetvisitorcount() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $visitors = Visitor::find()
                ->select(['DATE_FORMAT(last_visited, "%D %M %Y") as visited_date'])
                ->where(["user_id" => $request->user_id])
                ->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d', strtotime("-1 Week"))])
                ->asArray()
                ->orderBy(['id' => SORT_DESC])
                ->all();

        $prettyArray = array_column($visitors, 'visited_date');
        $data = array_count_values($prettyArray);

        $result = array();
        $i = 0;
        foreach ($data as $k => $v) {
            $result[$i][] = $k;
            $result[$i][] = $v;
            $i++;
        }
        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Get Device Type API.
     * @return mixed
     */
    public function actionGetdevicetype() {
        $devices = Visitor::find()
                ->select(['device_type'])
                ->distinct()
                ->asArray()
                ->all();
        $deviceList = array();
        foreach ($devices as $k => $device) {
            $deviceList[$k]['value'] = $device['device_type'];
            $deviceList[$k]['name'] = ucfirst($device['device_type']);
        }
        array_unshift($deviceList, array('value' => 0, 'name' => 'All'));

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $deviceList, JSON_PRETTY_PRINT));
    }

    /**
     * Get Visitors for Main Location Table API.
     * @return mixed
     */
    public function actionGetvisitorfortable() {
        $user_id = $_GET['user_id'];
        $child_ids = $this->getChilds($user_id);
        $child_ids = array_merge(array($user_id), $child_ids);
        $subQueryVisitors = Visitor::find()
                ->select(['MAX(id) as id'])
                ->where(['user_id' => $child_ids])
//                ->where(['visitor_id' => 0])
//                ->orWhere(['visitor_id' => $child_ids])
                ->groupBy(['visitor_id'])
                ->asArray()
                ->all();
        //print_r($subQueryVisitors);exit;
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
        //echo $order['data']." " . $dir;exit;

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
//        print_r($visitors);
//        exit;
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
            $count = Visitor::find()->where(['visitor_id' => $visitor['visitor_id']])->andWhere(['user_id' => $child_ids])->count();
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
                        ->where(['visitor_id' => $visitor['visitor_id']])
                        ->andWhere(['user_id' => $child_ids])
                        ->groupBy(['domain'])
                        ->asArray()
                        ->all();
            } else {
                $subQueryHistory = Visitor::find()
                        ->select(['MAX(id) as id'])
                        ->where(['visitor_id' => $visitor['visitor_id']])
                        ->andWhere(['user_id' => $child_ids])
                        ->groupBy(['domain', 'uid'])
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



            if (!empty($historyDetails)) {
                $hs = array();
                foreach ($historyDetails as $k => $history) {
                    if ($history['visitor_id'])
                        $count = Visitor::find()->where(['visitor_id' => $history['visitor_id'], 'domain' => $history['domain']])->andWhere(['user_id' => $child_ids])->count();
                    else
                        $count = Visitor::find()->where(['visitor_id' => $history['visitor_id'], 'domain' => $history['domain'], 'uid' => $history['uid']])->andWhere(['user_id' => $child_ids])->count();

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
                    //$hs[$k]['device_icon'] = 'assets/images/' . $deviceIconList[$history['device_type']];
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
        return json_encode(array('status' => 1, 'aaData' => $rs, JSON_PRETTY_PRINT));
    }

    /**
     * Save Campaign API.
     * @return mixed
     */
    public function actionSavecampaign() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);

        if (!empty($request)) {

            $data = $this->checkUniqueCampaign($request->name, $request->user_id);
            if (!empty($data)) {
                $res = $this->checkDateRange(date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date)), $request->user_id);
                if (!empty($res)) {
                    $this->setHeader(400);
                    return json_encode(array('status' => 0, 'code' => 200, 'message' => 'Campaign name already exist.'), JSON_PRETTY_PRINT);
                }
            }

            $save = array();
            $save['name'] = $request->name;
            $save['slug'] = Yii::$app->commoncomponent->slugUnique($request->name, 'campaign', 'slug');
            $save['user_id'] = $request->user_id;
            $save['start_date'] = date('Y-m-d', strtotime($request->start_date));
            $save['end_date'] = date('Y-m-d', strtotime($request->end_date));
            $save['url'] = $request->url;
            $save['no_of_keys'] = $request->no_of_keys;
            $save['key_generate_type'] = $request->key_generate_type;

            $save['campaign_type'] = $request->campaign_type->id;
            if ($request->campaign_type->id == 3) {
                $save['folder_type'] = $request->folder_type;
                $save['drive_user_id'] = $request->drive_user_id;
                $save['drive_id'] = $request->drive_id;
                $save['drive_type'] = $request->drive_type;
            }

            if (isset($request->key_generate_type) && $request->key_generate_type == "sequential") {
                $save['key_start_no'] = $request->key_start_no;
            }

            if (isset($request->campaign_alert) && $request->campaign_alert == 1) {

                $save['campaign_alert'] = 1;
                $save['manager_name'] = $request->manager_name;
                $save['manager_email'] = $request->manager_email;
            } else {
                $save['campaign_alert'] = 0;
            }

            if (isset($request->on_the_fly) && $request->on_the_fly == 1) {
                $save['on_the_fly'] = 1;
            } else {
                $save['on_the_fly'] = 0;
            }
            if (isset($request->uurl_type) && trim($request->uurl_type) == "1") {
                $vanityUrl = User::find()->select(['instance_url'])->where(['id' => $request->user_id])->asArray()->one();
                if ($vanityUrl != '')
                    $save['uurl'] = $vanityUrl['instance_url'] . "/campaign/";
                else
                    $save['uurl'] = Yii::$app->params['SHORTER_URL'] . "/campaign/";
            } else {
                $save['uurl'] = Yii::$app->params['SHORTER_URL'] . "/campaign/";
            }
            $save['uurl_type'] = $request->uurl_type;
            $save['connection_type'] = $request->connection_type;

            $userDetail = User::find()->where(['id' => $request->user_id])->asArray()->one();
            if ($userDetail['role'] == 'admin' || $request->no_of_keys <= 1000) {
                $save['status'] = 1;
            } else {
                $save['status'] = 0;
            }

            Yii::$app->db->createCommand()->insert('campaign', $save)->execute();
            $id = Yii::$app->db->getLastInsertID();

            if (!empty($id)) {
                //if (!isset($request->on_the_fly) || trim($request->on_the_fly) == 0) {
                if ($userDetail['role'] == 'admin' || $request->no_of_keys <= 1000) {

                    if ($request->key_generate_type == "sequential") {
                        $rows = array();
                        for ($i = 1; $i <= $request->no_of_keys; $i++) {
                            $rows[$i]['campaign_id'] = $id;
                            $rows[$i]['key'] = $request->key_start_no;
                            $request->key_start_no += 1;
                            // Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                        }
                        Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                    } else {

                        $rows = array();
                        for ($i = 1; $i <= $request->no_of_keys; $i++) {
                            $rows[$i]['campaign_id'] = $id;
                            //$randomString = Yii::$app->commoncomponent->generateRandomString(8, $id);
                            $rows[$i]['key'] = uniqid();
                            //Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                        }
                        Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                    }
                }

                $ids = $this->getParents($userDetail['parent_id']);
                $ids = array_merge(array($userDetail['parent_id']), $ids);

                Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($request->user_id);

                Yii::$app->mailer->compose('create_campaign', [
                            'campaign_name' => $request->name,
                            'start_date' => date('M d, Y', strtotime($request->start_date)),
                            'end_date' => date('M d, Y', strtotime($request->end_date)),
                            'username' => "Amit",
                            'role' => 'admin',
                            'no_of_keys' => $request->no_of_keys,
                            'confirm' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=confirm",
                            'cancel' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=cancel",
                        ])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo('amitgaur595@gmail.com')
                        ->setSubject('New Campaign Created')
                        ->send();
                //print_r($ids);exit;
                if (!empty($ids)) {
                    $emails = User::find()->select(['role', 'email_id', 'admin_name'])->where(['id' => $ids])->asArray()->all();
                    foreach ($emails as $email) {

                        Yii::$app->mailer->compose('create_campaign', [
                                    'campaign_name' => $request->name,
                                    'start_date' => date('M d, Y', strtotime($request->start_date)),
                                    'end_date' => date('M d, Y', strtotime($request->end_date)),
                                    'username' => $email['admin_name'],
                                    'role' => $email['role'],
                                    'no_of_keys' => $request->no_of_keys,
                                    'confirm' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=confirm",
                                    'cancel' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=cancel",
                                ])
                                ->setFrom(Yii::$app->params['adminEmail'])
                                ->setTo($email['email_id'])
                                ->setSubject('New Campaign Created')
                                ->send();
                    }
                }
                //}

                $status = 1;
                $response_code = 200;
                $message = "Campaign successfully added.";
            } else {
                $status = 0;
                $response_code = 400;
                $message = "Campaign not successfully added.";
            }
        }
        $this->setHeader(400);
        return json_encode(array('status' => $status, 'code' => $response_code, 'message' => $message), JSON_PRETTY_PRINT);
    }

    /**
     * Update Campaign API.
     * @return mixed
     */
    public function actionUpdatecampaign() {
        $postData = file_get_contents("php://input");
        $request = json_decode($postData);

        if (!empty($request)) {
            $data = $this->checkUniqueCampaign($request->name, $request->user_id, $request->id);
            if (!empty($data)) {
                $res = $this->checkDateRange(date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date)), $request->user_id, $request->id);
                if (!empty($res)) {
                    $this->setHeader(400);
                    return json_encode(array('status' => 0, 'code' => 200, 'message' => 'Campaign name already exist.'), JSON_PRETTY_PRINT);
                }
            }

            $save = array();
            $save['name'] = $request->name;
            //$save['user_id'] = $request->user_id;
            $save['start_date'] = date('Y-m-d', strtotime($request->start_date));
            $save['end_date'] = date('Y-m-d', strtotime($request->end_date));
            $save['url'] = $request->url;
            $save['no_of_keys'] = $request->no_of_keys;
            $save['key_generate_type'] = $request->key_generate_type;
            $save['campaign_type'] = $request->campaign_type;
            if ($request->campaign_type == 3) {
                $save['folder_type'] = $request->folder_type;
                $save['drive_user_id'] = $request->drive_user_id;
                $save['drive_id'] = $request->drive_id;
                $save['drive_type'] = $request->drive_type;
            }
            if (isset($request->key_generate_type) && $request->key_generate_type == "sequential") {
                $save['key_start_no'] = $request->key_start_no;
            }
            $userDetail = User::find()->where(['id' => $request->user_id])->asArray()->one();

            $extra_keys = $request->no_of_keys - $request->keys_exist;
            if ($extra_keys) {
                if ($userDetail['role'] == 'admin' || $extra_keys <= 1000) {

                    if ($request->key_generate_type == "sequential") {
                        $rows = array();
                        $query = new Query;
                        $query
                                ->from('campaign_keys')
                                ->where(['campaign_id' => $request->id])
                                //->orderBy(['id' => SORT_ASC])
                                ->select("Max(CAST(`key` as UNSIGNED)) as max_key");
                        $command = $query->createCommand();
                        $max_key = $command->queryOne();
                        $new_start = $max_key['max_key'] + 1;
                        for ($i = 1; $i <= $extra_keys; $i++) {
                            $rows[$i]['campaign_id'] = $request->id;
                            $rows[$i]['key'] = $new_start;
                            $new_start += 1;
                            // Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                        }
                        Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                    } else {

                        $rows = array();
                        for ($i = 1; $i <= $extra_keys; $i++) {
                            $rows[$i]['campaign_id'] = $id;
                            //$randomString = Yii::$app->commoncomponent->generateRandomString(8, $id);
                            $rows[$i]['key'] = uniqid();
                            //Yii::$app->db->createCommand()->insert('campaign_keys', $insert)->execute();
                        }
                        Yii::$app->db->createCommand()->batchInsert('campaign_keys', ['campaign_id', 'key'], $rows)->execute();
                    }
                }

                $ids = $this->getParents($userDetail['parent_id']);
                $ids = array_merge(array($userDetail['parent_id']), $ids);

                Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($request->user_id);

                Yii::$app->mailer->compose('create_campaign', [
                            'campaign_name' => $request->name,
                            'start_date' => date('M d, Y', strtotime($request->start_date)),
                            'end_date' => date('M d, Y', strtotime($request->end_date)),
                            'username' => "Amit",
                            'role' => 'admin',
                            'no_of_keys' => $request->new_keys,
                            'confirm' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=confirm",
                            'cancel' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=cancel",
                        ])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo('amitgaur595@gmail.com')
                        ->setSubject('New Campaign Created')
                        ->send();
                //print_r($ids);exit;
                if (!empty($ids)) {
                    $emails = User::find()->select(['role', 'email_id', 'admin_name'])->where(['id' => $ids])->asArray()->all();
                    foreach ($emails as $email) {

                        Yii::$app->mailer->compose('update_campaign', [
                                    'campaign_name' => $request->name,
                                    'start_date' => date('M d, Y', strtotime($request->start_date)),
                                    'end_date' => date('M d, Y', strtotime($request->end_date)),
                                    'username' => $email['admin_name'],
                                    'role' => $email['role'],
                                    'no_of_keys' => $request->no_of_keys,
                                    'new_keys' => $extra_keys,
                                    'confirm' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=confirm",
                                    'cancel' => Yii::$app->params['HTTP_URL'] . "/squibkeys/" . $save['slug'] . "?action=cancel",
                                ])
                                ->setFrom(Yii::$app->params['adminEmail'])
                                ->setTo($email['email_id'])
                                ->setSubject('Campaign Updated')
                                ->send();
                    }
                }
            }

            if (isset($request->campaign_alert) && $request->campaign_alert == 1) {
                $save['campaign_alert'] = 1;
                $save['manager_name'] = $request->manager_name;
                $save['manager_email'] = $request->manager_email;
            } else {
                $save['campaign_alert'] = 0;
            }

            $save['connection_type'] = $request->connection_type;
            $save['status'] = 1;

            Yii::$app->db->createCommand()->update('campaign', $save, "id = $request->id")->execute();

            $status = 1;
            $message = "Update successfully";

            $this->setHeader(200);
            return json_encode(array('status' => $status, 'message' => $message), JSON_PRETTY_PRINT);
        }
    }

    /**
     * Get Active Campaign List Table API.
     * @return mixed
     */
    public function actionGetcampaignlist() {
        $id = $_GET['id'];
        $child_ids = $this->getChilds($id);
        $child_ids = array_merge(array($id), $child_ids);
        $time = new \DateTime('now');
        $today = $time->format('Y-m-d');
        $user = User::find()->select(['parent_id'])->where(["id" => $id])->asArray()->one();
        $campaigns = Campaign::find()->where(["user_id" => $child_ids])->andWhere('status != :status', [':status' => 2])->andWhere([ 'and', "end_date>='$today'", "start_date<='$today'"])->asArray()->all();

        $result = array();
        if (!empty($campaigns)) {
            foreach ($campaigns as $k => $campaign) {
                $result[$k]['index'] = $k;
                $result[$k]['id'] = $campaign['id'];
                $result[$k]['type'] = $user['parent_id'];
                $result[$k]['name'] = $campaign['name'];
                $result[$k]['slug'] = $campaign['slug'];
                $result[$k]['start_date'] = date('M d, Y', strtotime($campaign['start_date']));
                $result[$k]['end_date'] = date('M d, Y', strtotime($campaign['end_date']));
                $result[$k]['url'] = $campaign['url'];
                $result[$k]['key_generate_url'] = ($campaign['on_the_fly']) ? $campaign['uurl'] . $campaign['slug'] : '';
                $result[$k]['status'] = ($campaign['status']) ? 'Active' : 'Pending';
                $result[$k]['keys'] = $campaign['no_of_keys'];
            }
            $status = 1;
            $message = "Campaign list get successfully.";
        } else {
            $status = 0;
            $message = "Campaign list did not get successfully.";
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'aaData' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Get Archived Campaign List API.
     * @return mixed
     */
    public function actionGetarchivedcampaignlist() {
        $id = $_GET['id'];
        $user = User::find()->select(['parent_id'])->where(["id" => $id])->asArray()->one();
        $date = new \DateTime('now');
        $today = $date->format('Y-m-d');
        $campaigns = Campaign::find()->where(['or', "status='2'", "start_date>'$today' AND end_date>'$today'", "start_date<'$today' AND end_date<'$today'"])->asArray()->all();

        $result = array();
        if (!empty($campaigns)) {
            foreach ($campaigns as $k => $campaign) {
                $result[$k]['index'] = $k;
                $result[$k]['id'] = $campaign['id'];
                $result[$k]['type'] = $user['parent_id'];
                $result[$k]['name'] = $campaign['name'];
                $result[$k]['slug'] = $campaign['slug'];
                $result[$k]['start_date'] = date('M d, Y', strtotime($campaign['start_date']));
                $result[$k]['end_date'] = date('M d, Y', strtotime($campaign['end_date']));
                $result[$k]['url'] = $campaign['url'];
                $result[$k]['key_generate_url'] = ($campaign['on_the_fly']) ? $campaign['uurl'] . $campaign['slug'] : '';
                $result[$k]['status'] = (date('Y-m-d', strtotime($campaign['end_date'])) >= $today) ? 'Active' : 'Ended';
            }
            $status = 1;
            $message = "Campaign list get successfully.";
        } else {
            $status = 0;
            $message = "Campaign list did not get successfully.";
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'aaData' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Get All Campaign List API.
     * @return mixed
     */
    public function actionGetallcampaignlist() {
        $id = $_GET['id'];

        $userData = User::find()->where(["id" => $id])->asArray()->one();
        $id_list = $this->getChilds($userData['id']);
        $id_list = array_merge(array($id), $id_list);

        $today = date('Y-m-d');
        //andWhere([ 'and', "end_date>='$today'", "start_date<='$today'"])
        $campaigns = Campaign::find()->where(["user_id" => $id_list])->with('visits')->asArray()->all();
        $result = array();
        if (!empty($campaigns)) {
            foreach ($campaigns as $k => $campaign) {

                $result[$k]['status'] = (date('Y-m-d', strtotime($campaign['end_date'])) >= $today) ? 'Active' : 'Ended';
                $result[$k]['index'] = $k;
                $result[$k]['id'] = $campaign['id'];
                $result[$k]['name'] = $campaign['name'];
                $result[$k]['slug'] = $campaign['slug'];
                $result[$k]['start_date'] = date('M d, Y', strtotime($campaign['start_date']));
                $result[$k]['end_date'] = date('M d, Y', strtotime($campaign['end_date']));
                $result[$k]['url'] = $campaign['url'];
                $result[$k]['key_generate_url'] = ($campaign['on_the_fly']) ? $campaign['uurl'] . $campaign['slug'] : '';
                $result[$k]['visit'] = count($campaign['visits']);
            }
            $status = 1;
            $message = "Campaign list get successfully.";
        } else {
            $status = 1;
            $message = "No campaign found.";
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'aaData' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Get Active Campaign List Drop-Down API.
     * @return mixed
     */
    public function actionGetactivecampaignlist() {
        $id = $_GET['id'];

        $userData = User::find()->where(["id" => $id])->asArray()->one();
        $id_list = $this->getChilds($userData['id']);
        $id_list = array_merge(array($id), $id_list);

        $today = date('Y-m-d');

        $campaigns = Campaign::find()->select(['id', 'name'])->where(["user_id" => $id_list, "status" => 1])->andWhere([ 'and', "end_date>='$today'", "start_date<='$today'"])->orderBy('name ASC')->asArray()->all();
        $result = array();
        if (!empty($campaigns)) {
            foreach ($campaigns as $k => $campaign) {
                $result[$k]['index'] = $k;
                $result[$k]['id'] = $campaign['id'];
                $result[$k]['name'] = $campaign['name'];
            }
            $status = 1;
            $message = "Campaign list get successfully.";
        } else {
            $status = 1;
            $message = "No campaign found.";
        }

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'aaData' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Delete Campaign API.
     * @return mixed
     */
    public function actionDeletecampaign() {
        $id = $_GET ['id'];
        $type = $_GET ['type'];
        if ($type) {
            $campaign = Campaign::findOne($id);
            $campaign->status = $type;
            $campaign->save();
            $response = 'Campaign archived successfully.';
        } else {
            $delete = Campaign::deleteAll('id = ' . $id);
            if ($delete) {
                CampaignKeys::deleteAll('campaign_id = ' . $id);
                Visitor::deleteAll('campaign_id = ' . $id);
                $response = 'Deleted successfully.';
            } else {
                $response = 'Not deleted successfully.';
            }
        }
        $this->setHeader(200);
        return json_encode(array('status' => 1, 'message' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Get Campaign by Id API.
     * @return mixed
     */
    public function actionGetcampaignbyid() {
        $postData = file_get_contents("php://input");
        $request = json_decode($postData);

        $campaign_id = $request->id;
        $campaign = Campaign::find()
                ->select(['*', 'DATE_FORMAT(start_date, "%M %d %Y") as start_date', 'DATE_FORMAT(end_date, "%M %d %Y") as end_date'])
                ->where(['id' => $campaign_id])
                ->asArray()
                ->one();
        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $campaign), JSON_PRETTY_PRINT);
    }

    /**
     * Get Campaign keys to Export in CSV API.
     * @return mixed
     */
    public function actionGetcampaigndetail() {
        ini_set('memory_limit', '1024M');
        $id = $_GET['id'];
        $campaigns = Campaign::find()
                //->with('keys')
                ->where(['id' => $id])
                ->asArray()
                ->one();

        $result = array();



        if (isset($_GET['order'])) {
            $dir = $_GET['order'][0]['dir'];
            $order = ($_GET['columns'][$_GET['order'][0]['column']]);
        } else {
            $dir = "ASC";
        }
        if (isset($_GET['search'])) {
            $search = $_GET['search']['value'];
        } else {
            $search = '';
        }

        if (!empty($campaigns)) {

//            $connection = Yii::$app->getDb();
//            $command = $connection->createCommand("CALL geyKeys(:camp_id,:oft,:lmt)");
//            $command->bindParam(":camp_id", $campaigns['id']);
//            $command->bindParam(":oft",  $_GET['start']);
//            $command->bindParam(":lmt", $_GET['length']);
//            $command->bindParam(":dir", $dir);
//            $campaign_keys = $command->queryAll();
            $csvStr = "SQUIBKeys, Site URL\r\n";

            if ($campaigns['on_the_fly'] == 0) {
                $query = new Query;
                $query
                        ->from('campaign_keys')
                        ->where([ 'campaign_id' => $campaigns['id']])
                        //->andFilterWhere(['like', 'key', $search])
                        //->orderBy("key " . $dir)
                        //->offset($_GET['start'])
                        //->limit($_GET['length'])
                        ->select("key");
                $command = $query->createCommand();
                $campaign_keys = $command->queryAll();



                foreach ($campaign_keys as $k => $key) {
                    $url = ($campaigns['on_the_fly']) ? $campaigns['uurl'] . $campaigns['slug'] . '/' . $key['key'] : $campaigns['uurl'] . $campaigns['slug'] . '/USB=' . $key['key'];
                    $csvStr .= $key['key'] . "," . $url . "\r\n";
//                $result[$k]['key'] = $key['key'];
//                $result[$k]['name'] = $campaigns['name'];
//                $result[$k]['start_date'] = date('M d Y', strtotime($campaigns['start_date']));
//                $result[$k]['end_date'] = date('M d Y', strtotime($campaigns['end_date']));
//                $result[$k]['uurl'] = ($campaigns['on_the_fly']) ? $campaigns['uurl'] . $campaigns['slug'] . '/' . $key['key'] : $campaigns['uurl'] . $campaigns['slug'] . '/USB=' . $key['key'];
                }
            } else {
                $url = $campaigns['uurl'] . $campaigns['slug'];
                $csvStr .= "All Keys," . $url . "\r\n";
            }

            header("Content-type: text/csv");
            header('Content-Disposition: attachment; filename="squibkeys.csv"');

            echo $csvStr;
//exit;
//            $query = new Query;
//            $campaign_keys_count = (new yii\db\Query())
//                    ->from('campaign_keys')
//                    ->where([ 'campaign_id' => $campaigns['id']])
//                    ->count();
//
//            /*
//             * Output
//             */
//            $response = array(
//                "draw" => isset($_GET['draw']) ?
//                        intval($_GET['draw']) :
//                        0,
//                "recordsTotal" => $campaign_keys_count,
//                "recordsFiltered" => $campaign_keys_count,
//                "data" => $result
//            );
//            echo json_encode($response);
//            exit;
//
//            $status = 1;
//            $message = "Campaign details get successfully";
//        } else {
//            $status = 0;
//            $message = "Campaign details did not get successfully";
        }
//
//        $this->setHeader(200);
//        return json_encode(array('status' => $status, 'data' => $result), JSON_PRETTY_PRINT);
    }

    /**
     * Get Campaign keys count API.
     * @return mixed
     */
    public function actionGetcampaignkeys() {
        $id = $_GET['id'];
        $query = new Query;
        $campaign_keys_count = (new yii\db\Query())
                ->from('campaign_keys')
                ->where([ 'campaign_id' => $id])
                ->count();

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $campaign_keys_count), JSON_PRETTY_PRINT);
    }

    /**
     * Check Unique Campaign.
     * @return mixed
     */
    public function checkUniqueCampaign($name, $user_id, $id = 0) {
        if ($id) {
            $time = new \DateTime('now');
            $today = $time->format('Y-m-d');
            return Campaign::find()
                            ->where(['name' => $name, 'user_id' => $user_id])
                            ->andWhere([">", "end_date", $today])
                            ->andWhere(["<>", "id", $id])
                            ->all();
        } else {
            $time = new \DateTime('now');
            $today = $time->format('Y-m-d');
            return Campaign::find()
                            ->where(['name' => $name, 'user_id' => $user_id])
                            ->andWhere([">", "end_date", $today])
                            ->all();
        }
    }

    /**
     * Check Date Range
     * @return mixed
     */
    public function checkDateRange($start_date, $end_date, $user_id, $id = 0) {
        if ($id) {
            return Campaign::find()
                            ->where(['user_id' => $user_id])
                            ->andWhere(["<>", "id", $id])
                            ->andWhere(["OR", "start_date BETWEEN '$start_date' AND '$end_date'", "end_date BETWEEN '$start_date' AND '$end_date'", ["and", "start_date <= '$start_date'", "end_date >= '$end_date'"], ["and", "start_date >= '$start_date'", "end_date <= '$end_date'"]])
                            ->asArray()
                            ->all();
        } else {
            return Campaign::find()
                            ->where(['user_id' => $user_id])
                            ->andWhere(["OR", "start_date BETWEEN '$start_date' AND '$end_date'", "end_date BETWEEN '$start_date' AND '$end_date'", ["and", "start_date <= '$start_date'", "end_date >= '$end_date'"], ["and", "start_date >= '$start_date'", "end_date <= '$end_date'"]])
                            ->asArray()
                            ->all();
        }
    }

    /**
     * Get Parents of the user
     * @return mixed
     */
    public function getParents($ids) {
        $result = User::find()->where(['id' => $ids])->asArray()->all();
        if (!empty($result) && $result[0]['parent_id'] != 0) {
            $ids = array_column($result, 'parent_id');
            if (!empty($ids)) {
                return array_merge($ids, $this->getParents($result[0]['parent_id']));
            }
        } else {
            return array();
        }
    }

    /**
     * Get Children of the user
     * @return mixed
     */
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

    /**
     * Check Campaign Activation and Key Assignment API
     * @return mixed
     */
    public function actionCheck_campaign_activate() {
        $postData = file_get_contents("php://input");
        $request = json_decode($postData);

        $time = new \DateTime('now');
        $today = $time->format('Y-m-d');
        $campaigns = Campaign::find()
                ->with('user')
                //->with('keys')
                ->where(['slug' => $request->name])
                ->asArray()
                ->one();

        $available = '';
        $key = '';

        // For the case of OTF key==0
        if ($campaigns['on_the_fly'] && $request->key == 0) {
            // IF user logged in then get user's UID
            if ($request->visitor_id != 0) {
                if (isset($request->uid)) {
                    $checkVisit = Visitor::find()->select(["*"])->where(["ip_address" => $request->ip_address, "visitor_id" => $request->visitor_id, "campaign_id" => $campaigns['id']])->orderBy('id DESC')->asArray()->one();
                }
            } else {
                if (isset($request->uid)) {
                    $checkUser = User::find()->select(["id"])->where(["uid" => $request->uid])->orderBy('id DESC')->asArray()->one();
                    if ($checkUser) {
                        $checkVisit = Visitor::find()->select(["*"])->where(["ip_address" => $request->ip_address, "visitor_id" => $checkUser['id'], "campaign_id" => $campaigns['id']])->orderBy('id DESC')->asArray()->one();
                    } else {
                        $checkVisit = Visitor::find()->select(["*"])->where(["ip_address" => $request->ip_address, "uid" => $request->uid, "campaign_id" => $campaigns['id']])->orderBy('id DESC')->asArray()->one();
                    }
                }
            }

            if ($checkVisit) {
                $query = new Query;
                $query
                        ->from('campaign_keys')
                        ->where(['id' => $checkVisit['key_id']])
                        //->orderBy(['id' => SORT_ASC])
                        ->select("*");
                $command = $query->createCommand();
                $campaign_key = $command->queryOne();
            } else {
                $query = new Query;
                $query
                        ->from('campaign_keys')
                        ->where(['campaign_id' => $campaigns['id'], 'assigned' => 0])
                        //->orderBy(['id' => SORT_ASC])
                        ->select("*");
                $command = $query->createCommand();
                $campaign_key = $command->queryOne();
            }
        } else {
            $query = new Query;
            $query
                    ->from('campaign_keys')
                    ->where(['campaign_id' => $campaigns['id'], 'key' => $request->key])
                    ->select("*");
            $command = $query->createCommand();
            $campaign_key = $command->queryOne();
        }


        if ($campaigns['start_date'] <= $today && $campaigns['end_date'] >= $today) {


            if ($campaign_key['assigned'] == 0) {
                $status = 1;
                $link = '';
                $available = true;
                $key = $campaign_key['id'];
                $message = "Key available";
                $uid = "";
            } else if ($campaign_key['assigned'] == 1) {
                $visitorData = Visitor::find()->select(["*"])->with('users')->where(["key_id" => $campaign_key['id']])->orderBy(['id' => SORT_DESC])->asArray()->one();
                $status = 2;
                $link = '';
                $key = $campaign_key['id'];
                $message = "Key already assigned.";
                $available = false;
                $uid = (empty($visitorData['visitor_id'])) ? $visitorData['uid'] : $visitorData['users']['uid'];
            } else {
                $status = 0;
                $message = "Key not found.";
                $available = false;
                $link = '';
                $uid = "";
            }
        } else {
            $status = 0;
            $link = $campaigns['user']['instance_url'];
            $message = "Sorry? This campaign has expired.<br>If you are not redirected in 5 seconds to the sponsor, <a href='$link'>click here.</a>";
            $available = false;
            $uid = "";
        }
        $data = array();
        $data['user_id'] = $campaigns['user_id'];
        $data['campaign_id'] = $campaigns['id'];
        $data['on_the_fly'] = $campaigns['on_the_fly'];
        $data['link'] = $campaigns['user']['instance_url'] ? $campaigns['user']['instance_url'] : Yii::$app->params['HTTP_URL'] . "/login";
        $data['url'] = $campaigns['url'];
        $data['connection_type'] = $campaigns['connection_type'];
        $data['key_id'] = $key;
        //$data['key'] = $request->key;
        $data['available'] = $available;
        $data['uid'] = $uid;

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'message' => $message, 'data' => $data), JSON_PRETTY_PRINT);
    }

    /**
     * Opt-in Sign Up API
     * @return mixed
     */
    public function actionAdd_visitor() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->user_id) {
            $parent = User::findOne([
                        'id' => $request->user_id,
            ]);
        }

        $user = new User;
        $user->admin_name = (isset($request->full_name)) ? $request->full_name : '';
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
        $uid = Yii::$app->commoncomponent->generateUserID();
        $user->uid = $uid;
        $user->sex = "";
        $user->dob = "0000-00-00";
        $user->website = "";
        $user->mobile_phone = "";
        $user->work_phone = "";
        $user->about_me = "";
        $user->created = date("Y-m-d H:i:s");
        $user->modified = date("Y-m-d H:i:s");
        $user->parent_id = $parent->id;
        $user->campaign_id = $request->campaign_id;

        $userExist = User::findOne([
                    'email_id' => $request->email_address,
        ]);

        if ($userExist) {
            $status = 0;
            $response = 'Already Registered?';
            $link = '';
            $uid = '';
        } else {
            $lat = 0;
            $lng = 0;
            $user->lat = $lat;
            $user->lng = $lng;
            $user->insert();
            $last_id = $user->id;

            $link = Yii::$app->params['HTTP_URL'] . "/login/" . $last_id . "/" . md5($last_id) . "/" . $request->email_address;
            /* SIGN UP MAIL SEND TO THE USER */
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand($parent->id);
            Yii::$app->mailer->compose('user_signup', ['link' => $link,])
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo($request->email_address)
                    ->setSubject('Activate your account on ' . Yii::$app->params['SiteName'])
                    ->send();

            /* MAIL SENT TO THE PARENT OF THE USER */
            $ids = $this->getParents($last_id);
            if (!empty($ids)) {
                $emails = User::find()->select(['email_id', 'admin_name'])->where(['id' => $ids])->asArray()->all();
                foreach ($emails as $email) {
                    Yii::$app->mailer->compose('sign_up_notification', [
                                'name' => $user->admin_name,
                                'email' => $user->email_id,
                                'message' => 'New user sign up from campaign optin',
                            ])
                            ->setFrom(Yii::$app->params['adminEmail'])
                            ->setTo($email['email_id'])
                            ->setSubject('New Sign Up Created From Campaign Optin')
                            ->send();
                }
            }

            $status = 1;
            $uid = $uid;
            $response = 'Signup Successful. Please check your email to activate your account.';
        }
        $this->setHeader(200);

        echo json_encode(array('status' => $status, 'data' => $response, 'link' => $link, 'uid' => (string) $uid), JSON_PRETTY_PRINT);
    }

    /**
     * Get Social Statistics on Dashboard API
     * @return mixed
     */
    public function actionGet_social_statistics() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $user_id = $request->user_id;
        $type = $request->type;

        $id_list = $this->getChilds($user_id);
        $id_list = array_merge(array($user_id), $id_list);


        // GET TODAY'S DATE
        $today = date('Y-m-d');
        // GET LAST ONE WEEK
        $lastWeekDate = date('Y-m-d', strtotime('-1 Week'));
        // GET FIRST DATE OF THE LAST MONTH
        $lastMonthStartDate = date('Y-m-d', strtotime('first day of last month'));

        // TODAYS LIKE COUNT
        $data['todayLike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 1])
                ->andWhere(["DATE_FORMAT(created,'%Y-%m-%d')" => $today])
                ->count();


        // PREVIOUS WEEK LIKE COUNT
        $data['prevWeekLike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 1])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastWeekDate, $today])
                ->count();

        // PREVIOUS MONTH LIKE COUNT
        $data['prevMonthLike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 1])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastMonthStartDate, $today])
                ->count();

        // TODAYS DISLIKE COUNT
        $data['todayDislike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 0])
                ->andWhere(["DATE_FORMAT(created,'%Y-%m-%d')" => $today])
                ->count();

        // PREVIOUS WEEK DISLIKE COUNT
        $data['prevWeekDislike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 0])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastWeekDate, $today])
                ->count();

        // PREVIOUS MONTH DISLIKE COUNT
        $data['prevMonthDislike'] = Like::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["like" => 0])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastMonthStartDate, $today])
                ->count();

        // TODAYS COMMENT COUNT
        $data['todayComment'] = Comment::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["DATE_FORMAT(created,'%Y-%m-%d')" => $today])
                ->count();

        // PREVIOUS WEEK COMMENT COUNT
        $data['prevWeekComment'] = Comment::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastWeekDate, $today])
                ->count();

        // PREVIOUS MONTH COMMENT COUNT
        $data['prevMonthComment'] = Comment::find()
                ->where(["profile_id" => $id_list])
                ->andWhere(["BETWEEN", "DATE_FORMAT(created,'%Y-%m-%d')", $lastMonthStartDate, $today])
                ->count();

        // TODAYS SHARE COUNT
        $data['todayShare'] = Share::find()
                ->where(["to_user_id" => $id_list])
                ->andWhere(["DATE_FORMAT(sent_time,'%Y-%m-%d')" => $today])
                ->count();

        // PREVIOUS WEEK SHARE COUNT
        $data['prevWeekShare'] = Share::find()
                ->where(["to_user_id" => $id_list])
                ->andWhere(["BETWEEN", "DATE_FORMAT(sent_time,'%Y-%m-%d')", $lastWeekDate, $today])
                ->count();

        // PREVIOUS MONTH SHARE COUNT
        $data['prevMonthShare'] = Share::find()
                ->where(["to_user_id" => $id_list])
                ->andWhere(["BETWEEN", "DATE_FORMAT(sent_time,'%Y-%m-%d')", $lastMonthStartDate, $today])
                ->count();

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $data));
    }

    /**
     * Clear Campaign Wizard API
     * @return mixed
     */
    public function actionClear_campaign() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->key_type == 'all') {
            $visitors = $visitors = Visitor::find()
                    ->select(['id'])
                    ->where(["campaign_id" => $request->name->id])
                    ->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d', strtotime($request->start_date))])
                    ->andWhere(["<=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d', strtotime($request->end_date))])
                    ->asArray()
                    ->all();

            if (!empty($visitors)) {
                $ids = array_column($visitors, 'id');
                Visitor::deleteAll(array('id' => $ids));
                $save['campaign_id'] = $request->name->id;
                $save['keys'] = $request->key_type;
                $save['start'] = '';
                $save['end'] = '';
                Yii::$app->db->createCommand()->insert('delete_log', $save)->execute();
                Yii::$app->db->createCommand()->update('campaign_keys', array('assigned' => 0), array("campaign_id" => $request->name->id))->execute();
                $status = 1;
                $message = "Data successfully deleted";
            } else {
                $status = 0;
                $message = "No data exist between these dates.";
            }
        } else {
            $keys = array();
            $start_no = $request->key_start_no;
            if ((int) $start_no < (int) $request->key_end_no) {
                for ($i = $start_no; $i <= $request->key_end_no; $i++) {
                    $k = CampaignKeys::find()->select(['id'])->where(['campaign_id' => $request->name->id, 'key' => $start_no])->asArray()->one();
                    if (!empty($k)) {
                        array_push($keys, $k);
                    }
                    $start_no++;
                }
            } else {
                $status = 0;
                $message = "Start key should be less then end key";
                $this->setHeader(200);
                return json_encode(array('status' => $status, 'message' => $message, JSON_PRETTY_PRINT));
            }
            if (!empty($keys)) {
                $keyIds = array_column($keys, 'id');

                $visitors = Visitor::find()
                        ->select(['id'])
                        ->where(['campaign_id' => $request->name->id, 'key_id' => $keyIds])
                        ->andWhere([">=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d', strtotime($request->start_date))])
                        ->andWhere(["<=", "DATE_FORMAT(last_visited, '%Y-%m-%d')", date('Y-m-d', strtotime($request->end_date))])
                        ->asArray()
                        ->all();

                if (!empty($visitors)) {
                    $ids = array_column($visitors, 'id');
                    Visitor::deleteAll(array('id' => $ids));
                    $save['campaign_id'] = $request->name->id;
                    $save['keys'] = $request->key_type;
                    $save['start'] = $request->key_start_no;
                    $save['end'] = $request->key_end_no;
                    Yii::$app->db->createCommand()->insert('delete_log', $save)->execute();
                    Yii::$app->db->createCommand()->update('campaign_keys', array('assigned' => 0), array("id" => $keyIds))->execute();
                    $status = 1;
                    $message = "Data successfully deleted";
                } else {
                    $status = 0;
                    $message = "No data exist between these dates.";
                }
            } else {
                $status = 0;
                $message = "No key exists.";
            }
        }

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'message' => $message, JSON_PRETTY_PRINT));
    }

    /**
     * Cleared Campaign Visits Data API
     * @return mixed
     */
    public function actionGetclearedcampaigndata() {
        $deletedData = DeleteLogs::find()->with('campaign')->asArray()->all();
        $result = array();
        if (!empty($deletedData)) {
            foreach ($deletedData as $key => $value) {
                $result[$key]['name'] = $value['campaign']['name'];
                $result[$key]['type'] = $value['keys'];
                $result[$key]['start_key'] = $value['start'];
                $result[$key]['end_key'] = $value['end'];
                $result[$key]['date'] = date('Y-m-d H:i:s', strtotime($value['created']));
            }
            $status = 1;
            $message = "Data retrieve successfully";
        } else {
            $status = 0;
            $message = "No Data exists.";
        }
        $this->setHeader(200);
        return json_encode(array('status' => $status, 'message' => $message, 'data' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Get SquibKey Runtime Visit Data on Dashboard API
     * @return mixed
     */
    public function actionGetsquibkeydata() {
        $id = $_GET['id'];
        $campaigns = Campaign::find()->select(['id'])->where(['user_id' => $id])->asArray()->all();
        $campaign_ids = array_column($campaigns, 'id');

        if (isset($_GET['load']) && $_GET['load'] == 'init') {
            for ($i = 300; $i > 0; $i--) {
                $visitorsCount[] = Visitor::find()->where(['campaign_id' => $campaign_ids])->andWhere("UNIX_TIMESTAMP(last_visited) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $i SECOND))")->count();
            }
            $this->setHeader(200);
            return json_encode(array('status' => 1, 'data' => $visitorsCount, JSON_PRETTY_PRINT));
        } else {
            $visitorCount = Visitor::find()->where(['campaign_id' => $campaign_ids])->andWhere("UNIX_TIMESTAMP(last_visited) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 300 SECOND))")->count();
            $this->setHeader(200);
            return json_encode(array('status' => 1, 'data' => (int) $visitorCount, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Get SquibCard Runtime Visit Data on Dashboard API
     * @return mixed
     */
    public function actionGetsquibcarddata() {
        $id = $_GET['id'];
        $user = User::find()->select(['instance_url'])->where(['id' => $id])->asArray()->one();
        if (isset($_GET['load']) && $_GET['load'] == 'init') {
            for ($i = 300; $i > 0; $i--) {
                $visitorsCount[] = Visitor::find()->where(['campaign_id' => 0, "domain" => $user['instance_url']])->andWhere("UNIX_TIMESTAMP(last_visited) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $i SECOND))")->count();
            }

            $this->setHeader(200);
            return json_encode(array('status' => 1, 'data' => $visitorsCount, JSON_PRETTY_PRINT));
        } else {
            $visitorCount = Visitor::find()->where(['campaign_id' => 0, "domain" => $user['instance_url']])->andWhere("UNIX_TIMESTAMP(last_visited) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 5 MINUTE))")->count();
            $this->setHeader(200);
            return json_encode(array('status' => 1, 'data' => (int) $visitorCount, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Get Campaign Name API
     * @return mixed
     */
    public function actionGet_campaign_name() {
        $slug = $_GET['slug'];
        $campaign = Campaign::find()->select(['name'])->where(['slug' => $slug])->asArray()->one();

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'name' => $campaign['name'], JSON_PRETTY_PRINT));
    }

    /**
     * Save/Update Private Branding API
     * @return mixed
     */
    public function actionPrivate_branding() {
        $user_id = $_REQUEST['user_id'];
        $root_path = Yii::getAlias('@webroot');

        if ($user_id && isset($_REQUEST["sitename"])) {
            $query = new Query;
            $query
                    ->from('private_branding')
                    ->where(['user_id' => $user_id])
                    ->select("*");

            $command = $query->createCommand();
            $brand = $command->queryOne();

            if (isset($_FILES["favicon"])) {
                $tempPath = $_FILES['favicon']['tmp_name'];
                $timestamp = time();
                $file_type1 = basename($_FILES["favicon"]["name"]);
                $file_type = pathinfo($file_type1, PATHINFO_EXTENSION);
                $randno = rand(1000, 999999);
                $savd_file_name = "favicon_" . $timestamp . "_" . $randno . "." . $file_type;
                $uploadPath = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'private_brand' . DIRECTORY_SEPARATOR . $savd_file_name;
                if (move_uploaded_file($tempPath, $uploadPath)) {
                    if ($brand['logo']) {
                        $favpath = $root_path . "/uploads/private_brand/" . $brand['favicon'];
                        @unlink($favpath);
                    }
                }
            } else {
                $savd_file_name = $brand['favicon'] ? $brand['favicon'] : "";
            }
            if (isset($_FILES["sitelogo"])) {
                $tempPath1 = $_FILES['sitelogo']['tmp_name'];
                $timestamp1 = time();
                $file_type2 = basename($_FILES["sitelogo"]["name"]);
                $file_type3 = pathinfo($file_type2, PATHINFO_EXTENSION);
                $randno1 = rand(1000, 999999);
                $savd_file_name1 = "sitelogo" . $timestamp1 . "_" . $randno1 . "." . $file_type3;
                $uploadPath1 = dirname(__FILE__) . '/../web' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'private_brand' . DIRECTORY_SEPARATOR . $savd_file_name1;
                if (move_uploaded_file($tempPath1, $uploadPath1)) {
                    if ($brand['favicon']) {
                        $logopath = $root_path . "/uploads/private_brand/" . $brand['logo'];
                        @unlink($logopath);
                    }
                }
            } else {
                $savd_file_name1 = $brand['logo'] ? $brand['logo'] : "";
            }


            $private_brand['favicon'] = $savd_file_name;
            $private_brand['logo'] = $savd_file_name1;
            $private_brand['user_id'] = $user_id;
            $private_brand['site_name'] = $_REQUEST['sitename'];

            if ($brand) {
                Yii::$app->db->createCommand()->update('private_branding', $private_brand, 'user_id =' . $brand['user_id'])->execute();
            } else {
                Yii::$app->db->createCommand()->insert('private_branding', $private_brand)->execute();
            }
            ///insert details in to database

            $response = 'Private Branding Updated Successfully';

            $status = 1;
        } else {
            $status = 0;
            $response = 'Error Occured';
        }

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'data' => $response, JSON_PRETTY_PRINT));
    }

    /**
     * Remove Private Branding API
     * @return mixed
     */
    public function actionRemovebrand() {
        $user_id = $_REQUEST['user_id'];
        $root_path = Yii::getAlias('@webroot');

        if ($user_id) {
            $query = new Query;
            $query
                    ->from('private_branding')
                    ->where(['user_id' => $user_id])
                    ->select("*");

            $command = $query->createCommand();
            $brand = $command->queryOne();

            if ($brand['favicon']) {
                $favpath = $root_path . "/uploads/private_brand/" . $brand['favicon'];
                @unlink($favpath);
            }

            if ($brand['logo']) {
                $logopath = $root_path . "/uploads/private_brand/" . $brand['logo'];
                @unlink($logopath);
            }

            Yii::$app->db->createCommand()->delete('private_branding', ['user_id' => $user_id])->execute();

            $response = 'Private Branding Reset Successfully';

            $status = 1;
        } else {
            $status = 0;
            $response = 'Error Occured';
        }

        $this->setHeader(200);
        return json_encode(array('status' => $status, 'data' => $response, JSON_PRETTY_PRINT));
    }

    /**
     * Get Private Branding API
     * @return mixed
     */
    public function actionSitepreferences() {
        $user_id = $_GET['user_id'];

        $ids = $this->getParents($user_id);
        $ids = array_merge(array($user_id), $ids);
        arsort($ids);

        foreach ($ids as $id) {
            $query = new Query;
            $query
                    ->from('private_branding')
                    ->where(['user_id' => $id])
                    ->select("*");

            $command = $query->createCommand();
            $brand = $command->queryOne();
            if ($brand)
                break;
        }

        $img_url = Yii::getAlias('@web') . "/uploads/";
        $data['site_name'] = $brand['site_name'] ? $brand['site_name'] : Yii::$app->params['SiteName'];
        $data['logo'] = $brand['logo'] ? $img_url . "private_brand/" . $brand['logo'] : "";
        $data['favicon'] = $brand['favicon'] ? $img_url . "private_brand/" . $brand['favicon'] : "";

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $data, JSON_PRETTY_PRINT));
    }

    /**
     * Get Private Branding of Campaign API
     * @return mixed
     */
    public function actionCampaignbrand() {
        $campaign_id = $_GET['campaign'];
        $campaign = Campaign::find()->select(['user_id'])->where(['slug' => $campaign_id])->asArray()->one();
        $user_id = $campaign['user_id'];
        $ids = $this->getParents($user_id);
        $ids = array_merge(array($user_id), $ids);
        arsort($ids);

        foreach ($ids as $id) {
            $query = new Query;
            $query
                    ->from('private_branding')
                    ->where(['user_id' => $id])
                    ->select("*");

            $command = $query->createCommand();
            $brand = $command->queryOne();
            if ($brand)
                break;
        }

        $img_url = Yii::getAlias('@web') . "/uploads/";
        $data['site_name'] = $brand['site_name'] ? $brand['site_name'] : Yii::$app->params['SiteName'];
        $data['logo'] = $brand['logo'] ? $img_url . "private_brand/" . $brand['logo'] : "";
        $data['favicon'] = $brand['favicon'] ? $img_url . "private_brand/" . $brand['favicon'] : "";

        $this->setHeader(200);
        return json_encode(array('status' => 1, 'data' => $data, JSON_PRETTY_PRINT));
    }

}
