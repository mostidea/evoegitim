<?php include 'inc/db/session.php'; ?>
<?php include 'inc/ust/header.php'; ?>

<!--=====================================-->
<!--=       Hero Banner Area Start      =-->
<!--=====================================-->
<div class="hero-banner hero-style-1">

    <img src="assets/images/banner/mobil.png" class="mobile-only" alt="Girl Image" id="clickableImage">
    <script>
        document.getElementById("clickableImage").addEventListener("click", function() {
            window.location.href = "/evoai";
        });
    </script>
    <style>
        .accordion-button {
            padding: 0.5rem 1rem;
            white-space: normal;
            height: auto;
        }

        .mobile-only {
            display: none;
            /* Varsayılan olarak bu resmi sakla */
        }

        @media (max-width: 768px) {
            .mobile-only {
                display: block;
                /* Eğer ekran genişliği 768px veya daha az ise göster */
            }
        }
    </style>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">

            </div>
            <div class="col-lg-6">
                <div class="banner-thumbnail">
                    <div class="thumbnail" data-sal-delay="500" data-sal="slide-left" data-sal-duration="1000">
                        <a href="https://evoegitim.com/student/register.php">
                            <img src="assets/images/banner/test-1.png" alt="Girl Image">
                        </a>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-1" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <img data-depth="1.5" src="assets/images/about/shape-15.png" alt="Shape">
                        </li>
                        <li class="shape-2 scene" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <img data-depth="-1.5" src="assets/images/about/shape-16.png" alt="Shape">
                        </li>
                        <li class="shape-3 scene" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <span data-depth="3" class="circle-shape"></span>
                        </li>
                        <li class="shape-4" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <img data-depth="-1" src="assets/images/counterup/shape-02.png" alt="Shape">
                        </li>
                        <li class="shape-5 scene" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <img data-depth="1.5" src="assets/images/about/shape-13.png" alt="Shape">
                        </li>
                        <li class="shape-6 scene" data-sal-delay="1000" data-sal="fade" data-sal-duration="1000">
                            <img data-depth="-2" src="assets/images/about/shape-18.png" alt="Shape">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="shape-7">
        <img src="assets/images/about/h-1-shape-01.png" alt="Shape">
    </div>
</div>
<!--=====================================-->
<!--=       Features Area Start      =-->
<!--=====================================-->
<!-- Start Categories Area  -->
<div class="features-area-2">
    <div class="container">
        <div class="features-grid-wrap">
            <div class="features-box features-style-2 edublink-svg-animate">
                <div class="icon">
                    <img class="svgInject" src="assets/images/animated-svg-icons/online-class.svg" alt="animated icon">
                </div>
                <div class="content">
                    <h5 class="title"><span>3020</span>Tamamlanan Ders</h5>
                </div>
            </div>
            <div class="features-box features-style-2 edublink-svg-animate">
                <div class="icon">
                    <img class="svgInject" src="assets/images/animated-svg-icons/instructor.svg" alt="animated icon">
                </div>
                <div class="content">
                    <h5 class="title"><span>En İyi</span>10 Platform</h5>
                </div>
            </div>
            <div class="features-box features-style-2 edublink-svg-animate">
                <div class="icon certificate">
                    <img class="svgInject" src="assets/images/animated-svg-icons/certificate.svg" alt="animated icon">
                </div>
                <div class="content">
                    <h5 class="title"><span>MEB</span>Onaylı</h5>
                </div>
            </div>
            <div class="features-box features-style-2 edublink-svg-animate">
                <div class="icon">
                    <img class="svgInject" src="assets/images/animated-svg-icons/user.svg" alt="animated icon">
                </div>
                <div class="content">
                    <h5 class="title"><span>2147</span>Öğrenci</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Categories Area  -->
<!--=====================================-->
<!--=       Categories Area Start      =-->
<!--=====================================-->
<!-- Start Categories Area  -->


<div class="features-area-5 gap-bottom-equal">
    <div class="container">
        <div class="section-title section-center sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
            <span class="pre-title"></span>
            <h2 class="title">Neler Yapıyoruz ?</h2>
            <span class="shape-line"><i class="icon-19"></i></span>
        </div>
        <div class="row g-5">
            <div class="col-lg-4 sal-animate" data-sal-delay="50" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-primary-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/abc.svg" alt="images svg" style="width: 79px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">İlkokul</h5>
                        <p>Takviye dersler ve sınav hazırlığı</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sal-animate" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-secondary-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/books.svg" alt="images svg" style="width: 79px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">Ortaokul</h5>
                        <p>LGS hazırlık ve takviye dersler</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-extra06-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/certificate.svg" alt="images svg" style="width: 55px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">Lise</h5>
                        <p>YKS hazırlık ve takviye dersler</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-extra06-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/language-hiragana.svg" alt="images svg" style="width: 79px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">Yabancı Dil</h5>
                        <p>Sınav hazırlığı ve becerileri</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-extra06-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/chart-infographic.svg" alt="images svg" style="width: 79px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">Eğitim Koçluğu</h5>
                        <p>Eğitim ve öğrenci koçluğu</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="features-box color-extra06-style edublink-svg-animate">
                    <div class="icon">
                        <img src="assets/images/svg-icons/3094121.png" alt="images svg" style="width: 79px !important;">
                    </div>
                    <div class="content">
                        <h5 class="title">Beceri</h5>
                        <p>Hızlı okuma, müzik, vb. eğitimler</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="gap-bottom-equal edu-about-area about-style-1">
    <div class="container edublink-animated-shape">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="about-image-gallery">
                    <img class="main-img-1" src="assets/images/about/about-01.webp" alt="About Image">
                    <div class="video-box" data-sal-delay="150" data-sal="slide-down" data-sal-duration="800">
                        <div class="inner">
                            <div class="thumb">
                                <img src="assets/images/about/about-02.webp" alt="About Image">
                                <a href="https://www.youtube.com/watch?v=PICj5tr9hcc" class="popup-icon video-popup-activation">
                                    <i class="icon-18"></i>
                                </a>
                            </div>
                            <div class="loading-bar">
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                    <div class="award-status bounce-slide">
                        <div class="inner">
                            <div class="icon">
                                <i class="icon-21"></i>
                            </div>
                            <div class="content">
                                <h6 class="title">%96.8</h6>
                                <span class="subtitle">Başarı Oranı</span>
                            </div>
                        </div>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-1 scene" data-sal-delay="500" data-sal="fade" data-sal-duration="200">
                            <img data-depth="1" src="assets/images/about/shape-36.png" alt="Shape">
                        </li>
                        <li class="shape-2 scene" data-sal-delay="500" data-sal="fade" data-sal-duration="200">
                            <img data-depth="-1" src="assets/images/about/shape-37.png" alt="Shape">
                        </li>
                        <li class="shape-3 scene" data-sal-delay="500" data-sal="fade" data-sal-duration="200">
                            <img data-depth="1" src="assets/images/about/shape-02.png" alt="Shape">
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-sal-delay="150" data-sal="slide-left" data-sal-duration="800">
                <div class="about-content">
                    <div class="section-title section-left">
                        <span class="pre-title">Neden Evoeğitim ?</span>
                        <h2 class="title">Kişiye Özel Eğitim Planları ile Birebir <span class="color-secondary">Çevrimiçi Dersler</span></h2>
                        <span class="shape-line"><i class="icon-19"></i></span>
                        <p>Kişiye özel eğitim planlaması, öğrencilerin ihtiyaçlarına göre hazırlanan ders programları sayesinde en etkili öğrenme deneyimini sunar. Birebir çevrimiçi dersler ile öğrenciler, konuları daha iyi anlamaları ve öğrenmeleri için öğretmenleriyle birebir etkileşime geçebilirler. Bu şekilde, öğrencilerin eğitimleri tamamen kendi ihtiyaçlarına göre şekillenirken, öğretmenler de öğrencilerin potansiyellerini en iyi şekilde ortaya çıkarabilmek için çalışırlar.</p>
                    </div>
                    <ul class="features-list">
                        <li>Solo (Bireysel) Dersler</li>
                        <li>Grup Dersler</li>
                        <li>Konu Tarama Denemeleri</li>
                    </ul>
                </div>
            </div>
        </div>
        <ul class="shape-group">
            <li class="shape-1 circle scene" data-sal-delay="500" data-sal="fade" data-sal-duration="200">
                <span data-depth="-2.3"></span>
            </li>
        </ul>
    </div>
</div>

<div class="cta-area-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="home-four-cta edu-cta-box cta-style-3 bg-image bg-image--16">
                    <div class="inner">
                        <div class="content text-end">
                            <span class="subtitle">E-Posta:</span>
                            <h3 class="title"><a href="mailto:destek@evoegitim.com">destek@evoegitim.com</a></h3>
                        </div>
                        <div class="sparator">
                            <span>&</span>
                        </div>
                        <div class="content">
                            <span class="subtitle">Bizimle İletişime Geç:</span>
                            <h3 class="title"><a href="tel:+905051118117">0 505 111 81 17</a></h3>
                        </div>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-01 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="2" src="assets/images/cta/shape-06.png" alt="shape" style="transform: translate3d(11.8px, -21.1px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-02 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="-2" src="assets/images/cta/shape-12.png" alt="shape" style="transform: translate3d(-14.7px, 20.1px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-03 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="-3" src="assets/images/cta/shape-04.png" alt="shape" style="transform: translate3d(-17.8px, 31.6px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="edu-categorie-area categorie-area-2 edu-section-gap">
    <div class="container">
        <div class="section-title section-center" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
            <h2 class="title">En çok tercih edilen dersler ?</h2>
            <span class="shape-line"><i class="icon-19"></i></span>
            <p>30'dan fazla alanda özel ders desteğiyle başarıya giden yolculuğunuzda bizimle ilerleyin!</p>
        </div>

        <div class="row g-5">
            <div class="col-lg-4 col-md-6" data-sal-delay="50" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-primary-style edublink-svg-animate">
                    <div class="icon">
                        <i class="ti ti-math-function"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Matematik</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-secondary-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Almanca</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra01-style">
                    <div class="icon">
                        <i class="ti ti-brand-react"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Fizik</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="50" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-tertiary-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">İngilizce</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra02-style">
                    <div class="icon">
                        <i class="ti ti-flask"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Kimya</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra03-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Fransızca</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="50" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra04-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Latince</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra05-style">
                    <div class="icon">
                        <i class="ti ti-brand-envato"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Biyoloji</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra06-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">İspanyolca</h5>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra05-style">
                    <div class="icon">
                        <i class="icon-16 computer-science"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Bilgisayar</h5>
                        </a>
                    </div>
                </div>
            </div>





            <div class="col-lg-4 col-md-6" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra06-style">
                    <div class="icon">
                        <i class="ti ti-language-hiragana"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">İtalyanca</h5>
                        </a>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 col-md-6" data-sal-delay="50" data-sal="slide-up" data-sal-duration="800">
                <div class="categorie-grid categorie-style-2 color-extra04-style">
                    <div class="icon">
                        <i class="ti ti-building-circus"></i>
                    </div>
                    <div class="content">
                        <a href="https://evoegitim.com/student/register.php">
                            <h5 class="title">Tarih</h5>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
<!-- End Categories Area  -->
<!--=====================================-->
<!--=       About Us Area Start      	=-->
<!--=====================================-->

<!--=====================================-->
<!--=       Course Area Start      		=-->
<!--=====================================-->
<!-- Start Course Area  -->
<div class="modern-schooling-cta-wrapper edu-cta-banner-area-6 bg-image">
    <div class="container">
        <div class="edu-cta-banner">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="thumbnail sal-animate" data-sal-delay="50" data-sal="slide-right" data-sal-duration="800">
                        <img src="assets/images/cta/cta-girl-bg.webp" alt="girl image">
                    </div>
                    <ul class="shape-group">
                        <li class="shape-01 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="2.5" src="assets/images/cta/shape-43.png" alt="shape" style="transform: translate3d(6.8px, -27.9px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-02">
                            <img src="assets/images/cta/shape-42.png" alt="shape">
                        </li>
                        <li class="shape-03 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="-2" src="assets/images/cta/shape-40.png" alt="shape" style="transform: translate3d(-8.1px, 33.1px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-04 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="2" src="assets/images/cta/shape-38.png" alt="shape" style="transform: translate3d(6.3px, -28.7px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="section-title section-left sal-animate" data-sal-delay="150" data-sal="slide-left" data-sal-duration="800">
                        <h2 class="title">Ücretsiz Tanışma <br> Dersi Almak <br> İster misiniz ? </h2>
                        <a href="register" class="edu-btn btn-secondary">HEMEN BAŞLA<i class="icon-4"></i></a>
                    </div>
                </div>
            </div>
            <ul class="shape-group">
                <li class="shape-05 scene" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                    <img data-depth="1.5" src="assets/images/cta/shape-39.png" alt="shape" style="transform: translate3d(6.7px, -27.5px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                </li>
                <li class="shape-06">
                    <img src="assets/images/cta/cta-round.svg" alt="shape">
                </li>
            </ul>
        </div>
    </div>
</div>
<p><span>&nbsp;</span></p>
<p><span>&nbsp;</span></p>
<!-- End Course Area -->
<!--=====================================-->
<!--=       CounterUp Area Start      	=-->
<!--=====================================-->
<p><span>&nbsp;</span></p>
<div class="counterup-area-2">
    <div class="container">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-8">
                <div class="counterup-box-wrap">
                    <div class="counterup-box counterup-box-1">
                        <div class="edu-counterup counterup-style-2">
                            <h2 class="counter-item count-number primary-color">
                                <span class="odometer" data-odometer-final="2.1">.</span><span>K</span>
                            </h2>
                            <h6 class="title">Öğrenci</h6>
                        </div>
                        <div class="edu-counterup counterup-style-2">
                            <h2 class="counter-item count-number secondary-color">
                                <span class="odometer" data-odometer-final="3.2">.</span><span>K</span>
                            </h2>
                            <h6 class="title">Tamamlanan ders</h6>
                        </div>
                    </div>
                    <div class="counterup-box counterup-box-2">
                        <div class="edu-counterup counterup-style-2">
                            <h2 class="counter-item count-number extra05-color">
                                <span class="odometer" data-odometer-final="200">.</span><span>+</span>
                            </h2>
                            <h6 class="title">Eğitmen</h6>
                        </div>
                        <div class="edu-counterup counterup-style-2">
                            <h2 class="counter-item count-number extra02-color">
                                <span class="odometer" data-odometer-final="96.8">.8</span><span>%</span>
                            </h2>
                            <h6 class="title">Başarı Oranı</h6>
                        </div>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-1 scene">
                            <img data-depth="-2" src="assets/images/about/shape-13.png" alt="Shape">
                        </li>
                        <li class="shape-2">
                            <img class="rotateit" src="assets/images/counterup/shape-02.png" alt="Shape">
                        </li>
                        <li class="shape-3 scene">
                            <img data-depth="1.6" src="assets/images/counterup/shape-04.png" alt="Shape">
                        </li>
                        <li class="shape-4 scene">
                            <img data-depth="-1.6" src="assets/images/counterup/shape-05.png" alt="Shape">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!--=====================================-->
<!--=       Testimonial Area Start      =-->
<!--=====================================-->
<!-- Start Testimonial Area  -->
<p><span>&nbsp;</span></p>
<p><span>&nbsp;</span></p>
<p><span>&nbsp;</span></p>
<div class="testimonial-area-5 gap-lg-bottom-equal">
    <div class="container">
        <div class="row g-lg-5">
            <div class="col-lg-5">
                <div class="testimonial-heading-area">
                    <div class="section-title section-left sal-animate" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                        <span class="pre-title">Sizden Gelenler</span>
                        <h2 class="title">Veli & Öğrenci Değerlendirmeleri</h2>
                        <span class="shape-line"><i class="icon-19"></i></span>
                        <p>Yüzlerce öğrencimize başırı yolunca ışık yakmaya devam ediyoruz. Bu yolculuğumuzda değerli öğrencilerimiz ve velilerimizin görüşlerini önemsiyoruz.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="swiper-testimonial-slider-wrapper swiper testimonial-coverflow swiper-coverflow swiper-3d swiper-initialized swiper-horizontal swiper-pointer-events">
                    <div class="swiper-wrapper" id="swiper-wrapper-38d51a810a29d3778" aria-live="off" style="cursor: grab; transition-duration: 0ms; transform: translate3d(-1172.5px, 0px, 0px);">
                        <div class="swiper-slide swiper-slide-duplicate swiper-slide-duplicate-active" data-swiper-slide-index="2" role="group" aria-label="3 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(320px, 0px, -720px) rotateX(0deg) rotateY(0deg) scale(1); z-index: -3;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Çocuğum bu platformda öğrenmeyi seviyor! Hem eğitici hem de eğlenceli içerikler sunuyorlar. Öğretmenler sürekli iletişimde ve gelişimini yakından takip ediyorlar. Harika bir eğitim deneyimi!""
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Ayşe A.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide" data-swiper-slide-index="0" role="group" aria-label="1 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(160px, 0px, -360px) rotateX(0deg) rotateY(0deg) scale(1); z-index: -1;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Online eğitim konusunda EVO dışında başka hiçbir platform bu kadar kapsamlı değil. Çocuğum için en iyi eğitim deneyimini sunuyorlar. Özellikle canlı dersler ve interaktif materyaller benim için büyük bir artı."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Mehmet B.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide swiper-slide-visible swiper-slide-prev" data-swiper-slide-index="1" role="group" aria-label="2 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Bu platform sayesinde çocuğum okulu daha çok sevmeye başladı. Dersler hem ilgi çekici hem de öğretici. Velilere düzenli raporlar göndermeleri harika bir özellik. Teşekkürler EVO EĞİTİM!"

                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Zeynep C.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide swiper-slide-visible swiper-slide-active" data-swiper-slide-index="2" role="group" aria-label="3 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(0px, 0px, 0px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 1;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Eğitimde en iyisi! Çocuğumun gelişimini takip etmek için harika araçlar sunuyorlar. Öğretmenler çok uzman ve yardımsever. Kesinlikle tavsiye ederim." </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Ahmet D.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Online eğitim platformu seçerken çok titiz davrandık ve bu markayı tercih etmekten mutluyuz. Çocuğum EVO EĞİTİM sayesinde hem öğreniyor hem de eğleniyor. Hem akademik hem de sosyal becerilerini geliştiriyor." </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Elif E.</h5>
                                    <span class="subtitle">Öğrenci</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Özellikle pandemi döneminde çocuğumun eğitimine katkı sağladılar. Hem güvenli hem de etkili bir öğrenme ortamı sunuyorlar. Her velinin kullanmasını tavsiye ederim."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Mustafa F.
                                    </h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Online eğitim platformu, çocuğumun öğrenme sürecini evimizin konforunda ve güvende yapmasına olanak tanıdı. Öğretmenlerin ilgisi ve öğretim kalitesi harika! Tebrikler EVO” </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Fatma G.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Bu marka, çocuğumun öğrenme yolculuğunu desteklemek için en iyi kaynaklara sahip. Ders içeriği çeşitliliği ve esnek programları, ailemiz için gerçek bir kurtarıcı oldu."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Emre H.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Online eğitim markası, çocuğumun öğrenme deneyimini kişiselleştirmeme yardımcı oldu. İhtiyacı olan alanlarda derinlemesine çalışma fırsatı buluyor ve öğretmenlerinden bireysel destek alabiliyor."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Nesrin İ.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Evo Eğitimi tercih etmemizin en büyük sebebi , arkasındaki eğitim kadrosunun gücü ve
                                        kurucularının da çok tecrübeli iki öğretmen olması.Çünkü eğitimi verebilecek en iyi kişiler
                                        yine egitimcilerdir."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Osman T.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Evo Eğitimin kıymetli kurucu ve eğitmenleri, çok kısa bir sürede online eğitimden bu kadar
                                        verim alacağımızı asla tahmin edemezdik.Başta biraz önyargılıydık ama sistem o kadar güzel
                                        oluşturulmuş ve öğretmenler o kadar iyi seçilmiş ki daha önce yüzyüze aldigimiz derslerden
                                        bu kadar verim alamamıştık.Binlerce kez teşekkürler."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Sevim H.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Bu platform, çocuğumun öğrenme sürecini ilgi çekici ve etkili bir şekilde sürdürmesine yardımcı oldu. İnteraktif ders materyalleri ve öğrenci topluluğu, onun için büyük bir motivasyon kaynağı."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Can K.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>



                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Platform, çocuğumuzun öğrenme sürecini daha organize ve yönetilebilir hale getiriyor. Ebeveynler için günlük raporlar, ilerlemeyi takip etmemizi kolaylaştırıyor."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Gökhan Ö.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide swiper-slide-visible swiper-slide-next" data-swiper-slide-index="3" role="group" aria-label="4 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-80px, 0px, -180px) rotateX(0deg) rotateY(0deg) scale(1); z-index: 0;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/ogrenci.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>
                                        "Bu online eğitim markası, çocuğumun öğrenme yolculuğunu daha erişilebilir hale getirdi. Özellikle öğretmenlerin kişisel geri bildirimleri ve rehberliği, onun gelişimini hızlandırdı."
                                    </p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Ali N.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide swiper-slide-duplicate" data-swiper-slide-index="0" role="group" aria-label="1 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-160px, 0px, -360px) rotateX(0deg) rotateY(0deg) scale(1); z-index: -1;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/testimonial-01.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>"Online eğitim platformu, çocuğumuzun öğrenme sürecini eğlenceli hâle getiriyor. Sanal sınıf atmosferi gerçek sınıfa çok benziyor ve bu da çocuğumuzun motivasyonunu artırıyor."</p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Yasemin S.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide swiper-slide-duplicate swiper-slide-duplicate-prev" data-swiper-slide-index="1" role="group" aria-label="2 / 4" style="width: 335px; transition-duration: 0ms; transform: translate3d(-240px, 0px, -540px) rotateX(0deg) rotateY(0deg) scale(1); z-index: -2;">
                            <div class="testimonial-grid">
                                <div class="thumbnail">
                                    <img src="assets/images/testimonial/testimonial-02.png" alt="Testimonial">
                                    <span class="qoute-icon"><i class="icon-26"></i></span>

                                </div>
                                <div class="content">
                                    <p>“EVO online eğitim, çocuğumun öğrenme ihtiyaçlarını karşılamak için esnek bir yapı sunuyor. İstediği zaman derslere katılabilir ve öğrenme hızını kendisi belirleyebilir. Bu nedenle tercihimiz oldu, mutluyuz."</p>
                                    <div class="rating-icon">
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                        <i class="icon-23"></i>
                                    </div>
                                    <h5 class="title">Serkan T.</h5>
                                    <span class="subtitle">Veli</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination swiper-pagination-clickable swiper-pagination-bullets swiper-pagination-horizontal"><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 1"></span><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 2"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active" tabindex="0" role="button" aria-label="Go to slide 3" aria-current="true"></span><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 4"></span></div>
                    <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Testimonial Area  -->
<!--=====================================-->
<!--=      Call To Action Area Start   	=-->
<!--=====================================-->
<!-- Start CTA Area  -->


<div class="edu-faq-area faq-style-1">
    <div class="container">
        <div class="row g-5 row--45">
            <div class="col-lg-6">
                <div class="edu-faq-gallery">
                    <div class="row g-5">
                        <div class="col-6 sal-animate" data-sal-delay="50" data-sal="slide-right" data-sal-duration="800">
                            <div class="faq-thumbnail thumbnail-1">
                                <img src="assets/images/faq/faq-01.jpg" alt="Faq Images">
                            </div>
                        </div>
                        <div class="col-6 sal-animate" data-sal-delay="100" data-sal="slide-left" data-sal-duration="800">
                            <div class="faq-thumbnail thumbnail-2">
                                <img src="assets/images/faq/faq-02.jpg" alt="Faq Images">
                            </div>
                        </div>
                        <div class="col-6 sal-animate" data-sal-delay="50" data-sal="slide-right" data-sal-duration="800">
                            <div class="faq-thumbnail thumbnail-3">
                                <img src="assets/images/faq/faq-03.jpg" alt="Faq Images">
                            </div>
                        </div>
                        <div class="col-6 sal-animate" data-sal-delay="100" data-sal="slide-left" data-sal-duration="800">
                            <div class="faq-thumbnail thumbnail-4">
                                <img src="assets/images/faq/faq-04.webp" alt="Faq Images">
                            </div>
                        </div>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-1 scene shape-light sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="2" src="assets/images/faq/shape-02.png" alt="Shape Images" style="transform: translate3d(15.2px, -22px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-1 scene shape-dark sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="1.5" src="assets/images/faq/dark-shape-02.png" alt="Shape Images" style="transform: translate3d(11.4px, -16.5px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-2 scene sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="-2" src="assets/images/faq/shape-03.png" alt="Shape Images" style="transform: translate3d(-11.1px, 37.8px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-3 scene sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="2" src="assets/images/faq/shape-04.png" alt="Shape Images" style="transform: translate3d(14.3px, -34.4px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-4 scene sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="-2" src="assets/images/faq/shape-05.png" alt="Shape Images" style="transform: translate3d(-19px, 38.2px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 sal-animate" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="edu-faq-content">
                    <div class="section-title section-left">
                        <span class="pre-title">Sıkça Sorulan Sorular</span>
                        <h2 class="title">Eğitimde Vizyonumuz <span class="color-secondary"> "Özveri"<br> </span> </h2>
                        <span class="shape-line"><i class="icon-19"></i></span>
                    </div>
                    <div class="faq-accordion" id="faq-accordion">
                        <div class="accordion">
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false">
                                        Evo Eğitim ve Öğretim nedir?
                                    </button>
                                </h5>
                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Evo Online ev ortamında interaktif ve online olarak birebir özel ders ve koçluk hizmeti sağlayan bir eğitim platformudur. Evo online sayesinde öğrenciler yenilikçi sisteme ayak uydurarak içlerindeki potansiyeli dışarı çıkarma fırsatı ediniyor. <br>
                                            <br> Her çocuğa özel hazırlanan eğitim planı dahilinde online özel ders seçeneklerimizden yararlanmanız mümkün. Üstelik çocukların Evo Online da tamamen bilgisayar ortamında eğitim görmelerinin yanında, kendilerini dijital bir sınıfta hissetmeleri de kaçınılmaz olacak. <br>
                                            <br> Evo Online sizleri de çocuğunuzun gelişimi hakkında daima haberdar eden bir platform. Dolayısıyla ders çalışmayı eğlenceli hale getirerek çocuğun gelişmesi yönünde de eğitim stratejileri geliştiriyor. <br>
                                            <br>Evo Eğitim ve Öğretim, öğrencilerin potansiyellerini gerçekleştirmelerine yardımcı olmak ve geleneksel özel derslerin yerini almak için tasarlanmış yenilikçi bir online eğitim platformudur. Evo Eğitim ve Öğretim olarak, öğrencilerin gelecekteki başarılarına katkıda bulunmayı hedefliyoruz. <br>
                                            <br>Haydi Öyleyse! Siz de çocuğunuzun nitelikli ve kaliteli bir eğitim görmesi taraftarıysanız bir an önce bizimle iletişime geçin!
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false">
                                        Neden Evo Eğitim'i Tercih Etmeliyim?
                                    </button>
                                </h5>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Nerede ne zaman eğitim alacağım? Zamanımı planlayabilecek miyim? Gibi endişelere kapılma dönemi son buluyor. Çünkü Evo Online sayesinde zaman ve mekan sıkıntısı olmadan eğitim görme fırsatı ayağınıza geliyor. Çocuğunuzun sadece sizin belirlediğiniz saat ve mekanlarda dilediği gibi eğitimini tamamlayarak gelişme kaydetmesi son derece kolay. Bu sayede yaz tatillerinde öğrenciler yaz tatillerinde bile öğrendiklerinden geri kalmadığı gibi, bir sonraki döneme daha kapsamlı hazırlanma şansı yakalayabiliyor. Bunun yanında çocuğunuzun Evo Online sayesinde tekrar yapabilme imkanı da mevcut. Almış olduğu online dersleri kaydedebilir ve dilediği zaman yeniden izleyebiliyor. Üstelik her öğrenci kendisine uygun öğretmenle ders alabiliyor. </p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false">
                                        Evo Eğitimin Farkı Nedir?
                                    </button>
                                </h5>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Her öğrenci kendisine uygun öğretmenden ders alabiliyor. Ayrıca öğrencilere eğitim öncesi eksikliklerini görebileceği bir seviye tespit sınavı uygulanıyor ve her sınıfa özel konu tarama denemesi belli periyotlar ile uygulanıyor. Böylece eğitimi aldığı hoca da öğrencinin ihtiyacına göre bir program düzenliyor. Eğitim sürecinde ailenin öğretmenin ve eğitim uzmanının olduğu bir WhatsApp grubu kurularak çocuğunuzun gelişimini takip etmeniz sağlanıyor. Her hafta branş öğretmeni tarafından dersler hakkında bilgilendirme yapılıyor. Öğrencilerle haftanın bazı günleri study with me (benimle birlikte çalış) etkinlikleri yapılıp, ayrıca isteyen öğrencilerimize eğitim koçluğu imkanı sunuyoruz.Eğer sizin için çocuğunuzun alacağı eğitim kalitesi birinci sırada geliyorsa, Evo Eğitim tamda size göre. Hemen ücretsiz deneme dersiniz ile yerinizi ayırtın!</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false">
                                        Ne tür eğitim destekleri sağlıyorsunuz?
                                    </button>
                                </h5>
                                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Matematik, Türkçe ve İngilizce başta olmak üzere tüm alanlarda eğitim destekleri sunuyoruz. Ayrıca, üniversite dersleri için sunduğumuz tekliflerimizi de gün geçtikçe genişletmekteyiz. Eğer aradığınız bir ders konusunu bulamazsanız, lütfen bizimle iletişime geçmekten çekinmeyin.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false">
                                        Online özel derslerin sağladığı avantajlar nelerdir?
                                    </button>
                                </h5>
                                <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Online canlı derslerinin birçok avantajı bulunmaktadır. Ulaşıma harcanan zaman ve para ortadan kalkar. Ayrıca, kayıt özelliği sayesinde dersler istenilen zaman tekrar izlenebilir. Öğretmenlerin çeşitliliği ise kısa sürede randevu almayı ve gerektiğinde öğretmen değiştirmeyi mümkün kılar.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false">
                                        Özel ders birimlerinin fiyatları ne kadar?
                                    </button>
                                </h5>
                                <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faq-accordion" style="">
                                    <div class="accordion-body">
                                        <p>Fiyatlarımız, üyelik modeline bağlı olarak değişmektedir ve genellikle 475₺ ile 600₺ arasında yer almaktadır. Amacımız, her bütçeye uygun en mükemmel modeli bulabilmektir!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="shape-group">
                        <li class="shape-1 scene sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <img data-depth="1.5" src="assets/images/about/shape-02.png" alt="Shape Images" style="transform: translate3d(19px, -41.6px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;">
                        </li>
                        <li class="shape-2 scene sal-animate" data-sal-delay="500" data-sal="fade" data-sal-duration="200" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; pointer-events: none;">
                            <span data-depth="-2.2" style="transform: translate3d(-10px, 22px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<p><span>&nbsp;</span></p>
<p><span>&nbsp;</span></p>
<p><span>&nbsp;</span></p>
<div class="video-area-2 bg-image--14 bg-image">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="video-banner-content">
                    <div class="video-btn">
                        <a href="https://www.youtube.com/watch?v=PICj5tr9hcc" class="video-play-btn video-popup-activation">
                            <i class="icon-18"></i>
                        </a>
                    </div>
                    <h2 class="title">Eğitmenlerimizi tanımak ister misiniz ?</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Ad Banner Area  -->
<!--=====================================-->
<!--=      		Brand Area Start   		=-->
<!--=====================================-->
<!-- Start Brand Area  -->
<p><span>&nbsp;</span></p>

<div class="edu-brand-area brand-area-1 gap-top-equal" style="display:none;">
    <div class="container">
        <div class="row">
            <div class="col-lg-5">
                <div class="brand-section-heading">
                    <div class="section-title section-left" data-sal-delay="150" data-sal="slide-up" data-sal-duration="800">
                        <span class="pre-title">Yayınlarımız</span>
                        <h2 class="title">Yardımcı Kaynaklar</h2>
                        <span class="shape-line"><i class="icon-19"></i></span>
                        <p>Derslerimizde, konu tarama sınavlarımızda ve mini testlerimizde kullandığımız yayınlar </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="brand-grid-wrap">
                    <div class="brand-grid">
                        <img src="assets/images/brand/1.jpg" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/2.png" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/3.png" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/4.jpg" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/5.png" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/6.jpg" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/7.webp" alt="Brand Logo">
                    </div>
                    <div class="brand-grid">
                        <img src="assets/images/brand/8.png" alt="Brand Logo">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Brand Area  -->
<!--=====================================-->
<!--=      		Blog Area Start   		=-->
<!--=====================================-->
<!-- Start Blog Area  -->
<div class="edu-blog-area blog-area-1 edu-section-gap">
    <div class="container">
        <div class="section-title section-center" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
            <span class="pre-title">BLOG</span>
            <h2 class="title">Neden Online Eğitim ?</h2>
            <span class="shape-line"><i class="icon-19"></i></span>
        </div>
        <div class="row g-5">
            <!-- Start Blog Grid  -->
            <div class="col-lg-4 col-md-6 col-12" data-sal-delay="100" data-sal="slide-up" data-sal-duration="800">
                <div class="edu-blog blog-style-1">
                    <div class="inner">
                        <div class="thumbnail">
                            <a href="blog-1">
                                <img src="assets/images/blog/blog-01.jpg" alt="Blog Images">
                            </a>
                        </div>
                        <div class="content position-top">
                            <div class="read-more-btn">
                                <a class="btn-icon-round" href="blog-1"><i class="icon-4"></i></a>
                            </div>
                            <div class="category-wrap">
                                <a href="#" class="blog-category">Evoeğitim</a>
                            </div>
                            <h5 class="title"><a href="blog-1">Daha Kolay Öğrenmek için İpuçları</a></h5>
                            <ul class="blog-meta">
                                <li><i class="icon-27"></i>Haziran 18, 2023</li>
                            </ul>
                            <p>Öğrenme süreci herkes için farklıdır ve bazen öğrenmek zorlu bir görev gibi görünebilir. Ancak, etkili öğrenme stratejilerini kullanarak öğrenme deneyimini daha kolay ve verimli hale getirebilirsiniz. Bu blog yazısında, daha kolay öğrenmek için bazı ipuçlarını paylaşacağız.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Blog Grid  -->
            <!-- Start Blog Grid  -->
            <div class="col-lg-4 col-md-6 col-12" data-sal-delay="200" data-sal="slide-up" data-sal-duration="800">
                <div class="edu-blog blog-style-1">
                    <div class="inner">
                        <div class="thumbnail">
                            <a href="blog-2">
                                <img src="assets/images/blog/blog-02.jpg" alt="Blog Images">
                            </a>
                        </div>
                        <div class="content position-top">
                            <div class="read-more-btn">
                                <a class="btn-icon-round" href="blog-2"><i class="icon-4"></i></a>
                            </div>
                            <div class="category-wrap">
                                <a href="#" class="blog-category">Evoeğitim</a>
                            </div>
                            <h5 class="title"><a href="blog-2">Neden Özel Ders Almalısınız?</a></h5>
                            <ul class="blog-meta">
                                <li><i class="icon-27"></i>Haziran 18, 2023</li>
                            </ul>
                            <p>Öğrenme süreci herkes için farklıdır ve bazen bireysel ihtiyaçlara özel çözümler gerektirir. Bu noktada, özel ders alma seçeneği devreye girer. Özel dersler, kişiselleştirilmiş bir öğrenme deneyimi sunar ve öğrencilerin ihtiyaçlarına göre uyarlanır.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Blog Grid  -->
            <!-- Start Blog Grid  -->
            <div class="col-lg-4 col-md-6 col-12" data-sal-delay="300" data-sal="slide-up" data-sal-duration="800">
                <div class="edu-blog blog-style-1">
                    <div class="inner">
                        <div class="thumbnail">
                            <a href="blog-3">
                                <img src="assets/images/blog/blog-03.jpg" alt="Blog Images">
                            </a>
                        </div>
                        <div class="content position-top">
                            <div class="read-more-btn">
                                <a class="btn-icon-round" href="blog-3"><i class="icon-4"></i></a>
                            </div>
                            <div class="category-wrap">
                                <a href="#" class="blog-category">Evoeğitim</a>
                            </div>
                            <h5 class="title"><a href="blog-3">Neden Evo Eğitimi tercih etmeliyim ?</a></h5>
                            <ul class="blog-meta">
                                <li><i class="icon-27"></i>Haziran 18, 2023</li>
                            </ul>
                            <p>Günümüzde, teknolojinin hızla gelişmesiyle birlikte online eğitim platformları, öğrenme deneyimini dönüştürüyor. Evo Eğitim, online eğitimde öncü bir platform olarak karşımıza çıkıyor. Bu blog yazısında, Evo Eğitim'in neden tercih edilmesi gerektiğini ele alacağız.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Blog Grid  -->
        </div>
    </div>
    <ul class="shape-group">
        <li class="shape-1 scene">
            <img data-depth="-1.4" src="assets/images/about/shape-02.png" alt="Shape">
        </li>
        <li class="shape-2 scene">
            <span data-depth="2.5"></span>
        </li>
        <li class="shape-3 scene">
            <img data-depth="-2.3" src="assets/images/counterup/shape-05.png" alt="Shape">
        </li>
    </ul>
</div>

<?php include 'inc/ust/footer.php'; ?>