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
            <h3 style="margin-top:0px;">Campaign Confirmed</h3>
            <p>Your campaign has been confirmed and keys are generated</p>
            <p>
                <b>Campaign Name: </b> <?php echo $campaign_name; ?><br/>
                 <b>Start Date: </b> <?php echo $start_date; ?><br/>
                <b>End Date: </b> <?php echo $end_date; ?><br/>
                <b>No. of Keys: </b> <?php echo $no_of_keys; ?><br/>
            </p>
        </td>
    </tr>
</table>