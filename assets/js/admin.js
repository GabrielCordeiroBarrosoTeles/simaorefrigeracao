/**
 * FrioCerto - Painel Administrativo
 * JavaScript principal
 */

document.addEventListener("DOMContentLoaded", () => {
  // Atualizar visualização do ícone ao digitar no campo de ícone
  const iconeInput = document.getElementById("icone")
  if (iconeInput) {
    const iconePreview = iconeInput.parentElement.querySelector(".input-group-text i")

    iconeInput.addEventListener("input", function () {
      const iconeName = this.value.trim()
      if (iconeName) {
        iconePreview.className = "fas fa-" + iconeName
      } else {
        iconePreview.className = "fas fa-icons"
      }
    })
  }

  // Inicializar tooltips
  $(() => {
    $('[data-toggle="tooltip"]').tooltip()
  })

  // Inicializar popovers
  $(() => {
    $('[data-toggle="popover"]').popover()
  })

  // Confirmação de exclusão
  const deleteButtons = document.querySelectorAll(".btn-delete")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      if (!confirm("Tem certeza que deseja excluir este item?")) {
        e.preventDefault()
      }
    })
  })

  // Máscara para telefone
  const telefoneInput = document.getElementById("telefone")
  if (telefoneInput) {
    telefoneInput.addEventListener("input", (e) => {
      let value = e.target.value.replace(/\D/g, "")

      if (value.length > 0) {
        value = "(" + value
      }
      if (value.length > 3) {
        value = value.substring(0, 3) + ") " + value.substring(3)
      }
      if (value.length > 10) {
        value = value.substring(0, 10) + "-" + value.substring(10)
      }
      if (value.length > 15) {
        value = value.substring(0, 15)
      }

      e.target.value = value
    })
  }

  // Sidebar toggle para dispositivos móveis
  const sidebarToggle = document.querySelector('[data-widget="pushmenu"]')
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      document.body.classList.toggle("sidebar-collapse")
      document.body.classList.toggle("sidebar-open")
    })
  }

  // Auto-dismiss para alertas
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.classList.add("fade")
      setTimeout(() => {
        alert.remove()
      }, 500)
    }, 5000)
  })
})
