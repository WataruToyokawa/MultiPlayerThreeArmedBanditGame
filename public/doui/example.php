<?php
    session_start();
?>
    <form action="example.php" method="post">
    <p>Enter text shown below:</p>
    <p><img src="index.php?<?php echo session_name()?>=<?php echo session_id()?>"></p>
    <p><input type="text" name="keystring"></p>
    <p><input type="submit" value="Check"></p>
    </form>

<?php
    if(count($_POST)>0){
        if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring']){
            //正解の場合のスクリプト
            echo "Correct";
        }else{
            //不正解の場合のスクリプト
            echo "Wrong";
        }
    }
    unset($_SESSION['captcha_keystring']);
?>
