<?php 
session_start();
error_reporting(E_ALL);
if(!isset($_SESSION['update_step'])) {
	$_SESSION['update_step'] = 1;
}
include("includes/head.php"); 
?>
<title>Start Updation</title>
<?php include("includes/header.php"); ?>
<?php include("includes/sidebar.php"); ?>
<div class="rightSide">
	<div class="col-xs-12">
		<div class="tab-content shadow-1">
			<div class="tab-pane active" id="welcome">
				<h1 class="primaryText">Welcome To <strong>"Video Downloader Script"</strong> Updater</h1>
				<h3 class="secondaryText"><i class="fa fa-folder-o"></i>Video Downloader Script</h3>
				<h4 class="primaryText">Thanks for Purchasing Nexthon's Product</h4>
				<p class="secondaryText">Before updating the script please read the update section in documentation</p>
				<p class="secondaryText">If you face any problem while updating then open the support ticket here : <strong><a href="https://nexthon.ticksy.com" target="_blank">Support</a></strong></p>
				<div class="clearfix"></div><br>
				<a href="./requirements.php" class="btn btn-theme btn-lg">Start Update Process!</a>
			</div>
		</div>
	</div>
</div>
<?php include("includes/footer.php"); ?>