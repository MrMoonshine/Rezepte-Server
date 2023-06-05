<!DOCTYPE html>
<html lang="">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>Alpakaküche-Config</title>
  </head>
  <body>
    <style>
      /*Nav bar icon size */
      a>img{
          max-width: 48px !important;
          max-height: 48px !important;
      }
    </style>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary d-flex d-print-none">
      <a class="navbar-brand" href="#"><img src="/favicon.ico" alt="">Alpakaküche-Config</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
          data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent"
          aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarToggleExternalContent">
          <div class="navbar-nav">
              <a class="nav-item nav-link active" href="/rezepte">Rezepte </a>
              <a class="nav-item nav-link" href="#">Config</a>
              <a class="nav-item nav-link" href="/">Home</a>
          </div>
      </div>
    </nav>
    <section class="container">
      <?php
        # Enable Logging of errors:
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        require("database.php");
        $db = new Database();
      ?>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
  </body>
</html>
