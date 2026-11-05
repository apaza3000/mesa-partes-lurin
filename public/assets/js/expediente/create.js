(function () {
  const form = document.querySelector('#form-expediente');
  if (!form) return;
  const steps = [...form.querySelectorAll('.wizard-step')];
  const previous = document.querySelector('#wizard-prev');
  const next = document.querySelector('#wizard-next');
  const submit = document.querySelector('#wizard-submit');
  let current = 0;
  const showStep = index => {
    steps.forEach((step, i) => step.classList.toggle('d-none', i !== index));
    previous.classList.toggle('d-none', index === 0);
    next.classList.toggle('d-none', index === steps.length - 1);
    submit.classList.toggle('d-none', index !== steps.length - 1);
  };
  next.addEventListener('click', () => {
    const fields = [...steps[current].querySelectorAll('[required]')];
    if (fields.some(field => !field.checkValidity())) return;
    current++;
    showStep(current);
  });
  previous.addEventListener('click', () => { current--; showStep(current); });
  document.querySelector('#buscar-persona').addEventListener('click', async () => {
    const tipo = document.querySelector('#tipo-documento').value;
    const numero = document.querySelector('#numero-documento').value.trim();
    if (!numero) return Swal.fire('Atención', 'Ingrese el número de documento.', 'warning');
    const response = await fetch(`/personas/buscar?tipo_documento=${encodeURIComponent(tipo)}&numero_documento=${encodeURIComponent(numero)}`);
    const data = await response.json();
    if (!data.encontrado) return Swal.fire('No encontrado', data.mensaje || 'Puede registrar los datos manualmente.', 'info');
    document.querySelector('#id-remitente').value = data.persona.id_persona;
    for (const [key, value] of Object.entries({
      nombres: data.persona.nombres, apellido_paterno: data.persona.apellido_paterno,
      apellido_materno: data.persona.apellido_materno, email: data.persona.email,
      telefono: data.persona.telefono, direccion: data.persona.direccion
    })) {
      if (form.elements[key]) form.elements[key].value = value || '';
    }
  });
  document.querySelector('#archivos').addEventListener('change', event => {
    const allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    const preview = document.querySelector('#preview-archivos');
    preview.innerHTML = '';
    [...event.target.files].forEach(file => {
      const extension = file.name.split('.').pop().toLowerCase();
      const valid = allowed.includes(extension) && file.size <= 10 * 1024 * 1024;
      preview.insertAdjacentHTML('beforeend', `<div class="alert alert-${valid ? 'success' : 'danger'} py-1">${file.name}</div>`);
    });
  });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    submit.disabled = true;
    try {
      const response = await fetch('/expedientes', { method: 'POST', body: new FormData(form) });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'No se pudo registrar el expediente.');
      await Swal.fire('Registrado', data.expediente.codigo_expediente, 'success');
      location.href = `/expedientes/${data.expediente.id_expediente}`;
    } catch (error) {
      Swal.fire('Error', error.message, 'error');
    } finally {
      submit.disabled = false;
    }
  });
})();
