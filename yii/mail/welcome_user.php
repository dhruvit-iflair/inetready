<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
?>
<table border="0" cellpadding="10" cellspacing="0" width="100%" id="emailBody" style="background:#fff">
    <tr>
        <td align="left" valign="top">
            <h3>Hi <?php echo $username; ?>,</h3>
            <p>Welcome to <i><?php echo Yii::$app->params['SiteName']; ?></i>! You're account has been created. To setup, share and save on <i><?php echo Yii::$app->params['SiteName']; ?></i>, login...
            </p>
            <p><a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>
            <hr>
            <p><b><?php echo Yii::$app->params['SiteName']; ?> Account Details</b></p>

            <p>
                <b>Email: </b> <?php echo $email; ?><br/>
                <b>Password: </b> <?php echo $password; ?><br/>
            </p>

        </td>
    </tr>
</table>