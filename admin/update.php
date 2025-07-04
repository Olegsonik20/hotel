<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 

   if(!empty($name)){
      $verify_name = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
      $verify_name->execute([$name]);
      if($verify_name->rowCount() > 0){
         $warning_msg[] = 'Имя уже занято!';
      }else{
         $update_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
         $update_name->execute([$name, $admin_id]);
         $success_msg[] = 'Имя обновлено!';
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass = $fetch_profile['password'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $c_pass = sha1($_POST['c_pass']);
   $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $warning_msg[] = 'Старый пароль не совпадает!';
      }elseif($c_pass != $new_pass){
         $warning_msg[] = 'Новый пароль не совпадает!';
      }else{
         if($new_pass != $empty_pass){
            $update_password = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
            $update_password->execute([$c_pass, $admin_id]);
            $success_msg[] = 'Пароль обновлен!';
         }else{
            $warning_msg[] = 'Введите новый пароль!';
         }
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>ОБновление</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   

<?php include '../components/admin_header.php'; ?>


<section class="form-container">

   <form action="" method="POST">
      <h3>Обновить профиль</h3>
      <input type="text" name="name" placeholder="<?= $fetch_profile['name']; ?>" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="old_pass" placeholder="Введите старый пароль" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="Введите новый пароль" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="c_pass" placeholder="Подтвердите новый пароль" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Обновить" name="submit" class="btn">
   </form>

</section>




















<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>


<script src="../js/admin_script.js"></script>

<?php include '../components/message.php'; ?>

</body>
</html>