<footer class="footer border-top py-3 mt-auto mb-0 bg-white">
    <div class="container d-flex flex-wrap justify-content-start gap-2 small text-secondary">
        <span>Swimming Up - @yield('title', '') {{ isset($swimmer['first_name']) ? '| '.$swimmer['first_name'] : '' }}</span>
    </div>
</footer>
