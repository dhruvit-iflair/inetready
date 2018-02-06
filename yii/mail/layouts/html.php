<?php

use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body style = "background:#E1E1E1">
        <?php $this->beginBody() ?>
        <table border = "0" cellpadding = "10" cellspacing = "0" height = "100%" width = "100%" id = "bodyTable" style = "background:#F7F7F7;">
            <tr>
                <td align = "center" valign = "top">
                    <table border = "0" cellpadding = "0" cellspacing = "0" width = "100%" id = "emailContainer">

                        <tr>
                            <td align = "center" style = "background:#fff;">
                                <table border = "0" cellpadding = "10" cellspacing = "0" width = "100%" id = "emailHeader">
                                    <tr>
                                        <td >
                                            <?php if (Yii::$app->view->params['logo'] == '') { ?>
                                                <a href = "<?php echo Yii::$app->params['HTTP_URL']; ?>" style = "display: table-cell; height: 50px; vertical-align: middle;padding-right:10px;"><img alt = "" src = "<?php echo Yii::$app->params['HTTP_URL'] . "/" . Yii::getAlias('@web') . "/images/logo-solo.png"; ?>" class = "logo" alt="Logo" width = "50"></a>
                                                <span style = "color:#000; display: table-cell; height: 50px; vertical-align: middle; text-align:left; font-size:18px; line-height:20px; font-family:Arial, Helvetica, sans-serif;"><b style = "font-size:21px;"><?php echo Yii::$app->view->params['site_name']; ?> </b></span>
                                            <?php } else { ?>
                                                <a href = "<?php echo Yii::$app->params['HTTP_URL']; ?>" style = "display: table-cell; height: 50px; vertical-align: middle;padding-right:10px;"><img alt = "" src = "<?php echo Yii::$app->view->params['logo']; ?>" class = "logo" alt="Logo" width = "150"></a>
                                            <?php } ?>
                                            <hr style = "border-bottom:#E1E1E1 solid 2px;float:left;width:100%; margin-bottom:0px;">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align = "center" valign = "top">
                                <?= $content ?>
                            </td>
                        </tr>
                        <tr>
                            <td align = "center" valign = "top">
                                <table border = "0" cellpadding = "0" cellspacing = "0" width = "100%" id = "emailFooter">
                                    <tr>
                                        <td align = "left" valign = "top" style = "color:#6D6D6D;padding:10px 0;">
<!--                                                <p style = "font-size:10px;"><b style="width:100%">About iRefer Book</b><br/>iRefer Book lets you create your own customizable resource directory mobile app that markets you, the resources you know/trust and that can be shared with clients and contacts to help them find quality sources.
                                            </p> -->
                                            <p style = "font-size:10px;">
                                                You are receiving this email from who has sent you this invitation via <?php echo Yii::$app->view->params['site_name']; ?>. This email is a notification email only, "replies" will not be received.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
