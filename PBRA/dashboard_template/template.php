<?php
// Start session and output buffering BEFORE any HTML/output so included components can send headers safely
session_start();
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Website with Components</title>
    <link rel="stylesheet" href="../page_title.css" />
</head>

<body>
    <!-- Page Title -->
    <div class="page-title">
        <h1 style="font-size: 30px;">TEMPLATE</h1>
    </div>

    <?php include '../navbar/navbar.php'; ?>
    <div id="content" class="content">
        <h1>Welcome to My PHP Website, This is a Template area and still work in progress.</h1>
        <p>Main content...
            Lorem ipsum dolor, sit amet consectetur adipisicing elit. Rerum architecto voluptatem laborum tempora illum ipsa iste eveniet nisi incidunt. Sunt non laudantium aliquam inventore iure, quae unde cum cupiditate atque.</p>
        <p style="height: 2000px;">Keep scrolling...</p>
    </div>
    <?php include '../scrolltop/scrolltop.php'; ?>
    <?php include '../footer/footer.php'; ?>
    <script src="../scrolltop/scrolltop.js"></script>

    <?php
    // Flush output buffer at end so headers from included files are sent properly
    ob_end_flush();
    ?>
</body>

</html>