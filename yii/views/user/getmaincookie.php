<?php
/* @var $this yii\web\View */
?>
<h1>visitor/index</h1>
 <script type="text/javascript">
        function RefreshParent() {
            alert();
            if (window.opener != null && !window.opener.closed) {
                window.opener.location.reload();
            }
        }
        window.onbeforeunload = RefreshParent;
    </script>
<p>
    You may change the content of this page by modifying
   <?php  //print_r($this->response); ?>
</p>
