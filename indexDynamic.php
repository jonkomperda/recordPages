
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Bootstrap record page</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="recordpage.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><b>Jon's Records</b></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">7" Records</a></li>
            <li><a href="#">10" Records</a></li>
            <li><a href="#">12" Records</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li><a href="#">Anti-Flag </a></li>
            <li><a href="#">Frank Turner</a></li>
            <li class="active"><a href="#">NOFX <span class="sr-only">(current)</span></a></li>
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          
          <?php
            $rfile = fopen("nofx12")
            if($rfile){
                while (($line = fgets($rfile)) !== false){
                    echo $line
                }
            }
            else {
                echo "error\nerror\nerror\nerror\nerror\n"
            }
          ?>
          
          <h1 class="page-header">NOFX 12"</h1>
          
          <!--Begin a record-->
          <h3 class="sub-header">The Album</h3>
          <div class="row placeholders">
            <div class="col-xs-6 col-sm-3 record">
              <img src="./img/nofx/the_album_2.jpg" class="img-responsive" alt="Generic record thumbnail">
              <h4>2nd Press</h4>
              <span class="text-muted">Black</span>
            </div>
          </div>
          <!--End a record-->
          
          <!--Begin a record-->
          <h3 class="sub-header">Liberal Animation</h3>
          <div class="row placeholders">
            <div class="col-xs-6 col-sm-3 record">
              <img src="./img/nofx/la_4_b.jpg" class="img-responsive" alt="Generic record thumbnail">
              <h4>4th Press</h4>
              <span class="text-muted">Blue</span>
            </div>
            <div class="col-xs-6 col-sm-3 record">
              <img src="./img/nofx/la_4_y.jpg" class="img-responsive" alt="Generic record thumbnail">
              <h4>4th Press</h4>
              <span class="text-muted">Yellow</span>
            </div>
          </div>
          <!--End a record-->
          
          <!--Begin a record-->
            <h3 class="sub-header">S&M Airlines</h3>
            <div class="row placeholders">
              <div class="col-xs-6 col-sm-3 record">
                <img src="./img/nofx/sma_2_o.jpg" class="img-responsive" alt="Generic record thumbnail">
                <h4>2nd Press</h4>
                <span class="text-muted">Orange</span>
              </div>
          </div>
          <!--End a record-->
          
          <!--Begin a record-->
            <h3 class="sub-header">Ribbed</h3>
            <div class="row placeholders">
              <div class="col-xs-6 col-sm-3 record">
                <img src="./img/nofx/ribbed_2_r.jpg" class="img-responsive" alt="Generic record thumbnail">
                <h4>2nd Press</h4>
                <span class="text-muted">Clear Red</span>
              </div>
              <div class="col-xs-6 col-sm-3 record">
                <img src="./img/nofx/ribbed_3_r.jpg" class="img-responsive" alt="Generic record thumbnail">
                <h4>3rd Press</h4>
                <span class="text-muted">Opaque Red</span>
              </div>
              <div class="col-xs-6 col-sm-3 record">
                  <img src="./img/nofx/ribbed_3_c.jpg" class="img-responsive" alt="Generic record thumbnail">
                  <h4>3rd Press</h4>
                  <span class="text-muted">Clear</span>
              </div>
            </div>
            <!--End a record-->
            
            <!--Begin a record-->
                <h3 class="sub-header">White Trash, Two Heebs, and a Bean</h3>
                <div class="row placeholders">
                  <div class="col-xs-6 col-sm-3 record">
                    <img src="./img/nofx/wtthaab_2_w.jpg" class="img-responsive" alt="Generic record thumbnail">
                    <h4>2nd Press</h4>
                    <span class="text-muted">White</span>
                  </div>
                  <div class="col-xs-6 col-sm-3 record">
                    <img src="./img/nofx/wtthaab_3_o.jpg" class="img-responsive" alt="Generic record thumbnail">
                    <h4>3rd Press</h4>
                    <span class="text-muted">Orange</span>
                  </div>
                  <div class="col-xs-6 col-sm-3 record">
                      <img src="./img/nofx/wtthaab_3_p.jpg" class="img-responsive" alt="Generic record thumbnail">
                      <h4>3rd Press</h4>
                      <span class="text-muted">Clear Purple</span>
                  </div>
                  <div class="col-xs-6 col-sm-3 record">
                        <img src="./img/nofx/wtthaab_4_y.jpg" class="img-responsive" alt="Generic record thumbnail">
                        <h4>4th Press</h4>
                        <span class="text-muted">Yellow</span>
                    </div>
                    <div class="col-xs-6 col-sm-3 record">
                          <img src="./img/nofx/wtthaab_4_p.jpg" class="img-responsive" alt="Generic record thumbnail">
                          <h4>4th Press</h4>
                          <span class="text-muted">Opaque Purple</span>
                      </div>
                </div>
                <!--End a record-->
            
                <!--Begin a record-->
                    <h3 class="sub-header">Punk in Drublic</h3>
                    <div class="row placeholders">
                      <div class="col-xs-6 col-sm-3 record">
                        <img src="./img/nofx/pid_2_r.jpg" class="img-responsive" alt="Generic record thumbnail">
                        <h4>2nd Press</h4>
                        <span class="text-muted">Clear Red</span>
                      </div>
                      <div class="col-xs-6 col-sm-3 record">
                        <img src="./img/nofx/pid_3_g.jpg" class="img-responsive" alt="Generic record thumbnail">
                        <h4>3rd Press</h4>
                        <span class="text-muted">Green</span>
                      </div>
                      <div class="col-xs-6 col-sm-3 record">
                          <img src="./img/nofx/pid_4_p.jpg" class="img-responsive" alt="Generic record thumbnail">
                          <h4>4th Press</h4>
                          <span class="text-muted">Pink</span>
                      </div>
                      <div class="col-xs-6 col-sm-3 record">
                            <img src="./img/nofx/pid_4_g.jpg" class="img-responsive" alt="Generic record thumbnail">
                            <h4>4th Press</h4>
                            <span class="text-muted">Clear Green</span>
                        </div>
                    </div>
                    <!--End a record-->
          
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/docs.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
