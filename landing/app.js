(() => {
  const root = document.documentElement;
  const btn = document.getElementById('lang');
  const titles = {
    ar: 'نوڤيكسا | Novixa — هندسة البرمجيات والمنتجات الرقمية',
    en: 'Novixa | Software engineering & digital products',
  };
  const read = () => { try { return localStorage.getItem('novixa_landing_lang'); } catch (_) { return null; } };
  const write = (v) => { try { localStorage.setItem('novixa_landing_lang', v); } catch (_) { /* storage unavailable */ } };

  function apply(lang) {
    root.lang = lang;
    root.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.title = titles[lang];
    btn.textContent = lang === 'ar' ? 'EN' : 'عربي';
    write(lang);
  }

  const fromUrl = new URLSearchParams(location.search).get('lang');
  apply(['ar', 'en'].includes(fromUrl) ? fromUrl : (read() || 'ar'));
  btn.addEventListener('click', () => apply(root.lang === 'ar' ? 'en' : 'ar'));
})();
