<?php
/**
 * AdminLTE 4 Footer Partial
 * @var array $config
 */
?>

</div>
</div>
</main>
<!-- /.main -->

<!-- Footer -->
<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Powered by <strong>OSPOS</strong>
    </div>
    <strong>
        <?= lang('Common.copyrights', [date('Y')]) ?> ·
        <span>
            <?= esc($config['company']) ?>
        </span>
    </strong>
</footer>
<!-- /.footer -->
</div>
<!-- ./wrapper -->

<!-- AdminLTE JavaScript -->
<script src="resources/adminlte/js/bootstrap.bundle.min.js"></script>
<script src="resources/adminlte/js/adminlte.min.js"></script>
<script src="resources/adminlte/js/sweetalert2.all.min.js"></script>

<script>
    // Live clock update
    function updateClock() {
        const now = new Date();
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: <?= ($config['timeformat'] == 'H:i:s' || $config['timeformat'] == 'H:i') ? 'false' : 'true' ?>
            };
        document.getElementById('liveclock').textContent = now.toLocaleString('<?= current_language_code() ?>', options);
    }
    setInterval(updateClock, 1000);

    // Replace BootstrapDialog with SweetAlert2
    window.BootstrapDialog = {
        confirm: function (options) {
            Swal.fire({
                title: options.title || 'Confirm',
                text: options.message || '',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: options.btnOKLabel || 'OK',
                cancelButtonText: options.btnCancelLabel || 'Cancel'
            }).then((result) => {
                if (result.isConfirmed && options.callback) {
                    options.callback(true);
                } else if (options.callback) {
                    options.callback(false);
                }
            });
        },
        show: function (options) {
            Swal.fire({
                title: options.title || '',
                html: options.message || '',
                icon: options.type === 'danger' ? 'error' : (options.type || 'info')
            });
        }
    };
</script>
</body>

</html>