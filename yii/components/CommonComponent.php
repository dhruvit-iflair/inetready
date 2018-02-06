<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use app\models\User;
use app\models\Visitor;
use app\models\CampaignKeys;
use yii\db\Query;

class CommonComponent extends Component {

    public function welcome() {
        echo "Hello..Welcome to MyComponent";
    }

    public function get_domain($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    public function encrptString($jsonArray, $iv) {
        $secret_key = 'valid_secret_key';
        $expireTimeCookie = time() + 2 * 24 * 60 * 60;

        // Encrypt $string
        return $encrypted_string = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret_key, $jsonArray, MCRYPT_MODE_CBC, $iv);
    }

    public function setHeader($status) {

        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        $content_type = "application/json; charset=utf-8";

        header($status_header);
        header('Content-type: ' . $content_type);
    }

    public function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public function checkHeaders($event) {

        $headers = Yii::$app->request->headers;
        //$headers = apache_request_headers();
        if (yii::$app->controller->id . "/" . yii::$app->controller->action->id != 'api/login' && yii::$app->controller->id . "/" . yii::$app->controller->action->id != 'api/forgotpassword' && yii::$app->controller->id . "/" . yii::$app->controller->action->id != 'api/resetpassword' && yii::$app->controller->id . "/" . yii::$app->controller->action->id != 'api/confirmpassword' && yii::$app->controller->id . "/" . yii::$app->controller->action->id != 'api/getsubdomain') {
            $auth = explode(" ", $headers['authorization']);
            $encrypted_string = base64_decode($auth[1]);
            $iv = base64_decode($auth[2]);
            $secret_key = 'secreatkey';
            $decrypted_string = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret_key, $encrypted_string, MCRYPT_MODE_CBC, $iv);
            $result = json_decode(trim($decrypted_string));

            if (!is_object($result) || time() > trim($result->expireTime) || trim($result->expireTime) == '') {
                echo json_encode(array('status' => 0, 'message' => 'logout'), JSON_PRETTY_PRINT);
                die;
            }//else{ echo 'login';}
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

    public function generateUserID() {
        $id = rand(10000000, 99999999);
        $user = User::find()
                ->select(['uid'])
                ->where(['uid' => $id])
                ->one();

        $visitor = Visitor::find()
                ->select(['uid'])
                ->where(['uid' => $id])
                ->one();

        if (empty($user) && empty($visitor)) {
            return $id;
        } else {
            $this->generateUserID();
        }
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

    /*
     * 	Get a unique slug
     */

    function slugUnique($title, $table_name, $field_name) {
        $slug = preg_replace("/-$/", "", preg_replace('/[^a-z0-9]+/i', "-", strtolower($title)));

        $query = new Query;
        $query
                ->from($table_name)
                ->andFilterWhere(['like', $field_name, "$slug-%", false])
                ->orderBy(['id' => 'DESC'])
                ->select("$field_name AS slug");
        $command = $query->createCommand();
        $result = $command->queryOne();
      

//        $query = "SELECT $field_name AS slug FROM $table_name WHERE $field_name  LIKE '$slug-%' ORDER BY id DESC LIMIT 1";
//        $result = $this->mysqli->query($query);
        if (!$result) {
            $query = new Query;
            $query
                    ->from($table_name)
                    ->andFilterWhere(['like', $field_name, "$slug%", false])
                    ->orderBy(['id' => 'DESC'])
                    ->select("$field_name AS slug");
            $command = $query->createCommand();
            $result = $command->queryOne();
              
//            $query = "SELECT $field_name AS slug FROM $table_name WHERE $field_name  LIKE '$slug%' ORDER BY id DESC LIMIT 1";
//            $result = $this->mysqli->query($query);
        }
     
        //$row = $result->fetch_assoc();
    
        if ($result['slug']) {
            $dd = explode("-", $result['slug']);

            $end = end($dd);
            if (is_numeric($end)) {
                return $slug . '-' . ($end + 1);
            } else {
                return $slug . '-' . '1';
            }
        } else {
            return $slug;
        }
    }

    function getBrand($user_id) {
        $query = new Query;
        $query
                ->from('private_branding')
                ->where(['user_id' => $user_id])
                ->select("*");

        $command = $query->createCommand();
        $brand = $command->queryOne();
        $img_url = Yii::$app->params['HTTP_URL'] . "/" . Yii::getAlias('@web') . "/uploads/";
        $data['site_name'] = $brand['site_name'] ? $brand['site_name'] : Yii::$app->params['SiteName'];
        $data['logo'] = $brand['logo'] ? $img_url . "private_brand/" . $brand['logo'] : "";
        return $data;
    }

}
