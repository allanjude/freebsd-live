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

			<li data-nav-id="/<?=$conf?>/" title="<?=$conf?>" class="dd-item <?if ($selected_conf == $conf):?>parent active<?endif;?>">
				<a href="/<?=$conf?>/">

					<?php if ($selected_conf == $conf):?>
					<i class="fa fa-angle-double-down"></i>
					<?php else:?>
					<i class="fa fa-angle-double-right"></i>
					<?php endif;?>

					<?=$conf?>

				</a>
				<ul class="range-sections">

				<?php
				$arr_all_streams = array();
				if ($selected_conf == $conf) {
					switch ($arr_conf['type']) {
						case "scaleengine":
							$error = NULL;
							$secrets = $arr_secrets[$conf];
							$keys = "{$secrets['cdn_id']}:{$secrets['api_key']}";
							$arr_all_streams = seapi($keys, "stream_metadata", "GET", NULL, NULL, $error);
							foreach ((array)$arr_all_streams as $stream =>  $arr_details) {
								if ($arr_details and (isset($arr_details['type']) and $arr_details['type'] != "origin") and $arr_details['type'] != "channel") {
									/* Skip transcodes and other non-origin streams */
									unset($arr_all_streams[$stream]);
								}
							}
							break;
						case "youtube":
						case "twitch":
							$arr_all_streams = $arr_conf['urls'];
							break;
						case "link":
							$arr_all_streams = $arr_conf['urls'];
							break;
					}
				}
				if ($arr_all_streams):
					foreach ($arr_all_streams as $stream => $arr_details):?>

					<li data-nav-id="/<?=$conf?>/<?=$stream?>/" title="<?=$conf?> - <?=$stream?>" class="dd-item ">
						<a href="/<?=$conf?>/<?=$stream?>/">
							<i class="fa <?=($arr_details === false or $arr_details === NULL) ? "fa-angle-right" : "fa-signal"?> papers-menu-item"></i>
							 <?=str_replace("_", " ", $stream)?>
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
} elseif (count($arr_uri) == 1 and $arr_streams[$selected_conf]['type'] != "text") {
	/* Conference Page */
?>

			<p>Select a stream from the &quot;<?=$arr_streams[$selected_conf]['description']?>&quot; from the menu on the left.</p>

<?php
} else {
	/* Stream Page */
	$stream = $arr_uri[1];
	$config = $arr_streams[$selected_conf];

	switch ($config['type']) {
		case "scaleengine":
			$secrets = $arr_secrets[$selected_conf];
			$keys = "{$secrets['cdn_id']}:{$secrets['api_key']}";
			$arr_s = seapi($keys, "streams", "GET", $stream, NULL, $error);
			$arr_details = seapi($keys, "stream_metadata", "GET", $stream, NULL, $error);
?>

			<h1><?=$selected_conf?> - <?=$stream?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>
			<?
			if ($arr_details === NULL or $arr_details === false) {
				echo "<h3>This stream is not currently live, a replay may be available</h3>";
			}
			?>

			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/clappr.min.js"></script>
			<script type="text/javascript" src="//<?=$secrets['username']?>-embed.secdn.net/clappr/0.3.8/level-selector.min.js"></script>

			<div id="se_video_embed"></div>

			<script type="text/javascript">
			var player = new Clappr.Player({
			       source: '<?=$arr_s['playback_url']?>',
			       parentId: "#se_video_embed",
			       autoPlay: true ,
			       poster: '<?=$arr_s['thumbnail_url']?>',
			       width: '720',
			       height: '400',
			       plugins: {core: [LevelSelector], playback: []},
			});
			</script>

			<? if (isset($arr_streams[$selected_conf]['chatroom'])):
				$chatroom = $arr_streams[$selected_conf]['chatroom'];
				if (!trim($chatroom) or $chatroom == "auto") {
					$chatroom = "${selected_conf}-${stream}";
				}
			?>

			<iframe src="https://kiwiirc.com/client/irc.geekshed.net/?nick=BSD_?&theme=cli#<?=$chatroom?>" style="border:0; width:100%; height:540px;"></iframe>

			<p>Direct Stream: <a href="<?=$arr_s['playback_url']?>"><?=$arr_s['playback_url']?></a></p>

			<p>IRC Chat: irc.geekshed.net #<?=$chatroom?></p>

			<?
			endif;
			?>

<?php
			break;

		case "twitch":
			?>

			<h1><?=$selected_conf?> - <?=str_replace("_", " ", $stream)?></h1>
			<p><?=$arr_streams[$selected_conf]['urls'][$stream]['description']?></p>
			<iframe width="720" height="400" src="https://player.twitch.tv/?channel=<?=$arr_streams[$selected_conf]['urls'][$stream]['username']?>&parent=live.freebsd.org" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen scrolling="no"></iframe>

			<?php
			break;

		case "youtube":
			?>

			<h1><?=$selected_conf?> - <?=str_replace("_", " ", $stream)?></h1>
			<p><?=$arr_streams[$selected_conf]['description']?></p>
			<iframe width="720" height="400" src="<?=$arr_streams[$selected_conf]['urls'][$stream]['url']?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

			<?php
			break;
			
		case "text":
			?>

			<h1><?=$arr_streams[$selected_conf]['description']?></h1>
			<?=$arr_streams[$selected_conf]['text']?>

			<?php
			break;

		case "link":
			?>

			<h1><?=$selected_conf?> - <?=str_replace("_", " ", $stream)?></h1>
			<p><?=$arr_streams[$selected_conf]['urls'][$stream]['description']?></p>
			<a href="<?=$arr_streams[$selected_conf]['urls'][$stream]['url']?>">Click to watch: <?=str_replace("_", " ", $stream)?></a>

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

