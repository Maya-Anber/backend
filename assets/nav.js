(function(){
  function renderNav(container){
    const baseLinks = [
      { href: '../list/index.php', label: 'Home' },
      { href: '../security/profile.php', label: 'My Profile', auth: true },
      { href: '../messages/conversations.html', label: 'Messages', auth: true },
      { href: '../exchange/requests.html', label: 'Exchange Requests', auth: true },
  { href: '../list/add-book.html', label: 'Add Book', auth: true },
      { href: '../security/login.html', label: 'Login', guest: true },
      { href: '../security/register.html', label: 'Register', guest: true },
      { href: '../security/logout.php', label: 'Logout', auth: true }
    ];

    // Determine current nesting to fix relative paths in different folders
    var here = (document.currentScript && document.currentScript.src) || '';
    var depth = 0;
    try {
      var path = window.location.pathname.replace(/\\+/g,'/');
      var idx = path.toLowerCase().indexOf('/backend/');
      if (idx >= 0) { path = path.slice(idx+1); }
      depth = (path.match(/\//g) || []).length - 1; // rough
    } catch(_){}

    function fix(href){
      if (!href) return '#';
      // keep absolute and protocol links untouched
      if (/^https?:/i.test(href)) return href;
      // If href already starts with ../ assume fine
      return href;
    }

    fetch('../security/session_info.php', { credentials:'same-origin' })
      .then(function(r){return r.ok?r.json():{logged_in:false}})
      .then(function(info){
        var logged = !!(info && info.logged_in);
        var ul = document.createElement('ul');
        ul.className = 'navbar-nav ms-auto';
        baseLinks.forEach(function(link){
          if ((link.auth && !logged) || (link.guest && logged)) return;
          var li = document.createElement('li'); li.className='nav-item';
          var a = document.createElement('a'); a.className='nav-link'; a.href = fix(link.href); a.textContent = link.label;
          li.appendChild(a);
          ul.appendChild(li);
        });
        container.innerHTML = '';
        container.appendChild(ul);
      })
      .catch(function(){
        // fallback minimal links
        container.innerHTML = '<ul class="navbar-nav ms-auto"><li class="nav-item"><a class="nav-link" href="../list/index.php">Home</a></li></ul>';
      });
  }

  // Look for nav placeholder
  document.addEventListener('DOMContentLoaded', function(){
    var placeholder = document.querySelector('[data-dyn-nav]');
    if (placeholder) renderNav(placeholder);
  });
})();
