<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

if(isset($_POST['cancel'])){

   $booking_id = $_POST['booking_id'];
   $booking_id = filter_var($booking_id, FILTER_SANITIZE_STRING);

   $verify_booking = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
   $verify_booking->execute([$booking_id]);

   if($verify_booking->rowCount() > 0){
      $delete_booking = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
      $delete_booking->execute([$booking_id]);
      $success_msg[] = 'Бронь успешно отменена!';
   }else{
      $warning_msg[] = 'Бронь уже отменена!';
   }
   
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Брони</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>



<section class="bookings">

   <h1 class="heading">Мои брони</h1>

   <div class="box-container">

   <?php
     $select_bookings = $conn->prepare("
    SELECT 
        b.*, 
        rt.type_name,
        r.room_number 
    FROM bookings b
    JOIN room_types rt ON b.type_id = rt.type_id
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = ?
");

$select_bookings->execute([$user_id]);

if($select_bookings->rowCount() > 0) {
    while($fetch_booking = $select_bookings->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p>Имя : <span><?= $fetch_booking['name']; ?></span></p>
      <p>Почта : <span><?= $fetch_booking['email']; ?></span></p>
      <p>Телефон : <span><?= $fetch_booking['number']; ?></span></p>
       <p>Тип номера: <span><?= $fetch_booking['type_name'] ?></span></p>
        <p>Номер комнаты: <span><?= $fetch_booking['room_number'] ?></span></p>
        <p>Дата заезда: <span><?= $fetch_booking['check_in'] ?></span></p>
        <p>Дата выезда: <span><?= $fetch_booking['check_out'] ?></span></p>
        <p>Взрослые: <span><?= $fetch_booking['adults'] ?></span></p>
        <p>Дети: <span><?= $fetch_booking['childs'] ?></span></p>
      <p>Номер брони : <span><?= $fetch_booking['booking_id']; ?></span></p>
      <form action="" method="POST">
         <input type="hidden" name="booking_id" value="<?= $fetch_booking['booking_id']; ?>">
         <input type="submit" value="Отмена брони" name="cancel" class="btn" onclick="return confirm('Отменить бронь?');">
      </form>
   </div>
   <?php
    }
   }else{
   ?>   
   <div class="box" style="text-align: center;">
      <p style="padding-bottom: .5rem; text-transform:capitalize;">Бронь не найдена!</p>
      <a href="index.php#reservation" class="btn">Забронировать</a>
   </div>
   <?php
   }
   ?>
   </div>

</section>



























<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>


<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>