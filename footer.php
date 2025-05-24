<?php
?>
    <footer class="bg-light py-5">
        <div class="container px-4 px-lg-5">
            <div class="small text-center text-muted">Copyright &copy; <?php echo date('Y'); ?> - Lampung Walk</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <?php if(isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>