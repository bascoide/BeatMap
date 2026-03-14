// Efeito de Navbar ao rolar
const navbar = document.querySelector(".navbar");

window.addEventListener("scroll", () => {
  if (!navbar) {
    return;
  }

  if (window.scrollY > 80) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

window.BeatmapInitPasswordToggles = function () {
  const passwordInputs = document.querySelectorAll(
    'input[type="password"]:not([data-password-toggle-ready])',
  );

  passwordInputs.forEach((input) => {
    input.setAttribute("data-password-toggle-ready", "1");

    const wrapper = document.createElement("div");
    wrapper.className = "password-toggle-wrapper";

    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    input.classList.add("password-toggle-input");

    const toggleButton = document.createElement("button");
    toggleButton.type = "button";
    toggleButton.className = "password-toggle-btn";
    toggleButton.textContent = "👁";
    toggleButton.setAttribute("aria-label", "Mostrar palavra-passe");
    toggleButton.setAttribute("title", "Mostrar palavra-passe");

    toggleButton.addEventListener("click", () => {
      const shouldShow = input.type === "password";
      input.type = shouldShow ? "text" : "password";
      toggleButton.classList.toggle("is-visible", shouldShow);
      toggleButton.setAttribute(
        "aria-label",
        shouldShow ? "Ocultar palavra-passe" : "Mostrar palavra-passe",
      );
      toggleButton.setAttribute(
        "title",
        shouldShow ? "Ocultar palavra-passe" : "Mostrar palavra-passe",
      );
    });

    wrapper.appendChild(toggleButton);
  });
};

document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.BeatmapInitPasswordToggles === "function") {
    window.BeatmapInitPasswordToggles();
  }
});

// Scroll Reveal
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  },
  { threshold: 0.1 },
);

document.querySelectorAll(".text-block, .stat-item").forEach((el) => {
  el.style.opacity = "0";
  el.style.transform = "translateY(30px)";
  el.style.transition = "all 0.8s ease-out";
  observer.observe(el);
});

// Scroll Suave para links internos
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      window.scrollTo({
        top: target.offsetTop - 80,
        behavior: "smooth",
      });
    }
  });
});

// Atualizar contador de músicos via base de dados
document.addEventListener("DOMContentLoaded", () => {
  const artistCount = document.getElementById("artist-count");
  if (artistCount) {
    fetch("/beatmap(index)/get_stats.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.total !== undefined) artistCount.innerText = data.total;
      })
      .catch((error) => console.error("Erro ao carregar estatísticas:", error));
  }
});

document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("contactWidgetOverlay")) {
    return;
  }

  const overlay = document.createElement("div");
  overlay.className = "contact-overlay";
  overlay.id = "contactWidgetOverlay";
  overlay.innerHTML = `
    <div class="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contactWidgetTitle">
      <div class="contact-modal-header">
        <h2 class="contact-modal-title" id="contactWidgetTitle">Falar comigo</h2>
        <button type="button" class="contact-close-btn" aria-label="Fechar formulario">x</button>
      </div>
      <form class="contact-form" id="contactWidgetForm">
        <div class="contact-form-grid">
          <label>Nome<span class="required-mark">*</span>
            <input type="text" name="name" required maxlength="100" />
          </label>
          <label>Email<span class="required-mark">*</span>
            <input type="email" name="email" class="contact-email-input" required maxlength="160" />
          </label>
        </div>
        <div class="contact-form-grid">
          <label>Telefone
            <input type="text" name="phone" maxlength="40" />
          </label>
          <label>Assunto<span class="required-mark">*</span>
            <input type="text" name="subject" required maxlength="160" />
          </label>
        </div>
        <label>Mensagem<span class="required-mark">*</span>
          <textarea name="message" required maxlength="4000"></textarea>
        </label>
        <div class="contact-form-actions">
          <div class="contact-status" id="contactWidgetStatus"></div>
          <button type="submit" class="contact-submit-btn" id="contactWidgetSubmit">Enviar email</button>
        </div>
      </form>
    </div>
  `;

  const button = document.createElement("button");
  button.type = "button";
  button.className = "contact-fab";
  button.id = "contactWidgetOpenBtn";
  button.textContent = "Contacto";

  document.body.appendChild(button);
  document.body.appendChild(overlay);

  const closeBtn = overlay.querySelector(".contact-close-btn");
  const form = document.getElementById("contactWidgetForm");
  const statusEl = document.getElementById("contactWidgetStatus");
  const submitBtn = document.getElementById("contactWidgetSubmit");
  const emailInput = form.querySelector('input[name="email"]');
  let lockedEmailValue = "";

  const lockContactEmail = (email) => {
    if (!emailInput) {
      return;
    }

    const normalizedEmail = (email || "").toString().trim();
    if (!normalizedEmail) {
      return;
    }

    lockedEmailValue = normalizedEmail;
    emailInput.value = normalizedEmail;
    emailInput.readOnly = true;
    emailInput.classList.add("is-locked");
    emailInput.setAttribute("aria-readonly", "true");
  };

  fetch("/beatmap(index)/contact_session.php", { cache: "no-store" })
    .then((response) => response.json())
    .then((data) => {
      if (data && data.loggedIn && data.email) {
        lockContactEmail(data.email);
      }
    })
    .catch(() => {
      // Se falhar, o formulario continua normal.
    });

  const openModal = () => {
    overlay.classList.add("is-open");
    document.body.style.overflow = "hidden";
  };

  const closeModal = () => {
    overlay.classList.remove("is-open");
    document.body.style.overflow = "";
  };

  button.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  overlay.addEventListener("click", (event) => {
    if (event.target === overlay) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && overlay.classList.contains("is-open")) {
      closeModal();
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(form);
    const payload = {
      name: (formData.get("name") || "").toString().trim(),
      email: (formData.get("email") || "").toString().trim(),
      phone: (formData.get("phone") || "").toString().trim(),
      subject: (formData.get("subject") || "").toString().trim(),
      message: (formData.get("message") || "").toString().trim(),
    };

    statusEl.textContent = "A enviar...";
    submitBtn.disabled = true;

    try {
      const response = await fetch("/beatmap(index)/contact_send.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || "Falha ao enviar mensagem.");
      }

      statusEl.textContent = "Mensagem enviada com sucesso.";
      form.reset();

      if (lockedEmailValue) {
        emailInput.value = lockedEmailValue;
      }

      setTimeout(() => {
        closeModal();
        statusEl.textContent = "";
      }, 1000);
    } catch (error) {
      statusEl.textContent = error.message || "Nao foi possivel enviar.";
    } finally {
      submitBtn.disabled = false;
    }
  });
});
