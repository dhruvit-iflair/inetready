<?php

namespace app\controllers;

use Yii;
use app\models\Message;
use app\models\Share;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'sendmessage' => ['post'],
                    'share' => ['post'],
                    'userlikes' => ['post'],
                    'postcomment' => ['post'],
                    'deletecomment' => ['post'],
                    'followuser' => ['post'],
                ],
            ],
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
     * Send message API.
     * @return mixed
     */
    public function actionSendmessage() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {

            $query = new Query;
            $query
                    ->from('user')
                    ->where(['id' => $request->to_user_id])
                    ->select("email_id, admin_name");

            $command = $query->createCommand();
            $user = $command->queryOne();


            $message = new Message;
            $message->to_user_id = $request->to_user_id;
            $message->sender_name = $request->sender_name;
            $message->sender_email = $request->sender_email;
            $message->message = $request->message;
            $message->sent_time = date("Y-m-d H:i:s");

            $message->insert();

            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand(1);
            Yii::$app->mailer->compose('new_message', [
                        'name' => $request->sender_name,
                        'email' => $request->sender_email,
                        'message' => $message->message
                    ])
                    ->setFrom([$message->sender_email => $request->sender_name])
                    ->setTo($user['email_id'])
                    ->setSubject('New Message on ' . Yii::$app->params['SiteName'])
                    //->setTextBody($message->message)
                    ->send();


            $status = 1;
            $response = 'Your message has been sent.';
        } else {
            $status = 0;
            $response = 'No Message Sent';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * User Likes/Dislikes API.
     * @return mixed
     */
    public function actionUserlikes() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {

            $query = new Query;
            $query
                    ->from('like')
                    ->where(['profile_id' => $request->profile_id, 'user_id' => $request->user_id])
                    ->select("*");

            $command = $query->createCommand();
            $like = $command->queryOne();

            if ($like) {
                $update['like'] = $request->like;
                Yii::$app->db->createCommand()->update('like', $update, 'id =' . $like['id'])->execute();
            } else {
                $create['user_id'] = $request->user_id;
                $create['profile_id'] = $request->profile_id;
                $create['like'] = $request->like;
                $create['created'] = date("Y-m-d H:i:s");
                Yii::$app->db->createCommand()->insert('like', $create)->execute();
            }

            $response['likes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 1, 'profile_id' => $request->profile_id])
                    ->count();

            $response['dislikes'] = (new yii\db\Query())
                    ->from('like')
                    ->where(['like' => 0, 'profile_id' => $request->profile_id])
                    ->count();

            $status = 1;
            $response['message'] = 'You like/dislike the user.';
        } else {
            $status = 0;
            $response = 'No Like/Dislike';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Post Comment API.
     * @return mixed
     */
    public function actionPostcomment() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {


            $create['user_id'] = $request->user_id;
            $create['profile_id'] = $request->profile_id;
            $create['comment'] = $request->comment;
            $create['created'] = date("Y-m-d H:i:s");
            Yii::$app->db->createCommand()->insert('comment', $create)->execute();


            $query = (new Query())
                    ->from('comment')
                    ->where(['profile_id' => $request->profile_id])
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
            $status = 1;
            $response['message'] = 'You like/dislike the user.';
        } else {
            $status = 0;
            $response = 'No Like/Dislike';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Delete Comment API.
     * @return mixed
     */
    public function actionDeletecomment() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {
            Yii::$app->db->createCommand()->delete('comment', 'id = ' . $request->comment_id)->execute();
            $status = 1;
            $response['message'] = 'Comment Deleted.';
        } else {
            $status = 0;
            $response = 'No Comments';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Follower/Following API.
     * @return mixed
     */
    public function actionFollowuser() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {

            $query = new Query;
            $query
                    ->from('follower')
                    ->where(['follower_id' => $request->follower_id, 'following_id' => $request->following_id])
                    ->select("*");

            $command = $query->createCommand();
            $follower = $command->queryOne();

            if ($follower) {
                $follow_status = $follower['status'] ? 0 : 1;
                $update['status'] = $follow_status;
                Yii::$app->db->createCommand()->update('follower', $update, 'id =' . $follower['id'])->execute();
            } else {
                $follow_status = 1;
                $create['follower_id'] = $request->follower_id;
                $create['following_id'] = $request->following_id;
                $create['status'] = $follow_status;
                $create['created'] = date("Y-m-d H:i:s");
                Yii::$app->db->createCommand()->insert('follower', $create)->execute();
            }
            $status = 1;

            $response['followers'] = (new yii\db\Query())
                    ->from('follower')
                    ->where(['following_id' => $request->following_id, 'status' => 1])
                    ->count();

            $response['follow_status'] = $follow_status;
            $response['message'] = 'You follow/unfollow the user.';
        } else {
            $status = 0;
            $response = 'No Like/Dislike';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * Share via Email API.
     * @return mixed
     */
    public function actionShare() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {

            $query = new Query;
            $query
                    ->from('user')
                    ->where(['id' => $request->to_user_id])
                    ->select("email_id, admin_name");

            $command = $query->createCommand();
            $user = $command->queryOne();


            $message = new Share;
            $message->to_user_id = $request->to_user_id;
            $message->sender_name = $request->sender_name;
            $message->sender_email = $request->sender_email;
            $message->message = $request->message;
            $message->sent_time = date("Y-m-d H:i:s");

            $message->insert();

            $messageToSend = $request->sender_name . " shared this...\n\n" . $request->message;
            Yii::$app->view->params = Yii::$app->commoncomponent->getBrand(1);
            Yii::$app->mailer->compose()
                    ->setFrom([$message->sender_email => $request->sender_name])
                    ->setTo($request->to_email)
                    ->setSubject($request->subject)
                    ->setTextBody($messageToSend)
                    ->send();


            $status = 1;
            $response = 'Your message has been sent.';
        } else {
            $status = 0;
            $response = 'No Email Sent';
        }

        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

}
