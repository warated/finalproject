<!--
    TODO This is just a sample page to ensure the environment is working. 
    Replace with your own fantastic code!
-->
<!doctype html>
<html lang="en">

<head>
    <title>SC-BED Environment Test</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body data-bs-theme="dark">
    <div class="container d-flex flex-column">
        <div class="d-flex justify-content-center" style="margin-bottom: 20px;">
            <h1>Environment Test</h1>
        </div>
        <div class="d-flex justify-content-center">
            <img src="assets/php.png" alt="PHP Logo" width="200px"><br>
        </div>
        <div class="d-flex justify-content-center" style="margin-bottom: 20px;">
            <?php 
                printf('✅ This environment is running PHP version %s.', phpversion()); 
            ?>
        </div>
        <div class="d-flex justify-content-center">
            <img src="assets/mariadb.png" alt="MariaDB Logo" width="200px"><br>
        </div>
        <div class="d-flex justify-content-center" style="margin-bottom: 50px;">
            <?php 
                try {
                    $dbh = new PDO("mysql:host=mariadb;dbname=TestDB", "root", "root");
                    echo '✅ Successfully established connection to MariaDB.';
                } catch (PDOException $e) {
                    echo '<p>❌ Connection to MariaDB Failed : <small>' . $e->getMessage() . '</small></p>';
                } 
            ?>
        </div>
        <div class="d-flex justify-content-center">
                <p><strong>If you don't see any errors above, you're all set!</strong></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>