document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const errorMessage = document.getElementById("errorMessage");
  const submitButton = document.querySelector(".login-submit-btn");

  const initPasswordToggles = () => {
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

  initPasswordToggles();

  // Forçar tema escuro
  document.body.classList.remove("light-theme");
  localStorage.setItem("theme", "dark");

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Feedback visual no botão
    submitButton.disabled = true;
    submitButton.textContent = "A verificar...";
    errorMessage.textContent = "";
    errorMessage.style.display = "none";

    const formData = new FormData(loginForm);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await fetch("api/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        submitButton.textContent = "Sucesso!";
        submitButton.style.backgroundColor = "#28a745"; // Verde

        // Redirecionar para a página principal após um breve momento
        setTimeout(() => {
          window.location.href = "index.html";
        }, 1000);
      } else {
        throw new Error(result.message || "Ocorreu um erro.");
      }
    } catch (error) {
      errorMessage.textContent = error.message;
      errorMessage.style.display = "block";

      submitButton.disabled = false;
      submitButton.textContent = "Entrar";
    }
  });
});
