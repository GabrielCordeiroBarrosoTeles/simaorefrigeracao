/**
 * Admin Panel JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  // Check if jQuery is already loaded
  if (typeof jQuery === "undefined") {
    // If not, attempt to load it from a CDN or other source
    var script = document.createElement("script")
    script.src = "https://code.jquery.com/jquery-3.6.0.min.js" // Or another jQuery CDN
    script.type = "text/javascript"
    script.onload = () => {
      // jQuery is now loaded, continue with initialization
      window.jQuery = jQuery // Declare jQuery in the global scope
      initializeAdminPanel()
    }
    document.head.appendChild(script)
  } else {
    // jQuery is already loaded, proceed with initialization
    initializeAdminPanel()
  }

  function initializeAdminPanel() {
    // Toggle sidebar
    const sidebarToggle = document.getElementById("sidebarToggle")
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", (e) => {
        e.preventDefault()
        document.querySelector(".sidebar").classList.toggle("active")
        document.querySelector(".content-wrapper").classList.toggle("active")
      })
    }

    // Close alerts automatically
    const alerts = document.querySelectorAll(".alert-dismissible")
    alerts.forEach((alert) => {
      setTimeout(() => {
        const closeButton = alert.querySelector(".close")
        if (closeButton) {
          closeButton.click()
        } else {
          alert.style.display = "none"
        }
      }, 5000)
    })

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll("[data-confirm]")
    deleteButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        if (!confirm(this.getAttribute("data-confirm") || "Tem certeza que deseja excluir este item?")) {
          e.preventDefault()
        }
      })
    })

    // Initialize tooltips
    if (typeof jQuery !== "undefined" && typeof jQuery.fn.tooltip !== "undefined") {
      jQuery('[data-toggle="tooltip"]').tooltip()
    }

    // Initialize popovers
    if (typeof jQuery !== "undefined" && typeof jQuery.fn.popover !== "undefined") {
      jQuery('[data-toggle="popover"]').popover()
    }

    // Custom file input
    const customFileInputs = document.querySelectorAll(".custom-file-input")
    customFileInputs.forEach((input) => {
      input.addEventListener("change", function () {
        const fileName = this.files[0] ? this.files[0].name : "Escolher arquivo"
        const label = this.nextElementSibling
        if (label) {
          label.textContent = fileName
        }
      })
    })

    // Phone mask
    const phoneInputs = document.querySelectorAll('input[name="telefone"]')
    phoneInputs.forEach((input) => {
      input.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, "")
        if (value.length <= 10) {
          value = value.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3")
        } else {
          value = value.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3")
        }
        this.value = value
      })
    })

    // CEP mask
    const cepInputs = document.querySelectorAll('input[name="cep"]')
    cepInputs.forEach((input) => {
      input.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, "")
        value = value.replace(/(\d{5})(\d{3})/, "$1-$2")
        this.value = value
      })
    })

    // Fetch address by CEP
    cepInputs.forEach((input) => {
      input.addEventListener("blur", function () {
        const cep = this.value.replace(/\D/g, "")
        if (cep.length === 8) {
          fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then((response) => response.json())
            .then((data) => {
              if (!data.erro) {
                const form = this.closest("form")
                if (form) {
                  const endereco = form.querySelector('input[name="endereco"]')
                  const cidade = form.querySelector('input[name="cidade"]')
                  const estado = form.querySelector('input[name="estado"]')

                  if (endereco) endereco.value = data.logradouro
                  if (cidade) cidade.value = data.localidade
                  if (estado) estado.value = data.uf
                }
              }
            })
            .catch((error) => console.error("Erro ao buscar CEP:", error))
        }
      })
    })
  }
})
