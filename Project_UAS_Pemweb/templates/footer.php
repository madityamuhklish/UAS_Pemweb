</div>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php include __DIR__ . "/chatbot-widget.php"; ?>
    <link rel="stylesheet" href="../assets/css/chatbot.css?v=20260710a">
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($needsChart)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<script src="../assets/js/app.js?v=20260710a"></script>

</body>

</html>