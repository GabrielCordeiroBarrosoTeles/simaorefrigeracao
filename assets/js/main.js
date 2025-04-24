/**
 * FrioCerto - Soluções em Ar Condicionado
 * JavaScript principal
 */

document.addEventListener("DOMContentLoaded", () => {
  // Menu móvel
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const mobileMenu = document.querySelector(".mobile-menu")

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", function () {
      mobileMenu.classList.toggle("active")

      // Animar o ícone do menu
      const spans = this.querySelectorAll("span")
      if (mobileMenu.classList.contains("active")) {
        spans[0].style.transform = "rotate(-45deg) translate(-5px, 6px)"
        spans[1].style.opacity = "0"
        spans[2].style.transform = "rotate(45deg) translate(-5px, -6px)"
      } else {
        spans[0].style.transform = "none"
        spans[1].style.opacity = "1"
        spans[2].style.transform = "none"
      }
    })

    // Fechar menu ao clicar em um link
    const mobileLinks = mobileMenu.querySelectorAll("a")
    mobileLinks.forEach((link) => {
      link.addEventListener("click", () => {
        mobileMenu.classList.remove("active")

        // Resetar o ícone do menu
        const spans = mobileMenuToggle.querySelectorAll("span")
        spans[0].style.transform = "none"
        spans[1].style.opacity = "1"
        spans[2].style.transform = "none"
      })
    })
  }

  // Rolagem suave para links de âncora
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()

      const targetId = this.getAttribute("href")
      const targetElement = document.querySelector(targetId)

      if (targetElement) {
        // Calcular a posição de rolagem considerando o header fixo
        const headerHeight = document.querySelector(".header").offsetHeight
        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight

        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        })
      }
    })
  })

  // Animação de entrada para elementos ao rolar
  const animateOnScroll = () => {
    const elements = document.querySelectorAll(".service-card, .stat-card, .testimonial-card")

    elements.forEach((element) => {
      const elementPosition = element.getBoundingClientRect().top
      const windowHeight = window.innerHeight

      if (elementPosition < windowHeight - 100) {
        element.style.opacity = "1"
        element.style.transform = "translateY(0)"
      }
    })
  }

  // Inicializar elementos com opacidade 0
  document.querySelectorAll(".service-card, .stat-card, .testimonial-card").forEach((element) => {
    element.style.opacity = "0"
    element.style.transform = "translateY(20px)"
    element.style.transition = "opacity 0.5s ease, transform 0.5s ease"
  })

  // Executar animação no carregamento e ao rolar
  window.addEventListener("load", animateOnScroll)
  window.addEventListener("scroll", animateOnScroll)

  // Validação de formulário
  const contactForm = document.querySelector("form")
  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      let isValid = true

      // Validar campos obrigatórios
      const requiredFields = contactForm.querySelectorAll("[required]")
      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.style.borderColor = "#ef4444"
        } else {
          field.style.borderColor = ""
        }
      })

      // Validar email
      const emailField = contactForm.querySelector('input[type="email"]')
      if (emailField && emailField.value.trim()) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailPattern.test(emailField.value)) {
          isValid = false
          emailField.style.borderColor = "#ef4444"
        }
      }

      // Validar telefone
      const phoneField = contactForm.querySelector('input[name="phone"]')
      if (phoneField && phoneField.value.trim()) {
        const phonePattern = /^$$\d{2}$$ \d{4,5}-\d{4}$/
        if (!phonePattern.test(phoneField.value)) {
          // Não bloquear o envio, apenas formatar o número
          const cleaned = phoneField.value.replace(/\D/g, "")
          if (cleaned.length === 11) {
            phoneField.value = `(${cleaned.substring(0, 2)}) ${cleaned.substring(2, 7)}-${cleaned.substring(7, 11)}`
          } else if (cleaned.length === 10) {
            phoneField.value = `(${cleaned.substring(0, 2)}) ${cleaned.substring(2, 6)}-${cleaned.substring(6, 10)}`
          }
        }
      }

      if (!isValid) {
        e.preventDefault()
        alert("Por favor, preencha todos os campos obrigatórios corretamente.")
      }
    })

    // Máscara para telefone
    const phoneInput = contactForm.querySelector('input[name="phone"]')
    if (phoneInput) {
      phoneInput.addEventListener("input", (e) => {
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
  }
})
