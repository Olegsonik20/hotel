<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

if(isset($_POST['check'])){
    
    $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_STRING);
    $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_STRING);
    $type_id = filter_var($_POST['type_id'], FILTER_VALIDATE_INT);
    $room_id = filter_var($_POST['room_id'], FILTER_VALIDATE_INT);

    
    $check_availability = $conn->prepare("
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND (
            (check_in <= ? AND check_out >= ?) 
            OR (check_in <= ? AND check_out >= ?)
        )
    ");
    
    $check_availability->execute([
        $room_id,
        $check_in, $check_in,
        $check_out, $check_out
    ]);

    if($check_availability->rowCount() > 0) {
        $warning_msg[] = 'Этот номер занят на выбранные даты!';
    } else {
        
        $type_availability = $conn->prepare("
            SELECT COUNT(*) as total_booked 
            FROM bookings 
            WHERE type_id = ? 
            AND (
                (check_in <= ? AND check_out >= ?) 
                OR (check_in <= ? AND check_out >= ?)
            )
        ");
        
        $type_availability->execute([
            $type_id,
            $check_in, $check_in,
            $check_out, $check_out
        ]);
        
        $result = $type_availability->fetch(PDO::FETCH_ASSOC);
        
        
        if($result['total_booked'] >= 3) {
            $warning_msg[] = 'Все номера этого типа заняты!';
        } else {
            $success_msg[] = 'Номер доступен для бронирования!';
        }
    }
}

if(isset($_POST['book'])){
    $booking_id = create_unique_id();
    
    
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $type_id = filter_var($_POST['type_id'], FILTER_VALIDATE_INT);
    $room_id = filter_var($_POST['room_id'], FILTER_VALIDATE_INT);
    $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_STRING);
    $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_STRING);
    $adults = filter_var($_POST['adults'], FILTER_VALIDATE_INT);
    $childs = filter_var($_POST['childs'], FILTER_VALIDATE_INT);

    
    $check_booking = $conn->prepare("
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND (
            (check_in <= ? AND check_out >= ?) 
            OR (check_in <= ? AND check_out >= ?)
        )
    ");
    
    $check_booking->execute([
        $room_id,
        $check_in, $check_in,
        $check_out, $check_out
    ]);

    if($check_booking->rowCount() > 0) {
        $warning_msg[] = 'Этот номер уже занят на выбранные даты!';
    } else {
        
        $verify_bookings = $conn->prepare("
            SELECT * FROM bookings 
            WHERE user_id = ? 
            AND type_id = ?
            AND room_id = ?
            AND check_in = ?
            AND check_out = ?
        ");
        
        $verify_bookings->execute([
            $user_id, 
            $type_id,
            $room_id,
            $check_in,
            $check_out
        ]);

        if($verify_bookings->rowCount() > 0){
            $warning_msg[] = 'Этот номер уже забронирован вами!';
        } else {
            
            $book_room = $conn->prepare("
                INSERT INTO bookings 
                (booking_id, user_id, name, email, number, 
                type_id, room_id, check_in, check_out, adults, childs) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            
            $book_room->execute([
                $booking_id,
                $user_id,
                $name,
                $email,
                $number,
                $type_id,
                $room_id,
                $check_in,
                $check_out,
                $adults,
                $childs
            ]);
            
            $success_msg[] = 'Номер успешно забронирован!';
        }
    }
}

if(isset($_POST['send'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $message = $_POST['message'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);

   $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      $warning_msg[] = 'Сообщение уже отправлено!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$id, $name, $email, $number, $message]);
      $success_msg[] = 'Сообщение отправлено!';
   }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Главная</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>



<section class="home" id="home">

   <div class="swiper home-slider">

      <div class="swiper-wrapper">

         <div class="box swiper-slide">
            <img src="images/standart3.jpg" alt="">
            <div class="flex">
               <h3>&nbsp;&nbsp;&nbsp;&nbsp;Люксовые комнаты</h3>
               <a href="#availability" class="btn">Проверить наличие</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-2.jpg" alt="">
            <div class="flex">
               <h3>&nbsp;&nbsp;&nbsp;&nbsp;Еда и напитки</h3>
               <a href="#reservation" class="btn">Зарезервировать</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="images/home-img-3.jpg" alt="">
            <div class="flex">
               <h3>&nbsp;&nbsp;&nbsp;&nbsp;Люксовые залы</h3>
               <a href="#contact" class="btn">Свяжитесь с нами</a>
            </div>
         </div>

      </div>
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>

   

</section>
<section class="availability" id="availability">

   <form action="" method="post">
      <div class="flex">
         <div class="box">
            <p>Тип номера <span>*</span></p>
               <select name="type_id" id="availability_type" class="input" required>
                  <?php
                   $types = $conn->query("SELECT * FROM room_types");
                  while($type = $types->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="'.$type['type_id'].'">'.$type['type_name'].'</option>';
                  }
                  ?>
               </select>
         </div>
         <div class="box">
            <p>Номер комнаты <span>*</span></p>
               <select name="room_id" id="availability_room" class="input" required>
                  <?php
                  $rooms = $conn->query("SELECT * FROM rooms");
                  while($room = $rooms->fetch(PDO::FETCH_ASSOC)) {
                     echo '<option value="'.$room['room_id'].'" data-type="'.$room['type_id'].'">№'.$room['room_number'].'</option>';
                  }
                  ?>
               </select>
         </div>
         <div class="box">
            <p>Заселение <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Выселение <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         
         <div class="box">
            <p>Взрослые <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1">1 Взрослый</option>
               <option value="2">2 Взрослых</option>
               <option value="3">3 Взрослых</option>
               <option value="4">4 Взрослых</option>
               <option value="5">5 Взрослых</option>
               <option value="6">6 Взрослых</option>
            </select>
         </div>
         <div class="box">
            <p>Дети <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="-">0 Детей</option>
               <option value="1">1 Ребенок</option>
               <option value="2">2 Ребенка</option>
               <option value="3">3 Ребенка</option>
               <option value="4">4 Ребенка</option>
               <option value="5">5 Детей</option>
               <option value="6">6 Детей</option>
            </select>
         </div>
      </div>
      <input type="submit" value="Проверить наличие" name="check" class="btn">
   </form>

</section>



<section class="about" id="about">

   <div class="row">
      <div class="image">
         <img src="images/about-img-1.jpg" alt="">
      </div>
      <div class="content">
         <h3>Лучший персонал</h3>
         <p>Наслаждайтесь высочайшим уровнем обслуживания в нашем отеле благодаря нашему лучшему персоналу. Наши дружелюбные и профессиональные сотрудники всегда готовы ответить на ваши запросы и обеспечить незабываемое пребывание для каждого гостя.</p>
         <a href="#reservation" class="btn">Сделать бронь</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="images/about-img-2.jpg" alt="">
      </div>
      <div class="content">
         <h3>Лучшая еда</h3>
         <p>Гостиница предлагает высококлассные рестораны и кафе с изысканным меню, включающим разнообразные блюда из мировой кухни. Наши шеф-повара используют только свежие и качественные ингредиенты, чтобы обеспечить незабываемое гастрономическое испытание для каждого гостя.</p>
         <a href="#contact" class="btn">Свяжитесь с нами</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="images/about-img-3.jpg" alt="">
      </div>
      <div class="content">
         <h3>Бассейн</h3>
         <p>Откройте для себя удивительную возможность расслабиться и охладиться в нашем превосходном бассейне. Насладитесь кристально чистой водой, уникальным дизайном и расслабляющей атмосферой. </p>
         <a href="#availability" class="btn">Проверить наличие</a>
      </div>
   </div>

</section>



<section class="services">

   <div class="box-container">

      <div class="box">
         <img src="images/icon-1.png" alt="">
         <h3>Еда и напитки</h3>
         <p>Откройте для себя изысканное меню ресторана гостиницы с широким выбором блюд и напитков.</p>
      </div>

      <div class="box">
         <img src="images/icon-2.png" alt="">
         <h3>Обед на свежем воздухе</h3>
         <p>Насладитесь изысканными блюдами под открытым небом в нашем ресторане.</p>
      </div>

      <div class="box">
         <img src="images/icon-3.png" alt="">
         <h3>Вид на пляж</h3>
         <p>Роскошный отель с беспрепятственным видом на пляж и море. Идеальное место для романтического отдыха и релаксации.</p>
      </div>

      <div class="box">
         <img src="images/icon-4.png" alt="">
         <h3>Украшения</h3>
         <p>Очарование в каждой детали: уютные декорации в стиле современного отеля.</p>
      </div>

      <div class="box">
         <img src="images/icon-5.png" alt="">
         <h3>Бассейн</h3>
         <p>Уединенный открытый бассейн в окружении пышных садов и панорамных видов приглашает гостей гостиницы насладиться идеальным отдыхом под открытым небом.</p>
      </div>

      <div class="box">
         <img src="images/icon-6.png" alt="">
         <h3>Забронируйте пляж</h3>
         <p>Ощутите праздник и релаксацию на нашем приватном пляже.</p>
      </div>

   </div>

</section>



<section class="reservation" id="reservation">

   <form action="" method="post">
      <h3>Совершите бронь</h3>
      <div class="flex">
         <div class="box">
  <p>Тип номера <span>*</span></p>
  <select name="type_id" id="reservation_type" class="input" required>
    <?php
    $types = $conn->query("SELECT * FROM room_types");
    while($type = $types->fetch(PDO::FETCH_ASSOC)) {
      echo '<option value="'.$type['type_id'].'">'.$type['type_name'].' - '.$type['price'].' руб.</option>';
    }
    ?>
  </select>
</div>

<div class="box">
  <p>Номер комнаты <span>*</span></p>
  <select name="room_id" id="reservation_room" class="input" required>
    <?php
    $rooms = $conn->query("SELECT * FROM rooms");
    while($room = $rooms->fetch(PDO::FETCH_ASSOC)) {
      echo '<option value="'.$room['room_id'].'" data-type="'.$room['type_id'].'">№'.$room['room_number'].'</option>';
    }
    ?>
  </select>
</div>
         <div class="box">
            <p>Ваше имя <span>*</span></p>
            <input type="text" name="name" maxlength="50" required placeholder="Введите Имя" class="input">
         </div>
         <div class="box">
            <p>Ваша почта <span>*</span></p>
            <input type="email" name="email" maxlength="50" required placeholder="Введите почту" class="input">
         </div>
         <div class="box">
            <p>Ваш телефон <span>*</span></p>
            <input type="number" name="number" maxlength="11" min="0" max="99999999999" required placeholder="Введите номер" class="input">
         </div>
         <div class="box">
            <p>Заселение <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Выселение <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>Взрослые <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1" selected>1 Взрослый</option>
               <option value="2">2 Взрослых</option>
               <option value="3">3 Взрослых</option>
               <option value="4">4 Взрослых</option>
               <option value="5">5 Взрослых</option>
               <option value="6">6 Взрослых</option>
            </select>
         </div>
         <div class="box">
            <p>Дети <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="0" selected>0 Детей</option>
               <option value="1">1 Ребенок</option>
               <option value="2">2 Ребенка</option>
               <option value="3">3 Ребенка</option>
               <option value="4">4 Ребенка</option>
               <option value="5">5 Детей</option>
               <option value="6">6 Детей</option>
            </select>
         </div>
      </div>
      <input type="submit" value="Забронировать" name="book" class="btn">
   </form>

</section>
 

<section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/gallery-img-1.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-2.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-3.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-4.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-5.jpg" class="swiper-slide" alt="">
         <img src="images/gallery-img-6.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>


<section class="contact" id="contact">

   <div class="row">

      <form action="" method="post">
         <h3>Отправьте нам сообщение/отзыв</h3>
         <input type="text" name="name" required maxlength="50" placeholder="Введите имя" class="box">
         <input type="email" name="email" required maxlength="50" placeholder="Введите почту" class="box">
         <input type="number" name="number" required maxlength="11" min="0" max="99999999999" placeholder="Введите номер" class="box">
         <textarea name="message" class="box" required maxlength="1000" placeholder="Введите сообщение" cols="30" rows="10"></textarea>
         <input type="submit" value="Отправить сообщение" name="send" class="btn">
      </form>

      <div class="faq">
         <h3 class="title">Часто задаваемые вопросы</h3>
         <div class="box active">
            <h3>Как отменить бронь?</h3>
            <p>Бронь можно отменить во кладке "Мои брони".</p>
         </div>
         <div class="box">
            <h3>Как узнать есть ли свободные номера?</h3>
            <p>Вы можете проверить наличие номеров в специльном блоке "Проверить наличие".</p>
         </div>
         <div class="box">
            <h3>Какие существуют методы оплаты?</h3>
            <p>В данный момент оплата происходи по прибитию в гостиницу.</p>
         </div>
         <div class="box">
            <h3>Существует ли ситсема промокодов?</h3>
            <p>У нас не предусмотрена система купонов и промокодов.</p>
         </div>
         <div class="box">
            <h3>Существуют ли возрастные ограничения?</h3>
            <p>У нас нет возрастных ограничений, кроме предусмотренных законодательством.</p>
         </div>
      </div>

   </div>

</section>


<section class="reviews" id="reviews">

   <div class="swiper reviews-slider">

      <div class="swiper-wrapper">
         <div class="swiper-slide box">
            <img src="images/pic-1.jpg" alt="">
            <h3>Евгений</h3>
            <p>Прекрасная гостиница с удивительным видом на море и отличным обслуживанием.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-2.jpg" alt="">
            <h3>Лилия</h3>
            <p>Отличное соотношение цены и качества, чистые номера и удобные кровати.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-3.jpg" alt="">
            <h3>Сергей</h3>
            <p>Приветливый персонал, вкусные завтраки и удобное расположение.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-4.jpg" alt="">
            <h3>Мария</h3>
            <p>Уютная гостиница с красивым дизайном интерьера и отличными условиями для отдыха.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-5.jpg" alt="">
            <h3>Афансий</h3>
            <p>Замечательная атмосфера, отличная звукоизоляция номеров и удобная парковка.</p>
         </div>
         <div class="swiper-slide box">
            <img src="images/pic-6.jpg" alt="">
            <h3>Егор</h3>
            <p>Отличная гостиница для семейного отдыха, ухоженная территория и бассейн, детская площадка и хороший ресторан.</p>
         </div>
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
  // Обрабатываем обе формы
  const forms = ['availability', 'reservation'];
  
  forms.forEach(form => {
    const typeSelect = document.getElementById(`${form}_type`);
    const roomSelect = document.getElementById(`${form}_room`);
    
    if (typeSelect && roomSelect) {
      // Обновляем комнаты при изменении типа
      function updateRooms() {
        const selectedType = typeSelect.value;
        Array.from(roomSelect.options).forEach(option => {
          option.style.display = option.getAttribute('data-type') === selectedType ? '' : 'none';
        });
        
        // Проверяем текущий выбранный номер
        const currentRoom = roomSelect.options[roomSelect.selectedIndex];
        if (!currentRoom || currentRoom.style.display === 'none') {
          // Выбираем первый доступный номер
          const firstVisible = Array.from(roomSelect.options).find(opt => opt.style.display !== 'none');
          if (firstVisible) roomSelect.value = firstVisible.value;
        }
      }
      
      // Обновляем тип при изменении комнаты
      function updateType() {
        const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
        if (selectedRoom) {
          typeSelect.value = selectedRoom.getAttribute('data-type');
        }
      }
      
      // Инициализация при загрузке
      updateRooms();
      
      // События
      typeSelect.addEventListener('change', updateRooms);
      roomSelect.addEventListener('change', updateType);
    }
  });
});
</script>
</html>
