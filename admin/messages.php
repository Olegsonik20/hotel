<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['delete'])){

   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_delete = $conn->prepare("SELECT * FROM `messages` WHERE id = ?");
   $verify_delete->execute([$delete_id]);

   if($verify_delete->rowCount() > 0){
      $delete_bookings = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
      $delete_bookings->execute([$delete_id]);
      $success_msg[] = 'Сообщение удалено!';
   }else{
      $warning_msg[] = 'Сообщение уже удалено!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Сообщения</title>

  
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

 
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   

<?php include '../components/admin_header.php'; ?>


<section class="grid">

   <h1 class="heading">Сообщения</h1>

   <div class="box-container">

   <?php
      $select_messages = $conn->prepare("SELECT * FROM `messages`");
      $select_messages->execute();
      if($select_messages->rowCount() > 0){
         while($fetch_messages = $select_messages->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p>Имя : <span><?= $fetch_messages['name']; ?></span></p>
      <p>Почта : <span><?= $fetch_messages['email']; ?></span></p>
      <p>Телефон : <span><?= $fetch_messages['number']; ?></span></p>
      <p>Сообщения : <span><?= $fetch_messages['message']; ?></span></p>
      <form action="" method="POST">
         <input type="hidden" name="delete_id" value="<?= $fetch_messages['id']; ?>">
         <input type="submit" value="Удалить сообщение" onclick="return confirm('Удалить сообщение?');" name="delete" class="btn">
      </form>
   </div>
   <?php
      }
   }else{
   ?>
   <div class="box" style="text-align: center;">
      <p>Сообщений не найдено!</p>
      <a href="dashboard.php" class="btn">Вернуться домой</a>
   </div>
   <?php
      }
   ?>

   </div>

</section>


















<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>


<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>