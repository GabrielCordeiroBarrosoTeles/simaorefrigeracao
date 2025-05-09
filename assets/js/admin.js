import { Chart } from "@/components/ui/chart"
// Função para inicializar o DataTables
function initDataTable() {
  if ($.fn.DataTable) {
    $(".datatable").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json",
      },
      responsive: true,
    })
  }
}

// Função para inicializar o FullCalendar
function initCalendar() {
  if (typeof FullCalendar !== "undefined" && document.getElementById("calendar")) {
    var calendarEl = document.getElementById("calendar")
    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: "pt-br",
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },
      events: "admin-calendario-json.php",
      eventClick: (info) => {
        window.location.href = "admin-form.php?form=agendamento&id=" + info.event.id
      },
    })
    calendar.render()
  }
}

// Função para inicializar máscaras de input
function initMasks() {
  if (typeof $.fn.mask !== "undefined") {
    $(".mask-telefone").mask("(00) 00000-0000")
    $(".mask-cpf").mask("000.000.000-00")
    $(".mask-cnpj").mask("00.000.000/0000-00")
    $(".mask-cep").mask("00000-000")
    $(".mask-dinheiro").mask("000.000.000.000.000,00", { reverse: true })
  }
}

// Função para inicializar o sidebar toggle
function initSidebar() {
  $("#sidebarToggle, #sidebarToggleTop").on("click", (e) => {
    $("body").toggleClass("sidebar-toggled")
    $(".sidebar").toggleClass("toggled")
    if ($(".sidebar").hasClass("toggled")) {
      $(".sidebar .collapse").collapse("hide")
    }
  })
}

// Função para inicializar tooltips
function initTooltips() {
  if (typeof $.fn.tooltip !== "undefined") {
    $('[data-toggle="tooltip"]').tooltip()
  }
}

// Função para inicializar o select2
function initSelect2() {
  if (typeof $.fn.select2 !== "undefined") {
    $(".select2").select2({
      theme: "bootstrap4",
    })
  }
}

// Função para inicializar o summernote
function initSummernote() {
  if (typeof $.fn.summernote !== "undefined") {
    $(".summernote").summernote({
      height: 200,
      lang: "pt-BR",
    })
  }
}

// Função para inicializar o datepicker
function initDatepicker() {
  if (typeof $.fn.datepicker !== "undefined") {
    $(".datepicker").datepicker({
      format: "dd/mm/yyyy",
      language: "pt-BR",
      autoclose: true,
    })
  }
}

// Função para inicializar o timepicker
function initTimepicker() {
  if (typeof $.fn.timepicker !== "undefined") {
    $(".timepicker").timepicker({
      showMeridian: false,
      minuteStep: 15,
    })
  }
}

// Função para inicializar o colorpicker
function initColorpicker() {
  if (typeof $.fn.colorpicker !== "undefined") {
    $(".colorpicker").colorpicker()
  }
}

// Função para inicializar o fileupload
function initFileupload() {
  if (typeof $.fn.fileinput !== "undefined") {
    $(".file-input").fileinput({
      language: "pt-BR",
      showUpload: false,
      showCaption: false,
      showRemove: false,
      showCancel: false,
      showClose: false,
      showBrowse: true,
      browseOnZoneClick: true,
      removeLabel: "",
      removeTitle: "Cancelar ou resetar mudanças",
      elErrorContainer: "#kv-avatar-errors-1",
      msgErrorClass: "alert alert-block alert-danger",
      defaultPreviewContent: '<img src="https://via.placeholder.com/200x200" alt="Sua imagem" style="width:100%;">',
      layoutTemplates: { main2: "{preview} {browse}" },
      allowedFileExtensions: ["jpg", "png", "gif"],
    })
  }
}

// Função para inicializar o chart.js
function initCharts() {
  if (typeof Chart !== "undefined") {
    // Configurações globais
    Chart.defaults.font.family = "'Nunito', 'Segoe UI', 'Arial'"
    Chart.defaults.font.size = 12
    Chart.defaults.color = "#666"
  }
}

// Função para inicializar o modal de confirmação
function initConfirmModal() {
  $("body").on("click", ".btn-delete", function (e) {
    e.preventDefault()
    var href = $(this).attr("href")
    var name = $(this).data("name") || "este item"

    $("#confirmDeleteModal")
      .find(".modal-body")
      .html("Tem certeza que deseja excluir <strong>" + name + "</strong>?")
    $("#confirmDeleteBtn").attr("href", href)
    $("#confirmDeleteModal").modal("show")
  })
}

// Função para inicializar o formulário de busca
function initSearchForm() {
  $("#searchForm").on("submit", (e) => {
    var searchTerm = $("#searchInput").val().trim()
    if (searchTerm === "") {
      e.preventDefault()
      $("#searchInput").focus()
    }
  })
}

// Função para inicializar o botão de voltar ao topo
function initBackToTop() {
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $(".back-to-top").fadeIn()
    } else {
      $(".back-to-top").fadeOut()
    }
  })

  $(".back-to-top").click(() => {
    $("html, body").animate({ scrollTop: 0 }, 800)
    return false
  })
}

// Função para inicializar o menu mobile
function initMobileMenu() {
  $(".mobile-menu-toggle").on("click", function () {
    $(".mobile-menu").toggleClass("active")
    $(this).toggleClass("active")
  })
}

// Função para inicializar o preloader
function initPreloader() {
  $(window).on("load", () => {
    $("#preloader").fadeOut("slow", function () {
      $(this).remove()
    })
  })
}

// Função para inicializar o contador
function initCounter() {
  if (typeof $.fn.countTo !== "undefined") {
    $(".counter").countTo()
  }
}

// Função para inicializar o wow.js
function initWow() {
  if (typeof WOW !== "undefined") {
    new WOW().init()
  }
}

// Função para inicializar o isotope
function initIsotope() {
  if (typeof $.fn.isotope !== "undefined") {
    var $grid = $(".grid").isotope({
      itemSelector: ".grid-item",
      percentPosition: true,
      masonry: {
        columnWidth: ".grid-sizer",
      },
    })

    $(".filter-button-group").on("click", "button", function () {
      var filterValue = $(this).attr("data-filter")
      $grid.isotope({ filter: filterValue })
    })

    $(".button-group").each((i, buttonGroup) => {
      var $buttonGroup = $(buttonGroup)
      $buttonGroup.on("click", "button", function () {
        $buttonGroup.find(".active").removeClass("active")
        $(this).addClass("active")
      })
    })
  }
}

// Função para inicializar o lightbox
function initLightbox() {
  if (typeof $.fn.magnificPopup !== "undefined") {
    $(".popup-gallery").magnificPopup({
      delegate: "a",
      type: "image",
      tLoading: "Carregando imagem #%curr%...",
      mainClass: "mfp-img-mobile",
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1],
      },
      image: {
        tError: '<a href="%url%">A imagem #%curr%</a> não pode ser carregada.',
      },
    })
  }
}

// Função para inicializar o slick carousel
function initSlick() {
  if (typeof $.fn.slick !== "undefined") {
    $(".slick-carousel").slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 3,
      slidesToScroll: 1,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1,
            infinite: true,
            dots: true,
          },
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
      ],
    })
  }
}

// Inicializar todos os componentes quando o documento estiver pronto
$(document).ready(() => {
  // Certifique-se de que o jQuery está carregado
  if (typeof jQuery == "undefined") {
    console.error("jQuery não está carregado!")
    return
  }

  // Inicialize o $ como jQuery
  var $ = jQuery

  initSidebar()
  initDataTable()
  initCalendar()
  initMasks()
  initTooltips()
  initSelect2()
  initSummernote()
  initDatepicker()
  initTimepicker()
  initColorpicker()
  initFileupload()
  initCharts()
  initConfirmModal()
  initSearchForm()
  initBackToTop()
  initMobileMenu()
  initCounter()
  initWow()
  initIsotope()
  initLightbox()
  initSlick()

  // Fechar alertas automaticamente após 5 segundos
  setTimeout(() => {
    $(".alert-dismissible").fadeOut("slow")
  }, 5000)
})
