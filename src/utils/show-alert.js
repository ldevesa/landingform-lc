import Swal from 'sweetalert2';

export function initContactForm() {
  const form = document.getElementById("contact-form");
  if (!form) return;

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Enviando...';

    try {
      const recaptchaSiteKey = "TU_CLAVE_RECAPTCHA";
      const token = await grecaptcha.execute(recaptchaSiteKey, {
        action: "submit",
      });

      if (!token) {
        Swal.fire({
          icon: 'error',
          title: 'Error reCAPTCHA',
          text: 'Verificá que no sos un robot.',
        });
        return;
      }

      const formData = new FormData(form);
      formData.append("recaptcha-token", token);

      const response = await fetch("/landingform/server/send-email.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Enviado!',
          text: 'Gracias por contactarnos.',
        });
        form.reset();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message || 'No se pudo enviar.',
        });
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error inesperado',
        text: 'Intentalo más tarde.',
      });
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = 'Enviar';
    }
  });
}
