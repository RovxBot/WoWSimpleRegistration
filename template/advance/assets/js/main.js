/* Grim Guzzler navigation, account accessibility, and connection helpers. */
(function () {
  'use strict';

  const toggle = document.querySelector('.menu-toggle');
  const navigation = document.querySelector('.site-navigation');
  const closeMenu = function (restoreFocus) {
    if (!toggle || !navigation) return;
    const wasOpen = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', 'false');
    navigation.classList.remove('is-open');
    if (restoreFocus && wasOpen) toggle.focus();
  };
  if (toggle && navigation) {
    toggle.addEventListener('click', function () {
      const isOpen = toggle.getAttribute('aria-expanded') !== 'true';
      toggle.setAttribute('aria-expanded', String(isOpen));
      navigation.classList.toggle('is-open', isOpen);
    });
    navigation.addEventListener('click', function (event) {
      if (event.target.closest('a, .language-toggle')) closeMenu(false);
    });
    document.addEventListener('click', function (event) {
      if (!event.target.closest('.site-header')) closeMenu(false);
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeMenu(true);
    });
    window.matchMedia('(min-width: 992px)').addEventListener('change', function (event) {
      if (event.matches) closeMenu(false);
    });
  }

  document.querySelectorAll('[data-copy-target]').forEach(function (button) {
    button.addEventListener('click', async function () {
      const target = document.getElementById(button.dataset.copyTarget);
      const feedback = document.getElementById('copy-feedback');
      if (!target) return;
      const command = target.textContent.trim();
      try {
        if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
        await navigator.clipboard.writeText(command);
        const label = button.querySelector('.copy-label');
        if (label) label.textContent = 'Copied';
        button.setAttribute('aria-label', 'Realmlist copied');
        if (feedback) feedback.textContent = 'Realmlist command copied to clipboard.';
        window.setTimeout(function () {
          if (label) label.textContent = 'Copy';
          button.setAttribute('aria-label', 'Copy realmlist');
        }, 2500);
      } catch (_) {
        const range = document.createRange();
        range.selectNodeContents(target);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        if (feedback) {
          feedback.classList.remove('sr-only');
          feedback.textContent = 'The realmlist command is selected. Use your device’s copy command to copy it.';
        }
      }
    });
  });

  // CAPTCHA and confirmation fields also come from shared backend helpers.
  document.querySelectorAll('input:not([type="hidden"]), select').forEach(function (input) {
    if (!input.labels.length && !input.hasAttribute('aria-label')) {
      input.setAttribute('aria-label', input.placeholder || input.name.replace(/_/g, ' '));
    }
  });

  // Translate legacy section links into the corresponding dedicated page.
  const legacyPages = { '#about': 'status', '#connect': 'play', '#register': 'register', '#player-portal': 'account', '#server-status': 'status', '#why-us': 'support', '#faq': 'support', '#contact': 'support' };
  const current = new URL(window.location.href);
  if (!current.searchParams.has('page') && legacyPages[current.hash]) {
    current.searchParams.set('page', legacyPages[current.hash]);
    window.location.replace(current.href);
  }
})();
