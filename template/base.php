<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

        <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>


        <link rel="stylesheet" href="./css/main.css">

        <title><?php echo $templateParams["tabTitle"]; ?></title>

    </head>
    <body>
        <header>
        </header>
        <nav class="navbar fixed-top navbar-dark bg-primary navbar-expand">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.html">
                    <img src="./img/logoMartini.png" alt="" width="30" height="24" class="d-inline-block align-text-top">
                    BeachService
                </a>

                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="report.php">Report</a></li>
                    <li class="nav-item"><a class="nav-link active" href="periods.php">Tariffe</a></li>
                </ul>
            </div>
        </nav>
        <main>
            <main>
                <?php
                    if(isset($templateParams["pageURL"])) {
                        require($templateParams["pageURL"]);
                    }
                 ?>
            </main>
        </main>
    </body>
</html>
