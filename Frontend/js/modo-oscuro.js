// ===== Modo oscuro (un solo botón para todo el sitio) =====
(function () {
  const CLAVE = 'ccm_tema';
  const root = document.documentElement;

  function leerGuardado() {
    try { return localStorage.getItem(CLAVE); } catch (e) { return null; }
  }
  function guardar(tema) {
    try { localStorage.setItem(CLAVE, tema); } catch (e) {}
  }

  const sistemaOscuro = window.matchMedia &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;

  let tema = leerGuardado() || (sistemaOscuro ? 'oscuro' : 'claro');
  let boton = null;

  function aplicar() {
    if (tema === 'oscuro') root.setAttribute('data-tema', 'oscuro');
    else root.removeAttribute('data-tema');
    actualizarBoton();
  }

  function actualizarBoton() {
    if (!boton) return;
    const oscuro = tema === 'oscuro';
    boton.innerHTML = oscuro
      ? '<i class="bi bi-sun-fill" aria-hidden="true"></i>'
      : '<i class="bi bi-moon-fill" aria-hidden="true"></i>';
    boton.setAttribute('aria-pressed', String(oscuro));
    boton.setAttribute('aria-label', oscuro ? 'Activar modo claro' : 'Activar modo oscuro');
    boton.title = oscuro ? 'Modo claro' : 'Modo oscuro';
  }

  function alternar() {
    root.classList.add('tema-anim');
    tema = tema === 'oscuro' ? 'claro' : 'oscuro';
    guardar(tema);
    aplicar();
    setTimeout(() => root.classList.remove('tema-anim'), 350);
  }

  function crearBoton() {
    if (document.querySelector('.btn-tema')) return;
    if (!document.querySelector('link[data-bootstrap-icons]')) {
      const iconos = document.createElement('link');
      iconos.rel = 'stylesheet';
      iconos.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
      iconos.setAttribute('data-bootstrap-icons', '');
      document.head.appendChild(iconos);
    }
    boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'btn-tema';
    boton.style.cssText =
      'position:fixed;left:20px;bottom:20px;width:46px;height:46px;border-radius:50%;' +
      'background:#0d47a1;color:#fff;border:2px solid #90caf9;display:flex;' +
      'align-items:center;justify-content:center;font-size:1.3rem;line-height:1;' +
      'cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.35);z-index:2147483647;';
    boton.addEventListener('click', alternar);
    document.body.appendChild(boton);
    actualizarBoton();
  }

  aplicar();

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', crearBoton);
  } else {
    crearBoton();
  }

  window.addEventListener('storage', (e) => {
    if (e.key === CLAVE && e.newValue && e.newValue !== tema) {
      tema = e.newValue;
      aplicar();
    }
  });
})();