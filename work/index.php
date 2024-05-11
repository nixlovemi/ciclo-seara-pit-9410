<?php
include_once('session.php');
include_once('constants.php');
include_once('functions.php');

$config = readConfig();
$_SESSION['config'] = $config;
?>

<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<title><?=$config['title']?></title>
		<meta name="description" content="<?=$config['title']?>" />
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=1280, height=800, initial-scale=1.0" />
		<link rel="shortcut icon" href="../favicon.ico" /> 
		<link rel="stylesheet" type="text/css" href="css/jquery.jscrollpane.custom.css" />
		<link rel="stylesheet" type="text/css" href="css/bookblock.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/font-awesome-4.7.0/css/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap-image-checkbox/dist/css/bootstrap-image-checkbox.min.css" />
		<link rel="stylesheet" type="text/css" href="css/custom.css" />
	</head>
	<body>
		<div id="dv-container" class="dv-container">	
			<div class="menu-panel">
				<h3 class="mb-0">
					Menu Catálogo
					<br />
					<strong id="apas2024">APAS 2024</strong>
				</h3>

				<ul id="menu-toc" class="menu-toc">
					<?php
						$groupOrder = $config['menuGroupOrder'];
						foreach ($groupOrder as $groupIdx => $groupName): ?>
							<li>
								<a class="new-menu-header collapsed" data-toggle="collapse" href="#collapseMenu<?=$groupIdx?>" role="button" aria-expanded="false" aria-controls="collapseMenu<?=$groupIdx?>">
									<?=$groupName?>
								</a>
								<div class="collapse new-menu-links" id="collapseMenu<?=$groupIdx?>">
									<?php
									$items = array_filter($config['pages'], function($val) use ($groupIdx) {
										return $groupIdx === $val['menuGroup'];
									});

									foreach($items as $menu):?>
										<a href="javascript:;" class="clickable" data-idx="<?=$menu['menuIdx']?>"><?=$menu['product']?></a>
									<?php endforeach; ?>
								</div>
							</li>
						<?php endforeach;
					?>
				</ul>

				<?php
				/*
				<ul id="menu-toc" class="menu-toc">
					<?php
					$current = 'menu-toc-current';
					foreach($config['pages'] as $item):?>
						<li
							style="display:<?=($item['menu']) ? 'block': 'none'?>"
							class="clickable <?=$current?>"
							data-idx="<?=$item['menuIdx']?>"
						>
							<a href="javascript:;"><?=$item['product']?></a>
						</li>
					<?php $current = ''; endforeach; ?>
				</ul>
				*/
				?>
			</div>

			<div class="bb-custom-wrapper">
				<div id="bb-bookblock" class="bb-bookblock">
					<?php foreach($config['pages'] as $nbr => $arrInfo): ?>
						<div class="bb-item" id="<?=ITEM_PAGE_PREFIX . $nbr?>">
							<?php include($arrInfo['path']) ?>
						</div>
					<?php endforeach; ?>
				</div>
				
				<nav>
					<span id="bb-email" data-toggle="modal" data-target="#emailModal">
						<img src="images/nav-share.png" />
					</span>
					<span id="bb-nav-prev">
						<img src="images/nav-left.png" />
					</span>
					<span id="bb-nav-next">
						<img src="images/nav-right.png" />
					</span>
				</nav>

				<span id="tblcontents" class="menu-button">
					<img src="images/nav-menu.png" />
				</span>

			</div>
		</div>

		<script src="js/modernizr.custom.79639.js"></script>
		<script src="js/jquery.js"></script>
		<script src="js/bootstrap.js"></script>
		<script src="js/jquery.mousewheel.js"></script>
		<script src="js/jquery.jscrollpane.min.js"></script>
		<script src="js/jquerypp.custom.js"></script>
		<script src="js/jquery.bookblock.js"></script>
		<script src="js/page.js"></script>
		<script>
			jQuery.fn.center = function () {
				this.css("position","absolute");
				this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + 
															$(window).scrollTop()) + "px");
				this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + 
															$(window).scrollLeft()) + "px");
				return this;
			}

			function execWindowSizeCalc()
			{
				let $baseDiv = $('#bb-bookblock');
				let dvW = $(window).width();
				let dvH = $(window).height();
				// let targetW = 2400;
				// let targetH = 1600;
				let targetW = 1920;
				let targetH = 1280;

				let newW = 0;
				let newH = 0;

				if (dvW <= dvH) {
					newW = dvW;
					newH = newW * (targetH / targetW);
				} else {
					newH = dvH;
					newW = newH * (targetW / targetH);
				}

				// adjust if needed O____O
				if (newW > dvW) {
					newW = dvW;
					newH = newW * (targetH / targetW);
				}
				if (newH > dvH) {
					newH = dvH;
					newW = newH * (targetW / targetH);
				}

				$baseDiv.width(newW);
				$baseDiv.height(newH);

				// center div
				$baseDiv.center();
			}

			$(function() {
				Page.init();
			});

			execWindowSizeCalc();
			$( window ).on( "resize", function() {
				execWindowSizeCalc();
			});
		</script>

		<?php
		// modals
		include('email_modal.php');
		?>
	</body>
</html>
