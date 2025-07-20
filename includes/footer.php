<?php
// includes/footer.php
?>
</div> <!-- /.container -->

<footer class="mt-5 py-3 bg-light text-center text-muted">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div>
      &copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?>. All rights reserved.
    </div>
    <div>
      <a href="/privacy.php" class="text-muted me-3">Privacy Policy</a>
      <a href="/terms.php" class="text-muted">Terms & Conditions</a>
    </div>
  </div>
  <div class="mt-2">
    <button id="toggleTheme" class="btn btn-sm btn-outline-secondary">Toggle Dark Mode</button>
  </div>
</footer>

<!-- Bootstrap JS Bundle (Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/custom.js"></script>

<script>
  // Dark mode toggle
  const toggleBtn = document.getElementById('toggleTheme');
  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('bg-dark');
    document.body.classList.toggle('text-light');
  });
</script>

</body>
</html>
