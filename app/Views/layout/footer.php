        </main>

<?php if (\App\Core\Auth::user()): ?>
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 text-center text-sm text-gray-500 py-3 px-4">
            <?= __('app.footer') ?>
            <div style="margin-top: 0.25rem; font-size: 0.75rem; color: #9ca3af;">
                Powered by <a href="https://muzamna.com" target="_blank" rel="noopener" style="color: #6b7280; text-decoration: underline;">Muzamna</a>
            </div>
        </footer>
    </div><!-- end main wrapper -->

    <script>
    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var isRtl = document.documentElement.getAttribute('dir') === 'rtl';
        var hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
        var isOpen = !sidebar.classList.contains(hideClass);
        if (isOpen) {
            sidebar.classList.add(hideClass);
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove(hideClass);
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    // Close export dropdowns on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown > div:not(.hidden)').forEach(function(menu) {
                if (!menu.classList.contains('hidden')) menu.classList.add('hidden');
            });
        }
    });
    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        var isRtl = document.documentElement.getAttribute('dir') === 'rtl';
        var hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
        if (window.innerWidth >= 1024) {
            document.getElementById('sidebar').classList.remove(hideClass);
            document.getElementById('sidebar-overlay').classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            document.getElementById('sidebar').classList.add(hideClass);
        }
    });
    // Auto-dismiss flash messages after 4 seconds
    document.querySelectorAll('[data-flash]').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s, max-height 0.5s, margin 0.3s, padding 0.3s';
            el.style.opacity = '0';
            el.style.maxHeight = '0';
            el.style.overflow = 'hidden';
            el.style.marginBottom = '0';
            el.style.paddingTop = '0';
            el.style.paddingBottom = '0';
            setTimeout(function() { el.remove(); }, 500);
        }, 4000);
    });
    // Prevent double-submit on forms
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.style.opacity = '0.6'; }
            setTimeout(function() { if (btn) { btn.disabled = false; btn.style.opacity = ''; } }, 5000);
        });
    });
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js');
    }
    </script>
<?php else: ?>
    </main>
<?php endif; ?>
</body>
</html>
