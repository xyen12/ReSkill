</main>

    <!-- ======================================
         FOOTER WEBSITE
    ======================================= -->

    <footer class="site-footer">

        <!-- Informasi brand dan deskripsi -->
        <div>
            <a class="footer-brand" href="<?= e(url()) ?>">
                ♻ ReSkill
            </a>

            <p>
                Edukasi pengelolaan limbah untuk kehidupan
                yang lebih berkelanjutan.
            </p>
        </div>

        <!-- Informasi hak cipta -->
        <small>
            © <?= date('Y') ?> ReSkill
        </small>

    </footer>

    <!-- Memanggil file JavaScript -->
    <script src="<?= e(url('assets/js/app.js')) ?>"></script>

</body>
</html>
