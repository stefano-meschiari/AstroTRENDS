<!DOCTYPE html>
<html>
  <head>
    <title>AstroTRENDS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="libs/select2/select2.css" rel="stylesheet">
    <link href="css/trends.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-47960829-1', 'stefanom.org');
  ga('send', 'pageview');
    </script>
  </head>
  <body>
    <!-- WIP
    <nav class="navbar navbar-default" role="navigation">
      <div class="navbar-header">
        <a class="navbar-brand">AstroMINES</a>
      </div>
      <div class="collapse navbar-collapse"> 
        <ul class="nav navbar-nav navbar-right">
          <li class="active"><a href="keywords.php">AstroTrends</a></li>
        </ul>
      </div>
    </nav>
    -->
    <div class="container">
      <div class="page-header">
        <h1 style="display:inline">AstroTRENDS</h1><a href="http://www.stefanom.org"><img src="../img/stefano-head.jpg" class="pull-right" style="margin-left:10px"></a>
        <p class="lead">Shows trends in the astronomy literature.  <a href="http://www.stefanom.org/astrotrends-a-new-tool-to-track-astronomy-topics-in-the-literature/" target="_blank">Read more...</a><span class="pull-right">Created by <a href="http://www.stefanom.org">Stefano Meschiari</a>.</span></p>
      </div>

      <div class="row">
        <div class="col-md-7" id="plot-container">
          <div id="plot">
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-success" id="keyword1-prog" style="width: 20%">
            </div>
            <div class="progress-bar progress-bar-warning" id="keyword2-prog" style="width: 20%">
            </div>
            <div class="progress-bar progress-bar-danger" id="keyword3-prog" style="width: 20%">
            </div>
            <div class="progress-bar progress-bar-info" id="keyword4-prog" style="width: 20%">
            </div>
            <div class="progress-bar" id="keyword5-prog" style="width: 20%">
            </div>
          </div>


          <form class="form-inline" id="plot-options">
            <div class="form-group">
              <button id="normalize" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Normalize by the first keyword"><span class="glyphicon glyphicon-sort-by-attributes"></span><span class="caret"></button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(0); return false;">No normalization</a></li>
                  <li class="divider"></li>
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(5); return false;">Normalize by total articles count</a></li>
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(1); return false;">Normalize by 1st keyword</a></li>
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(2); return false;">Normalize by 2nd keyword</a></li>
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(3); return false;">Normalize by 3rd keyword</a></li>
                  <li><a href="#" onClick="ASTRO_TRENDS_UI.normalize(4); return false;">Normalize by 2nd keyword</a></li>
                </ul>
          <button id="log" class="btn btn-default" type="button">Log</button>
            </div>
            <div class="form-group" style="float:right">
              <div class="input-group input-group-sm" style="width:400px">
                <span class="input-group-addon"> Share this:</span>
                  
                <input id="share" class="form-control">
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-5" id="plot-aside">
          <form role="form" id="params">
            <div class="form-group">
              <div class="row">
                <div class="col-md-5">
                  <label for="year1">From:</label>
                  <input id="year1" class="form-control" placeholder="Between this year..." value="1970">
                </div>
                <div class="col-md-5">
                  <label for="year2">To:</label>
                  <input id="year2" class="form-control" placeholder="to this year." value="2013">
                </div>
              </div>
            </div>
            <hr>
            <div class="form-group">
              <label for="keyword1">Keyword: <span class="badge" id="keyword1-badge"></span></label>
              <a id="keyword1-close" href="#" class="glyphicon glyphicon-remove-circle pull-right"></a>
              <select id="keyword1" class="full-width" placeholder="Keyword 1"></select>
            </div>
            <div class="form-group">
              <label for="keyword2">Keyword: <span class="badge" id="keyword2-badge"></span></label>
              <a id="keyword2-close" href="#" class="glyphicon glyphicon-remove-circle pull-right"></a>
              <select id="keyword2" class="full-width" placeholder="Keyword 2"></select>
            </div>
            <div class="form-group">
              <label for="keyword3">Keyword: <span class="badge" id="keyword3-badge"></span></label>
              <a id="keyword3-close" href="#" class="glyphicon glyphicon-remove-circle pull-right"></a>              
              <select id="keyword3" class="full-width" placeholder="Keyword 3"></select>
            </div>
            <div class="form-group">
              <label for="keyword4">Keyword: <span class="badge" id="keyword4-badge"></span></label>
              <a id="keyword4-close" href="#" class="glyphicon glyphicon-remove-circle pull-right"></a>              
              <select id="keyword4" class="full-width" placeholder="Keyword 4"></select>
            </div>
            <div class="form-group">
              <input id="show-all" type="checkbox"> <label for="show-all">Show total article counts</label>
              <input id="keyword5" value='"*"' type="hidden">
            </div>
            <hr>
            <div class="btn-toolbar" role="toolbar">
              
              <button id="inspire" type="button" class="btn btn-primary" title="Selects random keywords"><span class="glyphicon glyphicon-random"></span>&nbsp; Shuffle!</button>
              <button id="suggest" type="button" class="btn btn-default pull-right" title="Suggest new keywords"><span class="glyphicon glyphicon-comment"></span>&nbsp; Suggest a keyword or correction</button>
            </div>
          </form>

        </div>
      </div>
      <hr>


      <a name="more"></a>
      <div class="panel panel-default">
        <div class="panel-heading">About AstroTRENDS</div>
        <div class="panel-body">
          <p>
          This interactive visualization shows the number of refereed Astronomy articles published each year, containing the selected keywords in their abstracts.

          </p>
          <ul>
            <li>Click on a point to see the ADS results for that keyword.</li>
            <li>Click "Shuffle!" to view a random selection of astronomical keywords.</li>
            <li>Click the <span class="glyphicon glyphicon-sort-by-attributes"></span> icon to normalize trends.</li>
            <li>Click "Log" to toggle linear/log scale on the y-axis.</li>
            <li>Copy the contents of the "Share this" box to share a link to the current visualization.</li>
            <li>Click the <span class="glyphicon glyphicon-align-justify"></span> icon to export the plot to an image or PDF file.</li>
          </ul>
          <p><strong>This tool is for entertainment purposes only. No pretense of accuracy is implied!</strong></p>
          <p><a href="#">Back to top</a></p>
          
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery.js" type="text/javascript"></script>
    <script src="libs/underscore/underscore-min.js" type="text/javascript"></script>
    <script src="libs/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="libs/highcharts/js/highcharts.js" type="text/javascript"></script>
    <script src="libs/highcharts/js/modules/exporting.js" type="text/javascript"></script>
    <script src="libs/select2/select2.min.js" type="text/javascript"></script>
    <script src="js/keywords.full.js?v=1.4" type="text/javascript"></script>

    
  </body>
</html>
