<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
?>

<table border="0" cellpadding="10" cellspacing="0" width="100%" id="emailBody" style="background:#fff">
    <tr>
        <td align="left" valign="top">
            <h3 style="margin-top:0px;">Message Form</h3>
            <p>Below are the details of user.</p>
            <p><b>Name : </b><?php echo $name; ?></p>
            <p><b>Email : </b><?php echo $email; ?></p>
            <p style="padding-bottom:25px;"><b>Message : </b><?php echo $message; ?></p>
        </td>
    </tr>
</table>
