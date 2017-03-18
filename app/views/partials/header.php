<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <title><?php echo __('global.manage'); ?> <?php echo _e(Config::meta('sitename')); ?></title>

  <link href="<?php echo asset('app/views/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo asset('app/views/assets/css/app.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('app/views/assets/css/bootstrap-markdown.min.css'); ?>">

  <link rel="stylesheet" href="<?php echo asset('app/views/assets/css/bootstrap-tagsinput.css'); ?>">

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js" integrity="sha384-qFIkRsVO/J5orlMvxK1sgAt2FXT67og+NyFTITYzvbIP1IJavVEKZM7YWczXkwpB" crossorigin="anonymous"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js" integrity="sha384-ZoaMbDF+4LeFxg6WdScQ9nnR1QC2MIRxA1O9KWEXQwns1G8UNyIEZIQidzb0T1fo" crossorigin="anonymous"></script>
  <![endif]-->
</head>
<body<?php echo onload(); $user = Auth::user(); ?>>
<div class="navbar navbar-inverse" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
      <span class="sr-only"><?php echo _e('site.toggle_navigation'); ?></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo Uri::to('admin/staffs'); ?>">

       <img height="28" alt="Jata Negara" src="<?php echo asset('app/views/assets/img/jpa-101pxx119px.png'); ?>">

       <span class="sr-only"><?php echo _e(Config::meta('sitename')); ?></span></a>
    </div>

    <?php if($user): ?>
    <div class="navbar-collapse collapse navbar-inverse-collapse">

    <?php
      $menu = array('staffs');
      $hierarchies = array('divisions', 'branchs', 'sectors', 'units', 'categories', 'tags');
      $reports_mostview = array('staff', 'category', 'division', 'search');
      $admin = array('users', 'pages', 'fields', 'variables', 'metadata');
    ?>

      <ul class="nav navbar-nav">

        <?php foreach($menu as $url): ?>
        <li <?php if(is_active('admin/' . $url)) echo 'class="active"'; ?>>
          <a href="<?php echo Uri::to('admin/' . $url); ?>"><?php echo __($url . '.' . $url); ?></a>
        </li>
        <?php endforeach; ?>


        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('global.hierarchies'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach($hierarchies as $url):
              // only admin have right to manage division
              if($url == 'divisions' && $user->role != 'administrator') continue; ?>
              <li <?php if(is_active('admin/' . $url)) echo 'class="active"'; ?>>
                <a href="<?php echo Uri::to('admin/' . $url); ?>">
                  <?php echo __('global.' . $url); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('reports.reports'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
          	<li <?php if(is_active('admin/reports')) echo 'class="active"'; ?>><a href="<?php echo Uri::to('admin/reports'); ?>">Dashboard</a></li>
            <?php foreach($reports_mostview as $url): ?>
              <li <?php if(is_active('admin/reports/' . $url)) echo 'class="active"'; ?>>
                <a href="<?php echo Uri::to('admin/reports/' . $url); ?>">
                  <?php echo __('global.' . $url); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li>
        <?php
        $pretend = Session::get('pretend') ? 'false' : 'true';

        $pretend_url = Session::get('division') ? 'division/' . Session::get('division') : null;
        ?>
        <?php if (is_null(Session::get('pretend'))) : ?>
        	<a class="alert-success" href="<?php echo pretend($pretend, $pretend_url); ?>" target="_blank"><span class="glyphicon glyphicon-eye-open"></span> <?php echo __('global.pretend'); ?></a>
    	<?php else: ?>
    		<a class="alert-warning" href="<?php echo pretend($pretend, $pretend_url); ?>"><span class="glyphicon glyphicon-eye-close"></span> <?php echo __('global.cancel_pretend'); ?></a>
    	<?php endif ?>
        </li>

      </ul>

      <ul class="nav navbar-nav navbar-right">
       <li><a class="alert-warning" href="<?php echo Uri::to('admin/logout'); ?>"><span class="glyphicon glyphicon-off"></span> <?php echo __('global.logout'); ?></a></li>
       <li><a href="<?php echo Uri::to('/'); ?>"><span class="glyphicon glyphicon-globe"></span> <?php echo __('global.view_directory'); ?></a></li>

       <li><a href="<?php echo Uri::to('admin/profile'); ?>"><span class="glyphicon glyphicon-user"></span> <?php echo __('users.profile'); ?></a></li>

       <?php if($user->role == 'administrator'): ?>
       <li class="dropdown">
         <a href="<?php echo Uri::to('admin/setting'); ?>" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span> <?php echo __('settings.setting'); ?> <b class="caret"></b></a>
         <ul class="dropdown-menu">

            <?php foreach($admin as $url): ?>
              <li <?php if(is_active('admin/setting/' . $url)) echo 'class="active"'; ?>>
                <a href="<?php echo Uri::to('admin/setting/' . $url); ?>"><?php echo __('settings.' . $url); ?></a>
              </li>
           <?php endforeach; ?>

         </ul>
       </li>
      <?php endif; ?>
      </ul>
    </div> <!-- //.navbar-collapse -->
    <?php endif; ?>

  </div> <!-- //.container -->
</div> <!-- //.navbar -->

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="well admin-container">
