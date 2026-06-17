<?php
if(isset($_SESSION["status"]) && $_SESSION["status"]==0){
  header("Location: /student/code.php");
  exit;
}

$_uri = $_SERVER['REQUEST_URI'] ?? '';
function _nav_active($uri, ...$segments) {
  foreach ($segments as $s) {
    if (strpos($uri, $s) !== false) return ' nav-active';
  }
  return '';
}

// Okunmamış bilet sayısı
$_ticketQ = $db->prepare("
  SELECT COUNT(DISTINCT t.id) as c FROM tickets t
  JOIN tickets_reply tr ON t.id = tr.ticket_id
  WHERE t.user_id = :uid AND tr.sender <> :uid2 AND t.status = 1
");
$_ticketQ->execute([':uid' => $_SESSION["user_id"], ':uid2' => $_SESSION["user_id"]]);
$_unreadTickets = (int)$_ticketQ->fetchColumn();

// Okunmamış mesaj sayısı
$_soloQ = $db->prepare("SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND received = 0");
$_soloQ->execute([$_SESSION["user_id"]]);
$_soloUnread = (int)$_soloQ->fetchColumn();

$_groupUnread = 0;
$_groupsQ = $db->prepare("SELECT DISTINCT group_id FROM chat_group_members WHERE user_id = ?");
$_groupsQ->execute([$_SESSION["user_id"]]);
$_myGroups = $_groupsQ->fetchAll(PDO::FETCH_COLUMN);
if (!empty($_myGroups)) {
  $_safeIds = array_map('intval', $_myGroups);
  $_ph = implode(',', array_fill(0, count($_safeIds), '?'));
  $_gChatQ = $db->prepare("SELECT COUNT(*) FROM chat_group_messages WHERE group_id IN ($_ph) AND received = 0 AND (sender_user_id != ? OR sender_user_id IS NULL)");
  $_gChatQ->execute(array_merge($_safeIds, [(int)$_SESSION["user_id"]]));
  $_groupUnread = (int)$_gChatQ->fetchColumn();
}
$_totalChat = $_soloUnread + $_groupUnread;
?>
<style>
/* ── Overlay ──────────────────────────────────── */
.evo-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:998;backdrop-filter:blur(2px);}
.evo-overlay.open{display:block;}

/* ── Sidebar ──────────────────────────────────── */
.evo-sidebar{
  position:fixed;top:0;left:0;bottom:0;
  width:260px;
  background:#fff;
  border-right:1px solid #f1f0ff;
  display:flex;flex-direction:column;
  z-index:999;
  transition:transform .28s cubic-bezier(.4,0,.2,1);
  box-shadow:4px 0 24px rgba(124,58,237,.06);
}
.evo-sidebar.collapsed{transform:translateX(-100%);}

/* ── Logo ──────────────────────────────────────── */
.evo-sidebar__brand{
  display:flex;align-items:center;gap:.75rem;
  padding:1.25rem 1.4rem 1rem;
  border-bottom:1px solid #f5f3ff;
  flex-shrink:0;
}
.evo-sidebar__brand img{width:110px;height:auto;}
.evo-sidebar__close{
  margin-left:auto;display:none;
  width:32px;height:32px;border:none;background:#f5f3ff;
  border-radius:8px;cursor:pointer;color:#7c3aed;font-size:1rem;
  align-items:center;justify-content:center;
  transition:background .15s;
}
.evo-sidebar__close:hover{background:#ede9fe;}
@media(max-width:1199px){.evo-sidebar__close{display:flex;}}

/* ── User pill ─────────────────────────────────── */
.evo-sidebar__user{
  margin:1rem 1rem .5rem;
  padding:.7rem .9rem;
  background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);
  border-radius:12px;
  display:flex;align-items:center;gap:.65rem;
  flex-shrink:0;
}
.evo-sidebar__avatar{
  width:36px;height:36px;border-radius:10px;
  background:linear-gradient(135deg,#7c3aed,#4f46e5);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.85rem;font-weight:700;flex-shrink:0;
}
.evo-sidebar__uname{font-size:.78rem;font-weight:700;color:#3b0764;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.evo-sidebar__urole{font-size:.68rem;color:#7c3aed;font-weight:600;margin-top:1px;}

/* ── Nav scroll area ───────────────────────────── */
.evo-sidebar__nav{flex:1;overflow-y:auto;padding:.5rem 0 1rem;}
.evo-sidebar__nav::-webkit-scrollbar{width:3px;}
.evo-sidebar__nav::-webkit-scrollbar-thumb{background:#ede9fe;border-radius:4px;}

/* ── Section label ─────────────────────────────── */
.nav-section{
  padding:.9rem 1.4rem .3rem;
  font-size:.63rem;font-weight:800;letter-spacing:.08em;
  text-transform:uppercase;color:#a78bfa;
}

/* ── Nav link ──────────────────────────────────── */
.nav-item{list-style:none;}
.nav-link{
  display:flex;align-items:center;gap:.7rem;
  padding:.6rem 1.1rem .6rem 1.3rem;
  margin:1px .75rem;
  border-radius:10px;
  color:#64748b;
  font-size:.825rem;font-weight:600;
  text-decoration:none;
  position:relative;
  transition:background .15s,color .15s;
}
.nav-link:hover{background:#f5f3ff;color:#7c3aed;text-decoration:none;}
.nav-link .nav-icon{
  width:32px;height:32px;flex-shrink:0;border-radius:8px;
  background:#f8fafc;
  display:flex;align-items:center;justify-content:center;
  font-size:1rem;color:#94a3b8;
  transition:background .15s,color .15s;
}
.nav-link:hover .nav-icon{background:#ede9fe;color:#7c3aed;}
.nav-link.nav-active{background:#f5f3ff;color:#7c3aed;}
.nav-link.nav-active .nav-icon{background:#ede9fe;color:#7c3aed;}
.nav-link.nav-active::before{
  content:'';position:absolute;left:-12px;top:50%;transform:translateY(-50%);
  width:3px;height:60%;background:#7c3aed;border-radius:0 3px 3px 0;
}

/* ── Badge ─────────────────────────────────────── */
.nav-badge{
  margin-left:auto;
  min-width:20px;height:20px;padding:0 5px;
  background:#7c3aed;color:#fff;
  font-size:.6rem;font-weight:800;
  border-radius:20px;
  display:flex;align-items:center;justify-content:center;
  line-height:1;
}

/* ── Divider ───────────────────────────────────── */
.nav-divider{height:1px;background:#f5f3ff;margin:.5rem 1.2rem;}

/* ── Bottom bar ────────────────────────────────── */
.evo-sidebar__footer{
  padding:.75rem 1rem;
  border-top:1px solid #f5f3ff;
  flex-shrink:0;
}
.evo-sidebar__profile-btn{
  display:flex;align-items:center;gap:.6rem;
  padding:.55rem .8rem;
  border-radius:10px;
  color:#64748b;font-size:.8rem;font-weight:600;
  text-decoration:none;
  transition:background .15s,color .15s;
}
.evo-sidebar__profile-btn:hover{background:#f5f3ff;color:#7c3aed;text-decoration:none;}
.evo-sidebar__profile-btn i{font-size:1.1rem;}
</style>

<div class="preloader">
  <div class="loader"></div>
</div>
<div class="evo-overlay" id="evo-overlay"></div>

<aside class="evo-sidebar" id="evo-sidebar">

  <!-- Brand -->
  <div class="evo-sidebar__brand">
    <a href="student/dashboard.php">
      <img src="assets/images/logo/logo.png" alt="Evo Eğitim" />
    </a>
    <button class="evo-sidebar__close" id="evo-sidebar-close" aria-label="Menüyü kapat">
      <i class="ph ph-x"></i>
    </button>
  </div>

  <!-- User -->
  <div class="evo-sidebar__user">
    <div class="evo-sidebar__avatar">
      <?php echo mb_strtoupper(mb_substr($_SESSION["fullname"] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
    </div>
    <div style="min-width:0;">
      <div class="evo-sidebar__uname"><?php echo htmlspecialchars($_SESSION["fullname"] ?? ''); ?></div>
      <div class="evo-sidebar__urole">Öğrenci</div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="evo-sidebar__nav">
    <ul style="list-style:none;margin:0;padding:0;">

      <!-- Genel -->
      <li class="nav-section">Genel</li>
      <li class="nav-item">
        <a href="student/dashboard.php" class="nav-link<?php echo _nav_active($_uri,'dashboard'); ?>">
          <span class="nav-icon"><i class="ph ph-squares-four"></i></span>
          <span>Ana Sayfa</span>
        </a>
      </li>

      <!-- Derslerim -->
      <li class="nav-section">Derslerim</li>
      <li class="nav-item">
        <a href="student/appointment.php" class="nav-link<?php echo _nav_active($_uri,'appointment'); ?>">
          <span class="nav-icon"><i class="ph ph-book-open"></i></span>
          <span>Tüm Derslerim</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/group-classes.php" class="nav-link<?php echo _nav_active($_uri,'group-classes'); ?>">
          <span class="nav-icon"><i class="ph ph-users-three"></i></span>
          <span>Grup Dersler</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/lesson-calendar.php" class="nav-link<?php echo _nav_active($_uri,'lesson-calendar'); ?>">
          <span class="nav-icon"><i class="ph ph-calendar-dots"></i></span>
          <span>Ders Takvimim</span>
        </a>
      </li>

      <!-- Talepler -->
      <li class="nav-section">Talepler</li>
      <li class="nav-item">
        <a href="student/lesson-request.php" class="nav-link<?php echo _nav_active($_uri,'lesson-request'); ?>">
          <span class="nav-icon"><i class="ph ph-clock-countdown"></i></span>
          <span>Solo Ders Taleplerim</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/lesson-create.php" class="nav-link<?php echo _nav_active($_uri,'lesson-create'); ?>">
          <span class="nav-icon"><i class="ph ph-plus-circle"></i></span>
          <span>Solo Ders Talep Et</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/group-lesson-requests.php" class="nav-link<?php echo _nav_active($_uri,'group-lesson-requests'); ?>">
          <span class="nav-icon"><i class="ph ph-user-plus"></i></span>
          <span>Grup Ders Talep Et</span>
        </a>
      </li>

      <!-- İletişim -->
      <li class="nav-section">İletişim</li>
      <li class="nav-item">
        <a href="student/chat.php" class="nav-link<?php echo _nav_active($_uri,'/chat'); ?>">
          <span class="nav-icon"><i class="ph ph-chats-teardrop"></i></span>
          <span>Evo Chat</span>
          <?php if($_totalChat > 0): ?>
            <span class="nav-badge"><?php echo $_totalChat; ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/tickets.php" class="nav-link<?php echo _nav_active($_uri,'ticket'); ?>">
          <span class="nav-icon"><i class="ph ph-headset"></i></span>
          <span>Destek Merkezi</span>
          <?php if($_unreadTickets > 0): ?>
            <span class="nav-badge"><?php echo $_unreadTickets; ?></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- Değerlendirme -->
      <li class="nav-section">Değerlendirme</li>
      <li class="nav-item">
        <a href="student/teachers.php" class="nav-link<?php echo _nav_active($_uri,'teachers'); ?>">
          <span class="nav-icon"><i class="ph ph-chalkboard-teacher"></i></span>
          <span>Öğretmenlerim</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/reviews-sended.php" class="nav-link<?php echo _nav_active($_uri,'reviews-sended'); ?>">
          <span class="nav-icon"><i class="ph ph-star"></i></span>
          <span>Eğitmen Değerlendirmelerim</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/reviews-received.php" class="nav-link<?php echo _nav_active($_uri,'reviews-received'); ?>">
          <span class="nav-icon"><i class="ph ph-chat-circle-text"></i></span>
          <span>Eğitmenin Değerlendirmeleri</span>
        </a>
      </li>

      <!-- Paketler -->
      <li class="nav-section">Paketler</li>
      <li class="nav-item">
        <a href="student/package.php" class="nav-link<?php echo _nav_active($_uri,'student/package'); ?>">
          <span class="nav-icon"><i class="ph ph-package"></i></span>
          <span>Solo Ders Paketleri</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="student/group-package.php" class="nav-link<?php echo _nav_active($_uri,'group-package'); ?>">
          <span class="nav-icon"><i class="ph ph-stack"></i></span>
          <span>Grup Ders Paketleri</span>
        </a>
      </li>

    </ul>
  </nav>

  <!-- Footer -->
  <div class="evo-sidebar__footer">
    <a href="student/profile-settings.php" class="evo-sidebar__profile-btn<?php echo _nav_active($_uri,'profile-settings') ? ' nav-active' : ''; ?>">
      <i class="ph ph-gear"></i>
      <span>Hesap Ayarları</span>
    </a>
  </div>

</aside>

<script>
(function(){
  var sb  = document.getElementById('evo-sidebar');
  var ov  = document.getElementById('evo-overlay');
  var cls = document.getElementById('evo-sidebar-close');

  if (!sb || !ov) return;

  function openSidebar(){  sb.classList.remove('collapsed'); ov.classList.add('open'); }
  function closeSidebar(){ sb.classList.add('collapsed');    ov.classList.remove('open'); }

  ov.addEventListener('click', closeSidebar);
  if (cls) cls.addEventListener('click', closeSidebar);

  var hamBtn = document.querySelector('.sidebar-toggle-btn, [data-toggle="sidebar"], .hamburger-btn');
  if (hamBtn) hamBtn.addEventListener('click', function(e){ e.preventDefault(); openSidebar(); });

  if (window.innerWidth < 1200) sb.classList.add('collapsed');
  window.addEventListener('resize', function(){
    if (window.innerWidth >= 1200){ sb.classList.remove('collapsed'); ov.classList.remove('open'); }
    else sb.classList.add('collapsed');
  });
})();
</script>
