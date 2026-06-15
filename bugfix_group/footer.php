<?php
$_fpDir  = dirname($_SERVER['SCRIPT_NAME']);
$_fpBase = basename($_SERVER['SCRIPT_NAME']);
$_fpHome = ($_fpBase === 'index.php' && ($_fpDir === '/' || $_fpDir === ''))
        || ($_SERVER['SCRIPT_NAME'] === '/');
?>
    <style>
      .footer-layout1 .widget_title {
        color: #ffffff !important;
        padding-bottom: 10px;
        border-bottom: 2px solid #ffcc33;
        display: inline-block;
      }
    </style>
    <footer class="footer-wrapper footer-layout1 style4">
      <div class="widget-area" data-bg-src="assets/img/bg/footer-bg1.png">
        <div class="container">
          <div class="row justify-content-between">
            <div class="col-md-6 col-xl-auto">
              <div class="widget footer-widget">
                <div class="vs-widget-about">
                  <div class="footer-logo">
                    <a href="index.php"><img src="assets/img/logo.png" class="img-white" alt="logo" /></a>
                  </div>
                  <p class="footer-text">
                  Eğitimde vizyonumuz "ÖZVERİ"
                  </p>
                  <p class="footer-info"><i class="fal fa-phone-alt"></i><a class="text-inherit" href="tel:+908502427768">+90 (850) 242 77 68</a></p>
                  <p class="footer-info"><i class="fab fa-whatsapp"></i><a class="text-inherit" href="https://wa.me/905051118117" target="_blank" rel="noopener">WhatsApp ile iletişime geçin</a></p>
                  <p class="footer-info">
                    <i class="fal fa-envelope"></i><a class="text-inherit" href="mailto:info@example.com">destek@evoegitim.com</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-6 col-xl-auto">
              <div class="widget nav_menu footer-widget">
                <h3 class="widget_title">Evo Eğitim</h3>
                <div class="menu-all-pages-container footer-menu">
                  <ul class="menu">
                      <li><a href="bloglar">Blog</a></li>
                    <li><a href="iletisim">İletişim</a></li>
                        <li><a href="hakkimizda">Hakkımızda</a></li>
                                                <li><a href="neden-evo">Neden Evo Eğitim</a></li>
                        <li><a href="duyurular">Duyurular</a></li>
                        <li><a href="sikca-sorulan-sorular">Sıkça Sorulan Sorular</a></li>
                        <li><a href="evo-arge">Evo Ar-Ge</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-6 col-xl-auto">
              <div class="widget nav_menu footer-widget">
                <h3 class="widget_title">Evo Kayıt</h3>
                <div class="menu-all-pages-container footer-menu">
                  <ul class="menu">
                    <li><a href="application">Öğretmen Kayıt</a></li>
                    <li><a href="student/register.php">Öğrenci Kayıt</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-6 col-xl-auto">
              <div class="widget nav_menu footer-widget">
                <h3 class="widget_title">Kurumsal</h3>
                <div class="menu-all-pages-container footer-menu">
                  <ul class="menu">
                    <li><a href="assets/kvkk-sozlesmesi.pdf">KVKK Sözleşmesi</a></li>
                    <li><a href="assets/mesafeli-satis-sozlesmesi.pdf">Mesafeli Satış Sözleşmesi</a></li>
                    <li><a href="assets/on-bilgilendirme-formu.pdf">Ön Bilgilendirme Formu</a></li>
                    <li><a href="assets/sorumluluk-metni.pdf">Sorumluluk Metni</a></li>

                  </ul>
                </div>
              </div>
            </div>
         
          </div>
        </div>
      </div>
      <div class="copyright-wrap">
        <div class="container">
          <div class="row justify-content-between align-items-center">
            <div class="text-center col-lg-auto">
              <p class="copyright-text">
                <span class="js-get-year"></span> <i class="fal fa-copyright"></i> Tüm Hakları
                <a href="index.php">Evo Eğitim</a> Tarafından Saklıdır
                &nbsp;|&nbsp; Creative Design:
                <?php if ($_fpHome): ?>
                  <a href="https://mostdijital.com/" target="_blank" rel="noopener" style="color:inherit;">Most Dijital</a>
                <?php else: ?>Most Dijital<?php endif; ?>
                &nbsp;|&nbsp; Web Software:
                <?php if ($_fpHome): ?>
                  <a href="https://mostidea.com.tr/" target="_blank" rel="noopener" style="color:inherit;">Most Idea</a>
                <?php else: ?>Most Idea<?php endif; ?>
              </p>
            </div>
    
          </div>
        </div>
      </div>
    </footer>
    <!-- Scroll To Top -->
    <a href="#" class="scrollToTop scroll-btn"><i class="far fa-arrow-up"></i></a>
    <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/686e7bbb311a0d191792c695/1ivnp6led';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
