<?php if (is_logged_in()): ?>
    </div><!-- /.page-body-wrapper -->
  </div><!-- /.container-scroller -->
<?php else: ?>
  </div><!-- /.auth-wrapper -->
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('public/js/app.js') ?>"></script>
</body>
</html>
