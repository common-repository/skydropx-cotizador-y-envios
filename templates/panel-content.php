<?php
/**
 * Template: Panel Content
 *
 * @package  Ecomerciar\Skydropx\Templates
 */

$url = $args['url'];
?>

<iframe id="skydropx" src="<?php echo esc_url( $url ); ?>"?></iframe>
<style>
	.notice{
	display:none;
	}

	#wpwrap{
		background-color: white;
	}
	iframe#skydropx {
		height:calc(95vh);
		width:calc(85vw);
		box-sizing: border-box;
	}
</style>
