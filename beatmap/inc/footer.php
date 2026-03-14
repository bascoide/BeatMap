<?php if (isset($pageTitle) && $pageTitle !== 'BeatMap | Studio'): ?>
  </main>
<?php endif; ?>
<?php $contactSessionEmail = trim((string)($_SESSION['artist_email'] ?? $_SESSION['user_email'] ?? $_SESSION['email'] ?? '')); ?>
  <footer class="footer">
    <div class="footer-content">
      <div class="logo">BEAT<span>MAP</span></div>
      <p>&copy; <?php echo date('Y'); ?> BeatMap Studio. Todos os direitos reservados.</p>
    </div>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var navbar = document.querySelector('.navbar');
      var initPasswordToggles = function () {
        var passwordInputs = document.querySelectorAll('input[type="password"]:not([data-password-toggle-ready])');

        passwordInputs.forEach(function (input) {
          input.setAttribute('data-password-toggle-ready', '1');

          var wrapper = document.createElement('div');
          wrapper.className = 'password-toggle-wrapper';

          input.parentNode.insertBefore(wrapper, input);
          wrapper.appendChild(input);

          input.classList.add('password-toggle-input');

          var toggleButton = document.createElement('button');
          toggleButton.type = 'button';
          toggleButton.className = 'password-toggle-btn';
          toggleButton.setAttribute('aria-label', 'Mostrar palavra-passe');
          toggleButton.setAttribute('title', 'Mostrar palavra-passe');
          toggleButton.textContent = '👁';

          toggleButton.addEventListener('click', function () {
            var shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            toggleButton.classList.toggle('is-visible', shouldShow);
            toggleButton.setAttribute('aria-label', shouldShow ? 'Ocultar palavra-passe' : 'Mostrar palavra-passe');
            toggleButton.setAttribute('title', shouldShow ? 'Ocultar palavra-passe' : 'Mostrar palavra-passe');
          });

          wrapper.appendChild(toggleButton);
        });
      };

      initPasswordToggles();

      if (!navbar) return;

      function toggleNavbarScrolled() {
        if (window.scrollY > 80) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
      }

      window.addEventListener('scroll', toggleNavbarScrolled);
      toggleNavbarScrolled();

      if (document.getElementById('contactWidgetOverlay')) {
        return;
      }

      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'contact-fab';
      button.id = 'contactWidgetOpenBtn';
      button.textContent = 'Contacto';

      var overlay = document.createElement('div');
      overlay.className = 'contact-overlay';
      overlay.id = 'contactWidgetOverlay';
      overlay.innerHTML =
        '<div class="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contactWidgetTitle">'
        + '<div class="contact-modal-header">'
        + '<h2 class="contact-modal-title" id="contactWidgetTitle">Falar comigo</h2>'
        + '<button type="button" class="contact-close-btn" aria-label="Fechar formulario">x</button>'
        + '</div>'
        + '<form class="contact-form" id="contactWidgetForm">'
        + '<div class="contact-form-grid">'
        + '<label>Nome<span class="required-mark">*</span><input type="text" name="name" required maxlength="100"></label>'
        + '<label>Email<span class="required-mark">*</span><input type="email" name="email" class="contact-email-input" required maxlength="160"></label>'
        + '</div>'
        + '<div class="contact-form-grid">'
        + '<label>Telefone<input type="text" name="phone" maxlength="40"></label>'
        + '<label>Assunto<span class="required-mark">*</span><input type="text" name="subject" required maxlength="160"></label>'
        + '</div>'
        + '<label>Mensagem<span class="required-mark">*</span><textarea name="message" required maxlength="4000"></textarea></label>'
        + '<div class="contact-form-actions">'
        + '<div class="contact-status" id="contactWidgetStatus"></div>'
        + '<button type="submit" class="contact-submit-btn" id="contactWidgetSubmit">Enviar email</button>'
        + '</div>'
        + '</form>'
        + '</div>';

      document.body.appendChild(button);
      document.body.appendChild(overlay);

      var closeBtn = overlay.querySelector('.contact-close-btn');
      var form = document.getElementById('contactWidgetForm');
      var statusEl = document.getElementById('contactWidgetStatus');
      var submitBtn = document.getElementById('contactWidgetSubmit');
      var emailInput = form.querySelector('input[name="email"]');
      var lockedEmailValue = '';
      var sessionContactEmail = <?php echo json_encode($contactSessionEmail, JSON_UNESCAPED_UNICODE); ?>;

      function lockContactEmail(email) {
        if (!emailInput) {
          return;
        }

        var normalizedEmail = (email || '').toString().trim();
        if (!normalizedEmail) {
          return;
        }

        lockedEmailValue = normalizedEmail;
        emailInput.value = normalizedEmail;
        emailInput.readOnly = true;
        emailInput.classList.add('is-locked');
        emailInput.setAttribute('aria-readonly', 'true');
      }

      if (sessionContactEmail) {
        lockContactEmail(sessionContactEmail);
      }

      function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
      }

      button.addEventListener('click', openModal);
      closeBtn.addEventListener('click', closeModal);

      overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
          closeModal();
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
          closeModal();
        }
      });

      form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var formData = new FormData(form);
        var payload = {
          name: (formData.get('name') || '').toString().trim(),
          email: (formData.get('email') || '').toString().trim(),
          phone: (formData.get('phone') || '').toString().trim(),
          subject: (formData.get('subject') || '').toString().trim(),
          message: (formData.get('message') || '').toString().trim(),
        };

        statusEl.textContent = 'A enviar...';
        submitBtn.disabled = true;

        try {
          var response = await fetch('/contact_send.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
          });

          var result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Falha ao enviar mensagem.');
          }

          statusEl.textContent = 'Mensagem enviada com sucesso.';
          form.reset();

          if (lockedEmailValue) {
            emailInput.value = lockedEmailValue;
          }

          setTimeout(function () {
            closeModal();
            statusEl.textContent = '';
          }, 1000);
        } catch (error) {
          statusEl.textContent = error.message || 'Nao foi possivel enviar.';
        } finally {
          submitBtn.disabled = false;
        }
      });
    });
  </script>
</body>
</html>
