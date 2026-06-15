<div class="top-navbar d-flex justify-content-between align-items-center gap-16">   
  <!-- Sol taraf - Toggle Button -->
  <div class="flex-align gap-16">     
    <button type="button" class="toggle-btn d-xl-none d-flex text-26 text-gray-500">
      <i class="ph ph-list"></i>
    </button>    
  </div>    

  <!-- Sağ taraf - Davet Et ve Profil -->
  <div class="d-flex align-items-center gap-16">  
    
    <style>   
      #studentSelector {     
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important; 
      } 
      
      .invite-button-wrapper {
        min-width: 180px;
      }
      
      .navbar-profile-wrapper {
        min-width: 150px;
      }
      
      .top-navbar {
        padding: 0.75rem 1rem;
      }
      
      .top-navbar .form-select {
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
      }
      
      .top-navbar .dropdown button {
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>   
    
    <!-- Arkadaşını Davet Et Button -->
    <div class="invite-button-wrapper">         
      <a href="javascript:;" id="studentSelector" class="form-select text-center" 
         data-code="EVO<?php echo $_SESSION["user_id"]; ?>CDE">
        Arkadaşını Davet Et
      </a>   
    </div>     

    <!-- User Profile Dropdown -->     
    <div class="dropdown navbar-profile-wrapper">       
      <button class="users arrow-down-icon border border-gray-200 rounded-pill d-flex align-items-center justify-content-center position-relative w-100"         
              type="button" data-bs-toggle="dropdown" aria-expanded="false" 
              style="padding: 0.5rem 45px 0.5rem 1rem; min-height: 40px;">         
        <span class="position-relative text-center">           
          Profil Ayarları         
        </span>       
      </button>       
      
      <div class="dropdown-menu dropdown-menu--lg border-0 bg-transparent p-0 dropdown-menu-end">         
        <div class="card border border-gray-100 rounded-12 box-shadow-custom">           
          <div class="card-body">             
            <div class="flex-align gap-8 mb-20 pb-20 border-bottom border-gray-100">               
              <div class="">                 
                <h4 class="mb-0"><?php echo $_SESSION["fullname"]; ?></h4>                 
                <p class="fw-medium text-13 text-gray-200"><?php echo $_SESSION["email"]; ?></p>               
              </div>             
            </div>             
            
            <ul class="max-h-270 overflow-y-auto scroll-sm pe-4">             
              <li class="mb-4">                 
                <a href="https://evoegitim.com" target="_blank"                   
                   class="py-12 text-15 px-20 hover-bg-gray-50 text-gray-300 rounded-8 flex-align gap-8 fw-medium text-15">                   
                  <span class="text-2xl text-primary-600 d-flex"><i class="ph ph-house"></i></span>                   
                  <span class="text">Site Ana Sayfası</span>                 
                </a>               
              </li>               
              
              <li class="mb-4">                 
                <a href="student/profile-settings.php"                   
                   class="py-12 text-15 px-20 hover-bg-gray-50 text-gray-300 rounded-8 flex-align gap-8 fw-medium text-15">                   
                  <span class="text-2xl text-primary-600 d-flex"><i class="ph ph-gear"></i></span>                   
                  <span class="text">Hesap Ayarlarım</span>                 
                </a>               
              </li>               
              
              <li class="mb-4">                 
                <a href="student/package.php"                   
                   class="py-12 text-15 px-20 hover-bg-gray-50 text-gray-300 rounded-8 flex-align gap-8 fw-medium text-15">                   
                  <span class="text-2xl text-primary-600 d-flex"><i class="ph ph-chart-bar"></i></span>                   
                  <span class="text">Solo Ders Paketleri</span>                 
                </a>               
              </li>                
              
                   <li class="mb-4">                 
                <a href="student/group-package.php"                   
                   class="py-12 text-15 px-20 hover-bg-gray-50 text-gray-300 rounded-8 flex-align gap-8 fw-medium text-15">                   
                  <span class="text-2xl text-primary-600 d-flex"><i class="ph ph-chart-bar"></i></span>                   
                  <span class="text">Grup Ders Paketleri</span>                 
                </a>               
              </li>                
              
              <li class="pt-8 border-top border-gray-100">                 
                <a href="student/logout.php"                   
                   class="py-12 text-15 px-20 hover-bg-danger-50 text-gray-300 hover-text-danger-600 rounded-8 flex-align gap-8 fw-medium text-15">                   
                  <span class="text-2xl text-danger-600 d-flex"><i class="ph ph-sign-out"></i></span>                   
                  <span class="text">Çıkış Yap</span>                 
                </a>               
              </li>             
            </ul>           
          </div>         
        </div>       
      </div>     
    </div>     
    
  </div> 
</div>