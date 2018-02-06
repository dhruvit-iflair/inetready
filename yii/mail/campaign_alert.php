<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */
?>

<table border="0" cellpadding="10" cellspacing="0" width="100%" id="emailBody" style="background:#fff">
    <tr>
        <td align="left" valign="top">
            <h3 style="margin-top:0px;"><?php echo $manager_name; ?> - Your Campaign URL was clicked.</h3>
            <p>Below are the details of visitor.</p>

            <p><b>Campaign Name : </b><?php echo $campaign_name; ?></p>
            <p><b>SquibKey : </b><?php echo $squibkey; ?></p>
            <br/>
            <p><b>Name : </b><?php echo $name; ?></p>
            <p><b>UID : </b><?php echo $uid; ?></p>
            <p><b>Organization : </b><?php echo $organization; ?></p>
            <p><b>ISP : </b><?php echo $isp; ?></p>
            <p><b>Location Near : </b><?php echo $location; ?></p>
            <img src="http://maps.googleapis.com/maps/api/staticmap?size=800x600&scale=2&format=png8&sensor=false&zoom=13&markers=color:red|label:|<?php echo $latlng; ?>" width="800" height="600"/>
        </td>
    </tr>
</table>
