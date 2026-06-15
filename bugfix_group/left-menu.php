<?php 

if($_SESSION["status"]==0){
  header("Location: /student/code.php");
  exit;
}

?>
<div class="preloader">
  <div class="loader"></div>
</div>
<!--==================== Preloader End ====================-->

<!--==================== Sidebar Overlay End ====================-->
<div class="side-overlay"></div>
<!--==================== Sidebar Overlay End ====================-->

<!-- ============================ Sidebar Start ============================ -->

<aside class="sidebar">
  <!-- sidebar close btn -->
  <button type="button"
    class="sidebar-close-btn text-gray-500 hover-text-white hover-bg-main-600 text-md w-24 h-24 border border-gray-100 hover-border-main-600 d-xl-none d-flex flex-center rounded-circle position-absolute">
    <i class="ph ph-x"></i>
  </button>
  <!-- sidebar close btn -->

  <a href="student/dashboard.php"
    class="sidebar__logo text-center p-20 position-sticky inset-block-start-0 bg-white w-100 z-1 pb-10">
    <img src="assets/images/logo/logo.png" style="width: 140px;" alt="Logo" />
  </a>

  <div class="sidebar-menu-wrapper overflow-y-auto scroll-sm mt-24">
    <div class="p-20 pt-10">
      <ul class="sidebar-menu">
        <li class="sidebar-menu__item">
          <a href="student/dashboard.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-squares-four"></i></span>
            <span class="text">Ana Sayfa</span>
          </a>

          <!-- Submenu End -->
        </li>

        <li class="sidebar-menu__item">
          <a href="student/appointment.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Tüm Derslerim</span>
          </a>
       
          <!-- Submenu End -->
        </li>
        
        <li class="sidebar-menu__item">
          <a href="student/group-classes.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Grup Dersler</span>
          </a>
       
          <!-- Submenu End -->
        </li>
        <li class="sidebar-menu__item">
          <a href="student/lesson-calendar.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Ders Takvimim</span>
          </a>
       
          <!-- Submenu End -->
        </li>
        <li class="sidebar-menu__item">
          <a href="student/lesson-request.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Solo Ders Taleplerim</span>
          </a>
       
          <!-- Submenu End -->
        </li>

        <li class="sidebar-menu__item">
          <a href="student/lesson-create.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Solo Ders Talep Et</span>
          </a>
       
          <!-- Submenu End -->
        </li>
        <li class="sidebar-menu__item">
          <a href="student/group-lesson-requests.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-graduation-cap"></i></span>
            <span class="text">Grup Ders Talep Et</span>
          </a>
       
          <!-- Submenu End -->
        <li class="sidebar-menu__item">
    <a href="student/tickets.php" class="sidebar-menu__link">
        <span class="icon"><i class="ph ph-gear"></i></span>
        <span class="text">Destek Merkezi         
        <?php 
        
        $ticketCountq = $db->prepare("
            SELECT COUNT(DISTINCT t.id) as unread_count
            FROM tickets t
            JOIN tickets_reply tr ON t.id = tr.ticket_id
            WHERE t.user_id = :user_id
            AND tr.sender <> :sender
            AND t.status = 1
        ");
        $ticketCountq->execute([':user_id' => $_SESSION["user_id"], ':sender' => $_SESSION["user_id"]]);
        $ticketData = $ticketCountq->fetch(PDO::FETCH_ASSOC);
        $unreadTicketCount = $ticketData['unread_count'];
        ?>
        <?php if($unreadTicketCount > 0){ ?>  
            <span class="bg-evo evo-badge"><?php echo $unreadTicketCount; ?></span>
        <?php } ?>
        </span>
    </a>
</li>
    
        <li class="sidebar-menu__item">
          <a href="student/teachers.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-users-three"></i></span>
            <span class="text">Öğretmenlerim</span>
          </a>
        </li>
        <li class="sidebar-menu__item">
          <a href="student/reviews-sended.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-clipboard-text"></i></span>
            <span class="text">Eğitmen Değerlendirmelerim</span>
          </a>
        </li>
        <li class="sidebar-menu__item">
          <a href="student/reviews-received.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-users"></i></span>
            <span class="text">Eğitmenin Değerlendirmeleri</span>
          </a>
        </li>
     <li class="sidebar-menu__item">
    <a href="student/chat.php" class="sidebar-menu__link">
        <span class="icon"><i class="ph ph-chats-teardrop"></i></span>
        <?php 
        // Solo mesajların okunmamış sayısı (öğrencinin aldığı mesajlar)
        $soloChatQuery = $db->prepare("
            SELECT COUNT(*) as solo_count 
            FROM chat_messages 
            WHERE receiver_id = :user_id AND received = 0
        ");
        $soloChatQuery->execute(['user_id' => $_SESSION["user_id"]]);
        $soloUnreadCount = $soloChatQuery->fetch(PDO::FETCH_ASSOC)['solo_count'];

        // Öğrencinin üye olduğu grupları bul (user_id kontrolü)
        $studentGroupsQuery = $db->prepare("
            SELECT DISTINCT group_id 
            FROM chat_group_members 
            WHERE user_id = :user_id
        ");
        $studentGroupsQuery->execute(['user_id' => $_SESSION["user_id"]]);
        $studentGroups = $studentGroupsQuery->fetchAll(PDO::FETCH_COLUMN);

        // Grup mesajlarının okunmamış sayısı
        $groupUnreadCount = 0;
        if (!empty($studentGroups)) {
            $groupIds = implode(',', $studentGroups);
            $groupChatQuery = $db->prepare("
                SELECT COUNT(*) as group_count 
                FROM chat_group_messages 
                WHERE group_id IN ($groupIds) 
                AND received = 0 
                AND (sender_user_id != :user_id OR sender_user_id IS NULL)
            ");
            $groupChatQuery->execute(['user_id' => $_SESSION["user_id"]]);
            $groupUnreadCount = $groupChatQuery->fetch(PDO::FETCH_ASSOC)['group_count'];
        }

        // Toplam okunmamış mesaj sayısı
        $totalUnreadCount = $soloUnreadCount + $groupUnreadCount;
        ?>
        
        <span class="text position-relative">Evo Chat 
            <?php if($totalUnreadCount > 0){ ?>  
                <span class="bg-evo evo-badge"><?php echo $totalUnreadCount; ?></span>
            <?php } ?>
        </span>
    </a>
</li>

        <li class="sidebar-menu__item">
          <a href="student/package.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-coins"></i></span>
            <span class="text">Solo Ders Paketleri</span>
          </a>
        </li>
        <li class="sidebar-menu__item">
          <a href="student/group-package.php" class="sidebar-menu__link">
            <span class="icon"><i class="ph ph-coins"></i></span>
            <span class="text">Grup Ders Paketleri</span>
          </a>
        </li>


      </ul>
    </div>
  </div>
</aside>