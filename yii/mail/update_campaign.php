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
            <h3 style="margin-top:0px;">Campaign Updated and Keys are increased</h3>
            <p>Below are the details:</p>
            <p>
                <b>Campaign Name: </b> <?php echo $campaign_name; ?><br/>
                <b>No. of Keys Exist: </b> <?php echo $no_of_keys; ?><br/>
                <b>No. of Keys Added: </b> <?php echo $new_keys; ?><br/>
                <b>Date & Time: </b> <?php echo $time; ?><br/>
                <b>User name: </b> <?php echo $name; ?><br/>
                <b>Email: </b> <?php echo $email; ?><br/>
                <b>Organization: </b> <?php echo $organization; ?><br/>
                <b>Work Phone: </b> <?php echo $work_phone; ?><br/>
            </p>
            <?php if ($role == 'admin' && $new_keys > 1000) { ?>
                <p>
                    <a style="background: #65B951;color:#fff;padding:10px;margin-right: 10px;" href="<?php echo $confirm; ?>">Confirm</a>
                    <a  style="background: #E14635;color:#fff;padding:10px;" href="<?php echo $cancel; ?>">Cancel</a>
                </p>    
            <?php } ?>
        </td>
    </tr>
</table>