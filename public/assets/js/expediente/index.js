(function () {
  if (window.jQuery && window.jQuery.fn.DataTable && document.querySelector('#tabla-expedientes')) {
    window.jQuery('#tabla-expedientes').DataTable({
      order: [],
      language: { url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' }
    });
  }
})();
