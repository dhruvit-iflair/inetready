<?php

namespace app\controllers;

use Yii;
use app\models\Squibcard;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;

/**
 * SquibcardController implements the CRUD actions for Squibcard model.
 */
class SquibcardController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'getsquibcards' => ['get'],
                    'changestatus' => ['post'],
                    'thumbnail' => ['get'],
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
     * SquibCard List API.
     * @return mixed
     */
    public function actionGetsquibcards() {
        $query = new Query;
        $query
                ->from('squibcard')
                ->select("*");

        $command = $query->createCommand();
        $models = $command->queryAll();

        $result = array();
        $i = 0;
        foreach ($models as $model) {
            $result[$i] = $model;
            $result[$i++]['status'] = $model['status'] ? true : false;
        }

        $this->setHeader(200);

        return json_encode(array('status' => 1, 'data' => $result, JSON_PRETTY_PRINT));
    }

    /**
     * Change Status API.
     * @return mixed
     */
    public function actionChangestatus() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request) {
            foreach ($request as $squibcard) {
                $squibcard_mod['status'] = $squibcard->status ? 1 : 0;
                Yii::$app->db->createCommand()->update('squibcard', $squibcard_mod, 'id =' . $squibcard->id)->execute();
            }
            $status = 1;
            $response = 'Squibcard Status Updated.';
        } else {
            $status = 0;
            $response = 'Request invalid.';
        }
        $this->setHeader(200);
        echo json_encode(array('status' => $status, 'data' => $response), JSON_PRETTY_PRINT);
    }

    /**
     * SquibCard List API.
     * @return mixed
     */
    public function actionThumbnail() {
        $query = new Query;
        $query
                ->from('squibcard')
                ->where(['status' => 1])
                ->select("id,thumbnail,name");

        $command = $query->createCommand();
        $models = $command->queryAll();

//        $result = array();
//        $i = 0;
//        foreach ($models as $model) {
//            $result[$i] = $model;
//            $result[$i++]['status'] = $model['status'] ? true : false;
//        }

        $this->setHeader(200);

        return json_encode(array('status' => 1, 'data' => $models, JSON_PRETTY_PRINT));
    }

    /**
     * Lists all Squibcard models.
     * @return mixed
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider([
            'query' => Squibcard::find(),
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Squibcard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Squibcard model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Squibcard();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Squibcard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Squibcard model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Squibcard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Squibcard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Squibcard::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
