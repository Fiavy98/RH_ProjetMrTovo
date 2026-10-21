/* SIRH : comportements communs du layout (menu replié, menu mobile, groupes de menu, rôle de démonstration, messages). */
(function () {
  'use strict';

  var layout = document.getElementById('app-layout');
  var sidebar = document.getElementById('sidebar');
  var mask = document.getElementById('mobile-mask');
  var STORE = 'sirh_menu_replie';

  // Menu latéral replié : état mémorisé dans ce navigateur
  try { if (localStorage.getItem(STORE) === '1') layout.classList.add('compact'); } catch (e) {}

  document.addEventListener('click', function (event) {
    var group = event.target.closest('[data-group]');
    if (group) {
      var items = group.parentElement.querySelector('.nav-items');
      if (items) items.hidden = !items.hidden;
      return;
    }

    var button = event.target.closest('[data-action]');
    if (!button) return;

    switch (button.dataset.action) {
      case 'collapse':
        layout.classList.toggle('compact');
        try { localStorage.setItem(STORE, layout.classList.contains('compact') ? '1' : '0'); } catch (e) {}
        break;
      case 'mobile-toggle':
        sidebar.classList.toggle('mobile-open');
        mask.classList.toggle('show');
        break;
      case 'mobile-close':
        sidebar.classList.remove('mobile-open');
        mask.classList.remove('show');
        break;
    }
  });

  // Changement de rôle de démonstration : envoi automatique du petit formulaire
  document.addEventListener('change', function (event) {
    if (event.target.matches('[data-role-select]')) event.target.form.submit();
  });

  // Les messages (toasts) disparaissent seuls
  document.querySelectorAll('.toast-host .toast').forEach(function (toast) {
    setTimeout(function () { toast.classList.add('fade'); }, 4000);
    setTimeout(function () { toast.remove(); }, 4400);
  });
})();
