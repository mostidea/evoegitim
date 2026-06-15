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

  <a href="management/dashboard.php"
    class="sidebar__logo text-center p-20 position-sticky inset-block-start-0 bg-white w-100 z-1 pb-10">
    <img src="assets/images/logo/logo.png" style="width: 140px;" alt="Logo" />
  </a>

  <div class="sidebar-menu-wrapper overflow-y-auto scroll-sm mt-24">
    <div class="p-20 pt-10">
      <ul class="sidebar-menu">


        <?php if ($_SESSION["user_id"] == 2) {  ?>
          <li class="sidebar-menu__item">
            <a href="management/finance.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-squares-four"></i></span>
              <span class="text">Ana Sayfa</span>
            </a>

            <!-- Submenu End -->
          </li>
        <?php } else {  ?>

          <li class="sidebar-menu__item">
            <a href="management/dashboard.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-squares-four"></i></span>
              <span class="text">Ana Sayfa</span>
            </a>

            <!-- Submenu End -->
          </li>
          <li class="sidebar-menu__item has-dropdown">
            <a href="javascript:void(0)" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Evo Ders</span>
            </a>
            <!-- Submenu start -->
            <ul class="sidebar-submenu">
              <li class="sidebar-submenu__item">
                <a href="management/appointment.php?status=0" class="sidebar-submenu__link"> Havuz Dersleri </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/appointment.php?status=1" class="sidebar-submenu__link"> Tamamlanan Dersler </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/appointment.php?status=2" class="sidebar-submenu__link"> Tamamlanamayan Dersler </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/lessons.php" class="sidebar-submenu__link"> Branş Ders Ekle</a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/lesson-request.php" class="sidebar-submenu__link"> Ders Talepleri</a>
              </li>

            </ul>
            <!-- Submenu End -->
          </li>


          <li class="sidebar-menu__item">
            <a href="management/available-calendar.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Ders Takvimi</span>
            </a>

            <!-- Submenu End -->
          </li>


          <li class="sidebar-menu__item has-dropdown">
            <a href="javascript:void(0)" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Üye Yönetimi</span>
            </a>
            <!-- Submenu start -->
            <ul class="sidebar-submenu">
              <li class="sidebar-submenu__item">
                <a href="management/all-students.php" class="sidebar-submenu__link"> Tüm Öğrenciler </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/all-parents.php" class="sidebar-submenu__link"> Tüm Veliler </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/all-teachers.php" class="sidebar-submenu__link"> Tüm Öğretmenler </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/new-teachers.php" class="sidebar-submenu__link"> Öğretmen Ekle </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/edit-user.php?id=1" class="sidebar-submenu__link"> Admin Kullanıcısını Düzenle</a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/edit-user.php?id=2" class="sidebar-submenu__link">Finans Kullanıcısını Düzenle</a>
              </li>
                   <li class="sidebar-submenu__item">
                <a href="management/edit-user.php?id=3" class="sidebar-submenu__link">Blog Yazarı Kullanıcısını Düzenle</a>
              </li>
            </ul>
            <!-- Submenu End -->
          </li>


          <li class="sidebar-menu__item has-dropdown">
            <a href="javascript:void(0)" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Üye Değerlendirmeleri</span>
            </a>
            <!-- Submenu start -->
            <ul class="sidebar-submenu">
              <li class="sidebar-submenu__item">
                <a href="management/reviews-sended.php" class="sidebar-submenu__link"> Öğretmenlerin Değerlendirmeleri </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/reviews-received.php" class="sidebar-submenu__link"> Öğrencilerin Değerlendirmeleri </a>
              </li>

            </ul>
            <!-- Submenu End -->
          </li>


          <li class="sidebar-menu__item has-dropdown">
            <a href="javascript:void(0)" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Popup Modülü</span>
            </a>
            <!-- Submenu start -->
            <ul class="sidebar-submenu">
              <li class="sidebar-submenu__item">
                <a href="management/general-popup.php" class="sidebar-submenu__link"> Genel Popup </a>
              </li>
              <li class="sidebar-submenu__item">
                <a href="management/evo-ai-popup.php" class="sidebar-submenu__link"> Evo AI Popup </a>
              </li>

            </ul>
            <!-- Submenu End -->
          </li>

          <li class="sidebar-menu__item">
            <a href="management/groups.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-graduation-cap"></i></span>
              <span class="text">Grup Dersler</span>
            </a>

            <!-- Submenu End -->
          </li>
          <li class="sidebar-menu__item">
            <a href="management/group-request.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-chats-teardrop"></i></span>
              <?php 
$groupReqc = $db->prepare("
SELECT * FROM group_request 
WHERE status = 0");
$groupReqc->execute();
$groupc = $groupReqc->rowCount();
?>
              <span class="text position-relative">G.D Talepleri <br> Öğretmen
              <?php if($groupc > 0){ ?>  
              <span class="bg-evo evo-badge"><?php echo $groupc; ?></span></span>
              <?php } ?>
            </a>
          </li>
          <li class="sidebar-menu__item">
            <a href="management/student-group-requests.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-chats-teardrop"></i></span>
              <?php 
$groupReqcS = $db->prepare("
SELECT * FROM student_group_request 
WHERE status = 0");
$groupReqcS->execute();
$groupcS = $groupReqcS->rowCount();
?>
              <span class="text position-relative">G.D Talepleri <br> Öğrenci
              <?php if($groupcS > 0){ ?>  
              <span class="bg-evo evo-badge"><?php echo $groupcS; ?></span></span>
              <?php } ?>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/chat.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-chats-teardrop"></i></span>
             <?php 
        // Solo mesajların okunmamış sayısı (öğretmenin aldığı mesajlar)
        $soloChatQuery = $db->prepare("
            SELECT COUNT(*) as solo_count 
            FROM chat_messages 
            WHERE receiver_id = :user_id AND received = 0
        ");
        $soloChatQuery->execute(['user_id' => $_SESSION["user_id"]]);
        $soloUnreadCount = $soloChatQuery->fetch(PDO::FETCH_ASSOC)['solo_count'];

        // Grup mesajlarının okunmamış sayısı
        $groupUnreadCount = 0;
            $groupChatQuery = $db->prepare("
                SELECT COUNT(*) as group_count 
                FROM chat_group_messages 
                WHERE  received = 0 
                AND (sender_user_id != :user_id OR sender_user_id IS NULL)
            ");
            $groupChatQuery->execute(['user_id' => $_SESSION["user_id"]]);
            $groupUnreadCount = $groupChatQuery->fetch(PDO::FETCH_ASSOC)['group_count'];
        

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
            <a href="management/finance.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Finans Modülü</span>
            </a>
          </li>

              <li class="sidebar-menu__item">
            <a href="management/transaction.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Tüm İşlemler</span>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/blog.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Blog Modülü</span>
            </a>
          </li>


          <li class="sidebar-menu__item">
            <a href="management/notice.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Duyuru Modülü</span>
            </a>
          </li>
    
          <li class="sidebar-menu__item">
            <a href="management/basvurular.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Başvuru Modülü</span>
            </a>
          </li>

          
          <li class="sidebar-menu__item">
            <a href="management/iletisim" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">İletişim Modülü</span>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/tickets.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-gear"></i></span>
              <span class="text">Müşteri Hizmetleri Yönetimi         
              <?php 
$ticketCountq = $db->prepare("
SELECT *
FROM tickets t
JOIN tickets_reply tr ON t.id = tr.ticket_id
WHERE tr.sender <> 1 
AND t.status = 0;");
$ticketCountq->execute();
$unreadTicketCount = $ticketCountq->rowCount();

$ticketCountNew = $db->prepare("
SELECT *
FROM tickets WHERE status=0");
$ticketCountNew->execute();
$unreadTicketCountNew = $ticketCountNew->rowCount();
?>
                <?php if($unreadTicketCount > 0 || $unreadTicketCountNew > 0){ ?>  
              <span class="bg-evo evo-badge"><?php echo $unreadTicketCount+$unreadTicketCountNew; ?></span>
              <?php } ?></span>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/package.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Paket Modülü</span>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/virtual-backgrounds.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-image-square"></i></span>
              <span class="text">Sanal Arka Plan</span>
            </a>
          </li>

          <li class="sidebar-menu__item">
            <a href="management/affilate.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Affilate Modülü</span>
            </a>
          </li>


          <li class="sidebar-menu__item">
            <a href="management/notification.php" class="sidebar-menu__link">
              <span class="icon"><i class="ph ph-users-three"></i></span>
              <span class="text">Bildirim Modülü</span>
            </a>
          </li>



        <?php }  ?>
      </ul>
    </div>
  </div>
</aside>