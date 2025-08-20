<?php
// User Profile page: shows basic info and ratings received
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/assets/theme.css">
    <style>
        .star { color:#ffc107 }
    </style>
    <meta name="robots" content="noindex">
</head>
<body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="../list/index.php">📚 Book Exchange</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav" data-dyn-nav></div>
            </div>
        </nav>

    <div class="container my-4">
        <?php if (!$logged_in): ?>
            <div class="alert alert-warning">You must login first. <a href="login.php" class="alert-link">Login</a></div>
        <?php else: ?>
            <h1 class="mb-2">My Profile</h1>
            <p class="text-muted">Welcome, <strong><?php echo htmlspecialchars($username ?? 'User'); ?></strong></p>

                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <section class="card p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Messages</h5>
                                    <a class="btn btn-sm btn-outline-primary" href="../messages/conversations.html">Open inbox</a>
                                </div>
                                <div id="messagesPreview" class="mt-3 small text-muted">Loading…</div>
                            </section>

                            <section class="card p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">My requests (as requester)</h5>
                                    <a class="btn btn-sm btn-outline-primary" href="../exchange/requests.html">View all</a>
                                </div>
                                <div id="reqRequester" class="mt-3 small text-muted">Loading…</div>
                            </section>

                            <section class="card p-3">
                                <h5>My ratings</h5>
                                <div id="alert" class="alert d-none" role="alert"></div>
                                <div id="summary" class="mb-3"></div>
                                <div id="reviews"></div>
                            </section>
                        </div>

                        <div class="col-12 col-lg-4">
                            <section class="card p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Requests I received (as owner)</h6>
                                    <a class="btn btn-sm btn-outline-secondary" href="../exchange/requests.html">Manage</a>
                                </div>
                                <div id="reqOwner" class="mt-3 small text-muted">Loading…</div>
                            </section>

                            <section class="card p-3">
                                <h6>Shortcuts</h6>
                                <div class="list-group">
                                    <a class="list-group-item list-group-item-action" href="../list/index.php">My listings</a>
                                    <a class="list-group-item list-group-item-action" href="../exchange/requests.html">My requests</a>
                                    <a class="list-group-item list-group-item-action" href="../messages/conversations.html">Messages</a>
                                </div>
                            </section>
                        </div>
                    </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">© 2025 Book Exchange</footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/nav.js"></script>
    <?php if ($logged_in): ?>
    <script>
        function banner(msg,type='info'){ const el=document.getElementById('alert'); el.className='alert alert-'+type; el.textContent=msg; }
        function clearBanner(){ const el=document.getElementById('alert'); el.className='alert d-none'; el.textContent=''; }
        function stars(n){ n = Math.max(0, Math.min(5, Number(n)||0)); return '★★★★★☆☆☆☆☆'.slice(5-n, 10-n).replace(/\*/g,'').replace(/./g, c => c==='★'?'<span class="star">★</span>':'<span class="text-muted">☆</span>'); }

        function loadMyRatings(){
            clearBanner();
            fetch('../ratings/get_user_ratings.php', { credentials:'same-origin' })
                .then(r=>{ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
                .then(res=>{
                    if(!res || !res.success) { return banner((res && (res.message||res.error)) || 'Failed to load ratings', 'danger'); }
                    const { average, count, reviews } = res;
                    const sum = document.getElementById('summary');
                    sum.innerHTML = `<div class="d-flex align-items-center gap-3">`
                                                + `<div style="font-size:26px">${average || 0} / 5</div>`
                                                + `<div>${stars(Math.round(average||0))}</div>`
                                                + `<div class="text-muted">${count} review(s)</div>`
                                                + `</div>`;
                    const list = document.getElementById('reviews');
                    list.innerHTML = '';
                    if(!Array.isArray(reviews) || reviews.length === 0){
                        list.innerHTML = '<p class="text-muted">No reviews yet.</p>';
                        return;
                    }
                    const ul = document.createElement('ul'); ul.className='list-group';
                    reviews.forEach(r => {
                        const li = document.createElement('li'); li.className='list-group-item';
                        li.innerHTML = `<div class="d-flex justify-content-between">`
                                                 + `<div><strong>${r.rater_name || 'User '+(r.rater_id||'')}</strong> • <span class="text-muted small">${r.rating_date||''}</span></div>`
                                                 + `<div>${stars(r.rating)}</div>`
                                                 + `</div>`
                                                 + (r.review_text ? `<div class="mt-2">${r.review_text}</div>` : '');
                        ul.appendChild(li);
                    });
                    list.appendChild(ul);
                })
                .catch(err=> banner('Failed to load: '+err.message, 'danger'));
        }

            function shortList(items, mapFn, emptyText){
                if (!Array.isArray(items) || items.length===0) return `<div class="text-muted">${emptyText}</div>`;
                const top = items.slice(0,3);
                return '<ul class="list-group">' + top.map(mapFn).join('') + '</ul>';
            }

            function loadMessagesPreview(){
                const el = document.getElementById('messagesPreview');
                fetch('../messages/list_conversations.php', { credentials:'same-origin' })
                    .then(r=>r.ok?r.json():[]).then(list=>{
                        el.innerHTML = shortList(list, c => {
                            const title = c.listing_title || 'Request #' + c.request_id;
                            const badge = (c.unread_count>0) ? `<span class="badge bg-danger ms-2">${c.unread_count}</span>` : '';
                            return `<li class="list-group-item d-flex justify-content-between align-items-center">`
                                     + `<a href="../messages/chat.html?exchange_request_id=${encodeURIComponent(c.request_id)}">${title}</a>`
                                     + `<span class="text-muted small">${c.last_time||''}${badge}</span>`
                                     + `</li>`;
                        }, 'No conversations.');
                    }).catch(()=>{ el.textContent = 'Failed to load.'; });
            }

            function loadRequests(role, targetId){
                const el = document.getElementById(targetId);
                fetch(`../exchange/my_requests.php?role=${encodeURIComponent(role)}`, { credentials:'same-origin' })
                    .then(r=>r.ok?r.json():[]).then(list=>{
                        el.innerHTML = shortList(list, r => {
                            const status = (r.status||'').toLowerCase();
                            const badge = `<span class="badge bg-secondary text-capitalize">${status}</span>`;
                            const title = r.listing_title || ('Request #'+r.request_id);
                            const link = `../messages/chat.html?exchange_request_id=${encodeURIComponent(r.request_id)}`;
                            return `<li class="list-group-item d-flex justify-content-between align-items-center">`
                                     + `<a href="${link}">${title}</a>`
                                     + `<span class="small">${badge}</span>`
                                     + `</li>`;
                        }, 'No requests.');
                    }).catch(()=>{ el.textContent = 'Failed to load.'; });
            }

            loadMyRatings();
            loadMessagesPreview();
            loadRequests('requester', 'reqRequester');
            loadRequests('owner', 'reqOwner');
    </script>
    <?php endif; ?>
</body>
</html>