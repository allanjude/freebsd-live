<?php
require_once("scaleengine.php");

$arr_streams = json_decode(file_get_contents("streams.json"), true);
$arr_secrets = json_decode(file_get_contents("secrets.json"), true);

$arr_uri = explode('/', $_SERVER['REQUEST_URI']);
foreach ($arr_uri as $key => $val) {
	if ($val === "") {
		unset($arr_uri[$key]);
	}
}
$arr_uri = array_values($arr_uri);
$selected_conf = "";
if (isset($arr_uri[0])) {
	$selected_conf = $arr_uri[0];
}

?>
<!DOCTYPE html>
<html lang="en" class="js csstransforms3d">
  <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="description" content="">
	<link rel="shortcut icon" href="/images/favicon.png" type="image/x-icon" />
	<link rel="icon" href="/images/favicon.png" type="image/x-icon" />
	<title>FreeBSD Live Streaming</title>
	
	<link href="/css/nucleus.css" rel="stylesheet">
	<link href="/css/font-awesome.min.css" rel="stylesheet">
	<link href="/css/hybrid.css" rel="stylesheet">
	<link href="/css/featherlight.min.css" rel="stylesheet">
	<link href="/css/perfect-scrollbar.min.css" rel="stylesheet">
	<link href="/css/auto-complete.css" rel="stylesheet">
	<link href="/css/theme.css" rel="stylesheet">
	<link href="/css/hugo-theme.css" rel="stylesheet">
	<link href="/css/theme-red.css" rel="stylesheet">
	<script src="/js/jquery-2.x.min.js"></script>
	<style type="text/css">
		:root #header + #content > #left > #rlblock_left
		{ 
			display:none !important;
		}
	</style>
	<script type="text/javascript">
	    
		var baseurl = '\/';
	    
	</script>
    
	<link href="/css/papers-custom.css" rel="stylesheet">
	<link href="/live-custom.css" rel="stylesheet">

  </head>
  <body class="" data-url="<?=$_SERVER['REQUEST_URI']?>">
	<nav id="sidebar" class="">
		<div id="header-wrapper">
			<div id="header">
				<a id="logo" href="https://live.freebsd.org/">
					<img src="https://www.freebsd.org/logo/logo-full.png">
				</a>
			</div>
		</div>
		<div class="highlightable">
			<ul class="topics">

			<?php foreach ($arr_streams as $conf => $arr_conf): ?>

			<li data-nav-id="/<?=$conf?>/" title="<?=$conf?>" class="dd-item parent">
				<a href="/<?=$conf?>/">

					<?php if ($selected_conf == $conf):?>
					<i class="fa fa-angle-double-down"></i>
					<?php else:?>
					<i class="fa fa-angle-double-right"></i>
					<?php endif;?>

					<?=$conf?>

				</a>
				<ul class="ancestor-range-pages">

				<?php
				$arr_all_streams = array();
				switch ($arr_conf['type']) {
					case "officehours":
					case "scaleengine":
						$error = NULL;
						$secrets = $arr_secrets[$conf];
						$keys = "{$secrets['cdn_id']}:{$secrets['api_key']}";
						$arr_all_streams = seapi($keys, "stream_metadata", "GET", NULL, $error);
						foreach ((array)$arr_all_streams as $stream =>  $arr_details) {
							if ($stream == "freebsdday") {
								unset($arr_all_streams[$stream]);
							}
							if ($arr_details and (isset($arr_details['type']) and $arr_details['type'] != "origin")) {
								/* Skip transcodes and other non-origin streams */
								unset($arr_all_streams[$stream]);
							}
						}
						break;
					case "youtube":
						$arr_all_streams = $arr_conf['urls'];
						break;
					case "link":
						$arr_all_streams = $arr_conf['urls'];
						break;
				}
				if ($arr_all_streams):
					foreach ($arr_all_streams as $stream => $arr_details):?>

					<li data-nav-id="/<?=$conf?>/<?=$stream?>/" title="<?=$conf?> - <?=$stream?>" class="dd-item ">
						<a href="/<?=$conf?>/<?=$stream?>/">
							<i class="fa fa-angle-right papers-menu-item"></i>
							 <?=$stream?>
						</a>
					</li>

					<?php endforeach; /* stream */ ?>
				<?php endif; ?>

				</ul>
			</li>

			<?php endforeach; /* conference */ ?>

			</ul>

			<ul class="topics">
				<li class="dd-item">
					&nbsp;
				</li>
				<li class="dd-item">
					&nbsp;
				</li>
			</ul>

			<section id="footer">
			</section>
		</div>
	</nav>


        <section id="body">
          <div class="padding highlightable">
		<div id="body-inner">

<span id="sidebar-toggle-span">
  <a href="#" id="sidebar-toggle" data-sidebar-toggle=""><i class="fas fa-bars"></i> navigation</a>
</span>

<?php
if (count($arr_uri) == 0) {
	/* Home Page */
?>

			<p>This page contains the live streams for many FreeBSD related events</p>
			<p>Streams may only be available while the events is in progress.</p>

<?php
} elseif (count($arr_uri) == 1) {
	/* Conference Page */
?>

			<p>Select a stream from the &quot;<?=$arr_streams[$selected_conf]['description']?>&quot; from the menu on the left.</p>

<?php
} else {
	/* Stream Page */
	$stream = $arr_uri[1];
	$config = $arr_streams[$selected_conf];

	switch ($config['type']) {
		case "officehours":
			$secrets = $arr_secrets[$selected_conf];
			$keys = "{$secrets['cdn_id']}:{$secrets['api_key']}";
			$arr_details = seapi($keys, "stream_metadata", "GET", $stream, $error);
?>

			<h1><?=$selected_conf?> - <?=$stream?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>

			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/clappr.min.js"></script>
			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/level-selector.min.js"></script>

			<div id="se_video_embed"></div>

			<script type="text/javascript">
			var player = new Clappr.Player({
			       source: 'https://<?=$secrets['username']?>-hls.secdn.net/<?=$secrets['username']?>-channel/play/<?=$stream?>.smil/playlist.m3u8',
			       parentId: "#se_video_embed",
			       autoPlay: true ,
			       poster: 'https://<?=$secrets['username']?>-hls.secdn.net/<?=$secrets['username']?>-channel/play/<?=$stream?>/thumbnail.jpg',
			       width: '720',
			       height: '400',
			       plugins: {core: [LevelSelector], playback: []},
			});
			</script>

			<p><a href="rtmp://<?=$secrets['username']?>-vsn.secdn.net/<?=$secrets['username']?>-channel/play/<?=$stream?>">RTMP Link (Lower Latency)</a></p>

			<iframe src="https://kiwiirc.com/client/irc.geekshed.net/?nick=BSD_?&theme=cli#freebsd" style="border:0; width:100%; height:540px;"></iframe>

<?php
			break;
		case "scaleengine":
			$secrets = $arr_secrets[$selected_conf];
			$keys = "{$secrets['cdn_id']}:{$secrets['api_key']}";
			$arr_details = seapi($keys, "stream_metadata", "GET", $stream, $error);
?>

			<h1><?=$selected_conf?> - <?=$stream?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>

			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/clappr.min.js"></script>
			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/level-selector.min.js"></script>
			
			<div id="se_video_embed"></div>
			
			<script type="text/javascript">
			var player = new Clappr.Player({
			       source: 'https://<?=$secrets['username']?>-hls.secdn.net/<?=$secrets['username']?>-live/play/<?=$stream?>.smil/playlist.m3u8',
			       parentId: "#se_video_embed",
			       autoPlay: true ,
			       poster: 'https://<?=$secrets['username']?>-hls.secdn.net/<?=$secrets['username']?>-live/play/<?=$stream?>/thumbnail.jpg',
			       width: '720',
			       height: '400',
			       plugins: {core: [LevelSelector], playback: []},
			});
			</script>

			<p><a href="rtmp://<?=$secrets['username']?>-vsn.secdn.net/<?=$secrets['username']?>-live/play/<?=$stream?>">RTMP Link (Lower Latency)</a></p>

<?php
			break;

		case "youtube":
			?>

			<h1><?=$selected_conf?> - <?=$stream?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>
			<iframe width="720" height="400" src="<?=$link?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

			<?php
			break;
			
		case "link":
			?>

			<h1><?=$selected_conf?> - <?=$stream?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>
			<a href="<?=$link?>">Click to watch: <?=$stream?></a>

			<?php
			break;
	}
}
?>

		</div>
	  </div>
	</section>

	<script src="/js/perfect-scrollbar.min.js"></script>
	<script src="/js/perfect-scrollbar.jquery.min.js"></script>
	<script src="/js/jquery.sticky.js"></script>
	<script src="/js/featherlight.min.js"></script>
	<script src="/js/html5shiv-printshiv.min.js"></script>
	<script src="/js/modernizr.custom.71422.js"></script>
	<script src="/js/learn.js"></script>
	<script src="/js/hugo-learn.js"></script>
  </body>
</html>

