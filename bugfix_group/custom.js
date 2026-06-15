$(function () {
  Fancybox.bind();

  $("#selectAll").on("change", function () {
    $(".form-check .form-check-input").prop("checked", $(this).prop("checked"));
  });
  function editorFunction(editorId) {
    const quill = new Quill(editorId, {
      theme: "snow",
    });
  }
  function uploadImageFunction(imageId, previewId) {
    $(imageId).on("change", function () {
      var input = this; // 'this' is the DOM element here
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $(previewId).css("background-image", "url(" + e.target.result + ")");
          $(previewId).hide();
          $(previewId).fadeIn(650);
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  }
  uploadImageFunction("#coverImageUpload", "#coverImagePreview");
  uploadImageFunction("#imageUpload", "#profileImagePreview");
  $(".js-get-year").html(new Date().getFullYear());
  $(".js-phone-mask").inputmask("0(599) 999 99 99", { clearIncomplete: true });
  var sortColumn = $(".js-datatable").data("sort-column");

  var columnCount = $(".js-datatable").find("thead th").length;

  var table = $(".js-datatable").DataTable({
    scrollCollapse: true,
    scrollX: true,
    // responsive: true,
    order: sortColumn !== undefined && sortColumn <= columnCount ? [[sortColumn > 0 ? sortColumn - 1 : 0, "desc"]] : [],
    pagingType: "simple_numbers", // This changes pagination to just Previous/Next buttons
    drawCallback: function() {
      // Add double scroll
      $('.dt-scroll-body').doubleScroll({
        resetOnWindowResize: true
      });
    }
  });
  window.table = table;


  $(".sortable-table").each(function () {
    let url = $(this).data("url");
    let originalIndex;
    let itemEl;
    let originalParent;
    let nextSibling;

    let sortable = new Sortable(this, {
      handle: ".js-sorting-row",
      animation: 150,

      onStart: function (evt) {
        originalIndex = evt.oldIndex;
        itemEl = evt.item;
        originalParent = evt.item.parentNode;
        nextSibling = evt.item.nextSibling;
      },

      onEnd: function (evt) {
        let newIndex = evt.newIndex;
        let ids = [];
        let ranks = [];
        $(evt.from)
          .children("tr")
          .each(function (index) {
            let id = $(this).find(".js-sorting-row").data("id");
            let rank = index + 1;
            ids.push(id);
            ranks.push(rank);
          });

        $.ajax({
          url: url,
          method: "POST",
          data: {
            ids: JSON.stringify(ids),
            ranks: JSON.stringify(ranks),
          },
          success: function (response) {
            if (response !== "1") {
              if (originalParent) {
                originalParent.insertBefore(itemEl, nextSibling);
              }

              Swal.fire({
                icon: "error",
                title: "Hata",
                text: "Sıralama kaydedilemedi, eski pozisyonuna geri alındı.",
              });
            }
          },
          error: function () {
            if (originalParent) {
              originalParent.insertBefore(itemEl, nextSibling);
            }

            Swal.fire({
              icon: "error",
              title: "Hata",
              text: "Sunucuya ulaşılamadı, eski pozisyonuna geri alındı.",
            });
          },
        });
      },
    });
  });
  
  $("[data-select2-selector='default']").select2({ theme: "bootstrap-5", width: "100%" });

  //Popover
  $("[title]:not('.select2-selection__rendered')").each(function () {
    let $this = $(this);
    let titleText = $this.attr("title");

    // tippy(this, {
    //   content: titleText,
    //   allowHTML: true,
    //   delay: 0,
    // });

    $this.removeAttr("title");
  });

  //Datepicker
  $(".js-datepicker").each(function () {
    var $input = $(this); // 'this' referansını sakla

    $input.daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      autoUpdateInput: false,
      autoApply: true, // Kullanıcı 'Uygula' butonuna basmadan seçim yapılmasını sağlar
      locale: {
        format: "DD/MM/YYYY",
        separator: " - ",
        applyLabel: "Uygula",
        cancelLabel: "İptal",
        fromLabel: "Başlangıç",
        toLabel: "Bitiş",
        customRangeLabel: "Özel",
        weekLabel: "H",
        daysOfWeek: ["Paz", "Pzt", "Sal", "Çar", "Per", "Cum", "Cmt"],
        monthNames: ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"],
        firstDay: 1,
      },
    });

    // Tarih seçildiğinde input alanını güncelle
    $input.on("apply.daterangepicker", function (ev, picker) {
      $(this).val(picker.startDate.format("DD/MM/YYYY"));
    });

    // İptal edildiğinde input alanını temizle
    $input.on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
    });
  });

  $(".js-datepicker-time").each(function () {
    var $input = $(this); // 'this' referansını sakla

    $input.daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: true,
      timePicker24Hour: true,
      autoUpdateInput: false,
      autoApply: true, // Kullanıcı 'Uygula' butonuna basmadan seçim yapılmasını sağlar
      locale: {
        format: "DD/MM/YYYY HH:mm", // Tarih ve saat formatı
        separator: " - ",
        applyLabel: "Uygula",
        cancelLabel: "İptal",
        fromLabel: "Başlangıç",
        toLabel: "Bitiş",
        customRangeLabel: "Özel",
        weekLabel: "H",
        daysOfWeek: ["Paz", "Pzt", "Sal", "Çar", "Per", "Cum", "Cmt"],
        monthNames: ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"],
        firstDay: 1,
      },
    });

    // Tarih ve saat seçildiğinde input alanını güncelle
    $input.on("apply.daterangepicker", function (ev, picker) {
      $(this).val(picker.startDate.format("DD/MM/YYYY HH:mm"));
    });

    // İptal edildiğinde input alanını temizle
    $input.on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
    });
  });

  $(".js-datepicker-range").each(function () {
    var $input = $(this);

    $input.daterangepicker({
      showDropdowns: true,
      autoApply: true,
      autoUpdateInput: false,
      locale: {
        format: "DD/MM/YYYY",
        separator: " - ",
        applyLabel: "Uygula",
        cancelLabel: "İptal",
        fromLabel: "Başlangıç",
        toLabel: "Bitiş",
        customRangeLabel: "Özel",
        weekLabel: "H",
        daysOfWeek: ["Paz", "Pzt", "Sal", "Çar", "Per", "Cum", "Cmt"],
        monthNames: ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"],
        firstDay: 1,
      },
    });

    // Tarih aralığı seçildiğinde input alanını güncelle
    $input.on("apply.daterangepicker", function (ev, picker) {
      $(this).val(picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY"));
    });

    // İptal edildiğinde input alanını temizle
    $input.on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
    });
  });

  $(".js-summernote").summernote({
    tabsize: 2,
    height: 300,
    minHeight: 300,
    toolbar: [
      ["style", ["style"]],
      ["font", ["bold", "underline", "clear"]],
      ["color", ["color"]],
      ["para", ["ul", "ol", "paragraph"]],
      ["table", ["table"]],
      ["insert", ["link", "picture", "video"]],
      ["view", ["fullscreen", "codeview", "help"]],
    ],
    callbacks: {
      onImageUpload: function (files) {
        if (files.length > 0) {
          var url = $(this).data("url");
          console.log(url);

          uploadImage(files[0], url);
        }
      },
      onPaste: function (e) {
        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
        e.preventDefault();
        document.execCommand('insertText', false, bufferText);
      },
    },
  });

  function uploadImage(file, url) {
    var data = new FormData();
    data.append("upload", file);

    $.ajax({
      // Dinamik olarak alınan URL
      method: "POST",
      data: data,
      contentType: false,
      processData: false,
      success: function (response) {
        var responseJson = JSON.parse(response);
        if (responseJson.uploaded) {
          // Get the specific Summernote instance that triggered the upload
          var context = this;
          $(context).summernote("insertImage", responseJson.url);
        } else {
          alert("Image upload failed: " + responseJson.error.message);
        }
      },
      error: function () {
        alert("There was an error uploading the image.");
      },
    });
  }
  //Delete button
  $(document).on("click", ".delete-btn", function () {
    let id = $(this).data("id");
    let url = $(this).data("url");

    Swal.fire({
      title: "İçeriği silmek istediğinize emin misiniz?",
      text: "Bu işlemi geri alamayacaksınız!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Evet, sil!",
      cancelButtonText: "Hayır, iptal et",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `${url}?id=${id}`;
      }
    });
  });
  $(".js-bio-modal-trigger").on("click", function () {
    let textTitle = $(this).prev().val();
    let textarea = $(this).next();
    let textValue = textarea.html();
    $(".js-paste-text").html(textValue);
    $(".js-paste-title").html(textTitle);
  });
  $(".dt-bootstrap5").addClass("dataTables_wrapper");
  $(".dt-paging").addClass("dataTables_paginate");
});
