<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Номера</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="text-left">
  <div class="flex">
  <h3>
  Премиум номер
  </h3>
  <a href="index.php#reservation" class="btn">Зарезервировать</a>
</div>
</section>

<section class="gallery premium-slider" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/premium1.jpg" class="swiper-slide" alt="">
         <img src="images/premium2.jpg" class="swiper-slide" alt="">
         <img src="images/premium3.jpg" class="swiper-slide" alt="">
         <img src="images/premium4.jpg" class="swiper-slide" alt="">
         <img src="images/premium5.jpg" class="swiper-slide" alt="">
         <img src="images/premium6.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<section class="text-right">
  <div class="flex">
  <h3>
  Стандартный номер
  </h3>
  <a href="index.php#reservation" class="btn">Зарезервировать</a>
</div>
</section>

<section class="gallery premium-slider" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/standart1.jpg" class="swiper-slide" alt="">
         <img src="images/standart2.jpg" class="swiper-slide" alt="">
         <img src="images/standart3.jpg" class="swiper-slide" alt="">
         <img src="images/standart4.jpg" class="swiper-slide" alt="">
         <img src="images/standart5.jpg" class="swiper-slide" alt="">
         <img src="images/standart6.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<section class="text-right">
  <div class="flex">
  <h3>
  Люкс номер
  </h3>
  <a href="index.php#reservation" class="btn">Зарезервировать</a>
</div>
</section>

<section class="gallery premium-slider" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/luxe1.jpg" class="swiper-slide" alt="">
         <img src="images/luxe2.jpg" class="swiper-slide" alt="">
         <img src="images/luxe3.jpg" class="swiper-slide" alt="">
         <img src="images/luxe4.jpg" class="swiper-slide" alt="">
         <img src="images/luxe5.jpg" class="swiper-slide" alt="">
         <img src="images/luxe6.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>








<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {

  const forms = ['availability', 'reservation'];
  
  forms.forEach(form => {
    const typeSelect = document.getElementById(`${form}_type`);
    const roomSelect = document.getElementById(`${form}_room`);
    
    if (typeSelect && roomSelect) {

      function updateRooms() {
        const selectedType = typeSelect.value;
        Array.from(roomSelect.options).forEach(option => {
          option.style.display = option.getAttribute('data-type') === selectedType ? '' : 'none';
        });
        

        const currentRoom = roomSelect.options[roomSelect.selectedIndex];
        if (!currentRoom || currentRoom.style.display === 'none') {
 
          const firstVisible = Array.from(roomSelect.options).find(opt => opt.style.display !== 'none');
          if (firstVisible) roomSelect.value = firstVisible.value;
        }
      }
      

      function updateType() {
        const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
        if (selectedRoom) {
          typeSelect.value = selectedRoom.getAttribute('data-type');
        }
      }
      

      updateRooms();
      

      typeSelect.addEventListener('change', updateRooms);
      roomSelect.addEventListener('change', updateType);
    }
  });
});
</script>
</html>
