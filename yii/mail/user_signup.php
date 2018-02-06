<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
?>
<table border="0" cellpadding="10" cellspacing="0" width="100%" id="emailBody" style="background:#fff">
    <tr>
        <td align="left" valign="top">
            <h3 style="margin-top:0px;">Account Confirmation</h3>
            <p>Click the link below to confirm and activate your <?php echo Yii::$app->params['SiteName']; ?> account.</p>
            <p><a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>
            <p style="color:#6D6D6D;font-size:12px;padding-bottom:25px;">(If you can not click the link can copy and paste the link into your browser.)</p>
        </td>
    </tr>
</table>
