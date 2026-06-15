<?php include '../inc/db/session.php'; ?>
<?php include '../inc/db/user-veri.php'; ?>
<?php include '../inc/ust/admin-header.php'; ?>
            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <!-- Website Analytics -->
                <div class="col-lg-6 mb-4">
                  <div
                    class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg"
                    id="swiper-with-pagination-cards">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <div class="row">
                          <div class="col-12">
                            <h5 class="text-white mb-0 mt-2">Günlük Analizler</h5>
                            <small>Evo Eğitimin Günlük Raporları</small>
                          </div>
                          <div class="row">
                            <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                              <h6 class="text-white mt-0 mt-md-3 mb-3">Analizler</h6>
                              <div class="row">
                                <div class="col-6">
                                  <ul class="list-unstyled mb-0">
                                    <li class="d-flex mb-4 align-items-center">
                                      <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                      <p class="mb-0">Yeni Kayıt</p>
                                    </li>
                                    <li class="d-flex align-items-center mb-2">
                                      <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                      <p class="mb-0">Üye Öğrenci</p>
                                    </li>
                                  </ul>
                                </div>
                                <div class="col-6">
                                  <ul class="list-unstyled mb-0">
                                    <li class="d-flex mb-4 align-items-center">
                                      <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                      <p class="mb-0">Alınan Paket</p>
                                    </li>
                                    <li class="d-flex align-items-center mb-2">
                                      <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">0₺</p>
                                      <p class="mb-0">Kazanç</p>
                                    </li>
                                  </ul>
                                </div>
                              </div>
                            </div>
                            <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                              <img
                                src="assets/img/illustrations/add-new-roles.png"
                                alt="Website Analytics"
                                width="170"
                                class="card-website-analytics-img" />
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="row">
                          <div class="col-12">
                            <h5 class="text-white mb-0 mt-2">Haftalık Analizler</h5>
                            <small>Evo Eğitimin Haftalık Raporları</small>
                          </div>
                          <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                            <h6 class="text-white mt-0 mt-md-3 mb-3">Analizler</h6>
                            <div class="row">
                              <div class="col-6">
                                <ul class="list-unstyled mb-0">
                                  <li class="d-flex mb-4 align-items-center">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Yeni Kayıt</p>
                                  </li>
                                  <li class="d-flex align-items-center mb-2">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Üye Öğrenci</p>
                                  </li>
                                </ul>
                              </div>
                              <div class="col-6">
                                <ul class="list-unstyled mb-0">
                                  <li class="d-flex mb-4 align-items-center">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Alınan Paket</p>
                                  </li>
                                  <li class="d-flex align-items-center mb-2">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">0₺</p>
                                    <p class="mb-0">Kazanç</p>
                                  </li>
                                </ul>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                            <img
                              src="assets/img/illustrations/wizard-create-deal-confirm.png"
                              alt="Website Analytics"
                              width="192"
                              class="card-website-analytics-img" />
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="row">
                          <div class="col-12">
                            <h5 class="text-white mb-0 mt-2">Tüm Analizler</h5>
                            <small>Evo Eğitimin Tüm Zaman Raporları</small>
                          </div>
                          <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                            <h6 class="text-white mt-0 mt-md-3 mb-3">Analizler</h6>
                            <div class="row">
                              <div class="col-6">
                                <ul class="list-unstyled mb-0">
                                  <li class="d-flex mb-4 align-items-center">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Yeni Kayıt</p>
                                  </li>
                                  <li class="d-flex align-items-center mb-2">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Üye Öğrenci</p>
                                  </li>
                                </ul>
                              </div>
                              <div class="col-6">
                                <ul class="list-unstyled mb-0">
                                  <li class="d-flex mb-4 align-items-center">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">+0</p>
                                    <p class="mb-0">Alınan Paket</p>
                                  </li>
                                  <li class="d-flex align-items-center mb-2">
                                    <p class="mb-0 fw-semibold me-2 website-analytics-text-bg">0₺</p>
                                    <p class="mb-0">Kazanç</p>
                                  </li>
                                </ul>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                            <img
                              src="assets/img/illustrations/page-pricing-standard.png"
                              alt="Website Analytics"
                              width="200"
                              class="card-website-analytics-img" />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-pagination"></div>
                  </div>
                </div>
                <!--/ Website Analytics -->

                <!-- Sales Overview -->
                <div class="col-lg-3 col-sm-6 mb-4">
                  <div class="card">
                    <div class="card-header">
                      <div class="d-flex justify-content-between">
                        <small class="d-block mb-1 text-muted">Kullanıcı Verileri</small>
                      </div>
                      <h4 class="card-title mb-1">0 Öğrenci</h4>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-4">
                          <div class="d-flex gap-2 align-items-center mb-2">
                            <span class="badge bg-label-info p-1 rounded"
                              ><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
   <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
   <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
   <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
</svg></span>
                            <p class="mb-0">Üye</p>
                          </div>
                          <h5 class="mb-0 pt-1 text-nowrap">0%</h5>
                          <small class="text-muted">0</small>
                        </div>
                        <div class="col-4">
                          <div class="divider divider-vertical">
                            <div class="divider-text">
                              <span class="badge-divider-bg bg-label-secondary">VS</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                            <p class="mb-0">Kayıtlı</p>
                            <span class="badge bg-label-primary p-1 rounded"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
   <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
   <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
   <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
</svg></span>
                          </div>
                          <h5 class="mb-0 pt-1 text-nowrap ms-lg-n3 ms-xl-0">0%</h5>
                          <small class="text-muted">0</small>
                        </div>
                      </div>
                      <div class="d-flex align-items-center mt-4">
                        <div class="progress w-100" style="height: 8px">
                          <div
                            class="progress-bar bg-info"
                            style="width: 70%"
                            role="progressbar"
                            aria-valuenow="70"
                            aria-valuemin="0"
                            aria-valuemax="100"></div>
                          <div
                            class="progress-bar bg-primary"
                            role="progressbar"
                            style="width: 30%"
                            aria-valuenow="30"
                            aria-valuemin="0"
                            aria-valuemax="100"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--/ Sales Overview -->

                <!-- Revenue Generated -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                  <div class="card">
                    <div class="card-body pb-0">
                      <div class="card-icon">
                        <span class="badge bg-label-success rounded-pill p-2">
                          <i class="ti ti-credit-card ti-sm"></i>
                        </span>
                      </div>
                      <h5 class="card-title mb-0 mt-2">0.00₺</h5>
                      <small>Başarılı Ödeme</small>
                    </div>
                    <div id="revenueGenerated"></div>
                  </div>
                </div>
                <!--/ Revenue Generated -->

                <!-- Earning Reports -->
                <div class="col-lg-6 mb-4">
                  <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between mb-lg-n4">
                      <div class="card-title mb-0">
                        <h5 class="mb-0">Ödeme Raporları</h5>
                        <small class="text-muted">Ödeme Raporlar Yüklenen Bakiyelere Göre Raporlanmaktadır.</small>
                      </div>
                      <div class="dropdown">
                        <button
                          class="btn p-0"
                          type="button"
                          id="earningReportsId"
                          data-bs-toggle="dropdown"
                          aria-haspopup="true"
                          aria-expanded="false">
                          <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReportsId">
                          <a class="dropdown-item" href="javascript:void(0);">View More</a>
                          <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                        </div>
                      </div>
                      <!-- </div> -->
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-4 d-flex flex-column align-self-end">
                          <div class="d-flex gap-2 align-items-center mb-2 pb-1 flex-wrap">
                            <h1 class="mb-0">0.00₺</h1>
                            <div class="badge rounded bg-label-success">+0%</div>
                          </div>
                          <small class="text-muted">Geçen Haftaya Göre Bu Haftaki Satış</small>
                        </div>
                        <div class="col-12 col-md-8">
                          <div id="weeklyEarningReports"></div>
                        </div>
                      </div>
                      <div class="border rounded p-3 mt-2">
                        <div class="row gap-4 gap-sm-0">
                          <div class="col-12 col-sm-4">
                            <div class="d-flex gap-2 align-items-center">
<img src="paytr.svg" style="max-width: 76%;">
                            </div>
                            <h4 class="my-2 pt-1">0.00₺</h4>
                            <div class="progress w-75" style="height: 4px">
                              <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: 65%"
                                aria-valuenow="65"
                                aria-valuemin="0"
                                aria-valuemax="100"></div>
                            </div>
                          </div>
                          <div class="col-12 col-sm-4">
                            <div class="d-flex gap-2 align-items-center">
<img src="paymax.svg" style="max-width: 61%;"> 
                            </div>
                            <h4 class="my-2 pt-1">0.00₺</h4>
                            <div class="progress w-75" style="height: 4px">
                              <div
                                class="progress-bar bg-info"
                                role="progressbar"
                                style="width: 50%"
                                aria-valuenow="50"
                                aria-valuemin="0"
                                aria-valuemax="100"></div>
                            </div>
                          </div>
                          <div class="col-12 col-sm-4">
                            <div class="d-flex gap-2 align-items-center">
   <img src="gpay.png" style="max-width: 25%;"> 
                            </div>
                            <h4 class="my-2 pt-1">0.00₺</h4>
                            <div class="progress w-75" style="height: 4px">
                              <div
                                class="progress-bar bg-danger"
                                role="progressbar"
                                style="width: 65%"
                                aria-valuenow="65"
                                aria-valuemin="0"
                                aria-valuemax="100"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--/ Earning Reports -->

                <!-- Support Tracker -->
                <div class="col-md-6 mb-4">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between pb-0">
                      <div class="card-title mb-0">
                        <h5 class="mb-0">Ders İstatistikleri</h5>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-sm-4 col-md-12 col-lg-4">
                          <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-1">
                            <h1 class="mb-0">0</h1>
                            <p class="mb-0">Toplam Ders</p>
                          </div>
                          <ul class="p-0 m-0">
                            <li class="d-flex gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                              <div class="badge rounded bg-label-primary p-1"><i class="ti ti-ticket ti-sm"></i></div>
                              <div>
                                <h6 class="mb-0 text-nowrap">Tamamlanan Dersler</h6>
                                <small class="text-muted">0</small>
                              </div>
                            </li>
                            <li class="d-flex gap-3 align-items-center mb-lg-3 pb-1">
                              <div class="badge rounded bg-label-info p-1">
                                <i class="ti ti-circle-check ti-sm"></i>
                              </div>
                              <div>
                                <h6 class="mb-0 text-nowrap">Bekleyen Dersler</h6>
                                <small class="text-muted">0</small>
                              </div>
                            </li>
                            <li class="d-flex gap-3 align-items-center pb-1">
                              <div class="badge rounded bg-label-warning p-1"><i class="ti ti-clock ti-sm"></i></div>
                              <div>
                                <h6 class="mb-0 text-nowrap">İptal Edilen Dersler</h6>
                                <small class="text-muted">0</small>
                              </div>
                            </li>
                          </ul>
                        </div>
                        <div class="col-12 col-sm-8 col-md-12 col-lg-8">
                          <div id="supportTracker"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--/ Support Tracker -->
              </div>
            </div>
            <!--/ Content -->
<?php include '../inc/ust/admin-footer.php'; ?>