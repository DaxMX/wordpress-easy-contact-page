<?php
# Página de contacto de fácil integración en WordPress.
# (c) DaxMX. Derechos reservados. https://daxmx.net/
# $id page-contacto.php
# PayPal donate: https://www.paypal.com/donate/?hosted_button_id=X6TBD2PPB7C9S
# Bitcoin donate: bc1qt4e4ex9zmgj0h7lsqku62wy3gcs6cxawgp0m59
# Instrucciones de Integración: https://daxmx.net/?p=283

# Carga Javascript y CSS requerido
if(!empty($_GET['css'])) {dax_contact_inline_css();}
if(!empty($_GET['js'])) {dax_contact_inline_js();}

# Comprobar que soporta captcha
$is_captcha = function_exists('imagecreatetruecolor');
if($is_captcha) session_start();
if(!empty($_GET['get_captcha']) && $is_captcha) {dax_contact_get_captcha();}

defined('ABSPATH') or die;
# Obtenemos los detalles de tu blog para enviar el mensaje
$para = get_option('admin_email');
$from = 'no-reply@' . dax_contact_fix_gpc(str_replace('www.', '', $_SERVER['HTTP_HOST']));
$WPN = get_option('blogname');

if($_POST && $para) {
foreach($_POST as $k => $v) {${$k} = dax_contact_fix_gpc($v);}
# Verificación de seguridad y validación de campos incorrectos o vacíos
$OKOK = !$is_captcha || (!empty($codigo) && $_SESSION['codigo_captcha'] === md5(strtolower($codigo)));
$IPOK = preg_replace('#[^a-z0-9\.\-:\[\]/]#', '', (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].'/' : '') . $_SERVER['REMOTE_ADDR']);
$UAOK = dax_contact_fix_gpc($_SERVER['HTTP_USER_AGENT']);
$ref = empty($ref) ? '' : $ref;
$mail = (!empty($mail) && preg_match('#^[a-z0-9\.\-\_]+@[a-z0-9\.\-\_]+\.[a-z]{2,12}$#i', $mail)) ? $mail : 0;
$nombre = empty($nombre) ? 0 : preg_replace('#[^a-z0-9\-_ ]#i', '', $nombre);
if(strlen($nombre) < 3) $nombre = 0;
$msg = empty($msg) || strlen($msg) < 21 ? 0 : wordwrap(nl2br($msg), 75, "\n", true);
	# Si se pasan todas las verificaciones, preparamos el mensaje
	if($OKOK && $mail && $nombre && $msg) {
	$hmsg = "From: \"$mail\" <$from>\r\n"
	. "Reply-To: \"$nombre\" <$mail>\r\n"
	. "X-Originating-IP: $IPOK\r\n"
	. "Message-Id: <" . time() . "-$from>\r\n"
	. "MIME-Version: 1.0\r\n"
	. "Content-Type: text/html; charset=utf-8\r\n";
	$html = "<!DOCTYPE html'>\n"
	. "<html><head>\n<meta charset='utf-8'>\n"
	. "<meta name='generator' content='DaxMX WordPress Mailer'>\n"
	. "<style>body{font-size:14px;}\ntd{background:#F3F3F3;color:#000;text-align:left;padding:3px}\n</style>"
	. "</head>\n"
	. "<body>\n"
	. "<p>Mensaje desde <b>".get_site_url()."</b>:</p>\n"
	. "<p>\n$msg\n</p>\n"
	. "<p><b>Datos del remitente:</b></p>\n"
	. "<div style='margin:10px'>\n<table style='border-spacing:2px;border:0'>\n"
	. "<tr><td style='width:120px'>Direcci&oacute;n IP:</td>\n"
	. "<td>$IPOK</td></tr>\n"
	. "<tr><td>Navegador:</td>\n"
	. "<td>$UAOK</td></tr>\n"
	. "<tr><td>Lleg&oacute; desde:</td>\n"
	. "<td>$ref</td></tr>\n"
	. "<tr><td>Nombre:</td>\n"
	. "<td>$nombre</td></tr>\n"
	. "<tr><td>E-Mail:</td>\n"
	. "<td>$mail</td></tr>\n"
	. "</table></div>\n"
	. "<p>&copy;" . date('Y') . " $WPN</p>\n"
	. "</body></html>\n";
		# Enviamos el mensaje de contacto y mostramos el resultado al usuario
		if(@mail($para, "Contacto $WPN", $html, $hmsg)) {
		die('<p class="ok">Su mensaje se envi&oacute; correctamente, gracias.</p>');
		} else {
		die('<p class="red">Error al enviar su mensaje, intente m&aacute;s tarde.</p>');
		}
	} else {
		# Datos y filtros de seguridad incorrectos, detallamos los mensajes de error
		$err = '<div class="red"><strong>Error, corrige esta información:</stron><br><ul>';
		if(!$nombre) {$err .= '<li id="error-nombre">Su nombre parece incorrecto.</li>';}
		if(!$mail) {$err .= '<li id="error-mail">La direcci&oacute;n de correo electr&oacute;nico es incorrecta.</li>';}
		if(!$msg) {$err .= '<li id="error-msg">El mensaje no parece válido.</li>';}
		if(!$OKOK) {$err .= '<li id="error-codigo">El c&oacute;digo de la imagen es incorrecto.</li>';}
	die($err.'</ul></div>');
	}
}

# Evitamos que los motores de búsqueda indexen tu página de contacto
if(function_exists('wp_robots')) {
remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
add_filter('wp_robots', 'wp_robots_no_robots');
} else add_action('wp_head', 'wp_no_robots');

add_action('wp_enqueue_scripts', 'dax_contact_add_css');
get_header();

# Formulario de contacto inicia
?>
<div id="page" class="single">
	<article id="content" class="article page">
	<div class="single_post">
		<header><h1 class="title"><a href="<?php the_permalink($page->ID);?>">Contacto <?php echo $WPN;?></a></h1></header>
		<div class="post-content">
		<?php if($para) { ?>
			<p>Captura los datos a continuación. Todos los campos son requeridos.</p>
			<div id="res"></div>
			<form id="contacto" action="<?php the_permalink($page->ID);?>" method="post">
			<p>
			<input type="hidden" name="ref" value="<?php echo empty($_SERVER['HTTP_REFERER']) ? '' : htmlspecialchars(dax_contact_fix_gpc($_SERVER['HTTP_REFERER']));?>" id="ref">
			<label for="nombre">Nombre:</label>
			<input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="" tabindex="1" required autofocus>
			</p>
			<p>
			<label for="mail">Correo Electrónico:</label>
			<input type="email" name="mail" id="mail" placeholder="yo@ejemplo.com" value="" tabindex="2" required>
			</p>
			<p>
			<label for="msg">Mensaje:</label>
			<textarea placeholder="Escribe tu mensaje..." name="msg" id="msg" tabindex="3" required></textarea>
			</p>
			<?php if($is_captcha) { ?>
			<p>
			<label for="codigo">Código de Verificación:</span></label>
			<input type="text" name="codigo" id="codigo" style="width:200px;" value="" maxlength="6" tabindex="4" required>
			<img src="<?php echo get_template_directory_uri()?>/page-contacto.php?get_captcha=1" id="get-contact-captcha" alt="captcha" width="150" height="60" style="vertical-align:bottom;margin-left:16px;">
			</p>
			<?php } ?>
			<p style="text-align:center"><button type="submit">Enviar Mensaje</button></div>
			</form>
		<?php } else echo "<p>El administrador no ha habilitado esta página.</p>";
		?>
			<p>Creado con <a href="https://daxmx.net/formulario-contacto-wordpress-sin-plugins-283.html">Formulario de contacto WordPress</a> por <a href="https://daxmx.net/">DaxMX</a></p>
		</div>
	</div>
	</article>
</div>
<?php
get_footer();
# Funciones internas de la página de contacto
function dax_contact_fix_gpc($s) {return $s === '' ? '' : trim(strip_tags($s));}
function dax_contact_add_css() {
	$d = get_template_directory_uri().'/page-contacto.php';
	wp_enqueue_style('daxmx-contact-css', "$d?css=1", array(), null);
	wp_enqueue_script('daxmx-contact-js', "$d?js=1", array(), null, true);
}
function dax_contact_inline_css() {
	$css = '.red,.ok {margin:10px auto;width:100%;padding:5px;line-height:20px;}
.red {background:#FFC;border:1px solid #F30;}
.ok {background:#CF9;border:1px solid #0C3;}';
	header('Content-Type: text/css; charset=uft-8');
	header('Content-Length: '.strlen($css));
	die($css);
}
function dax_contact_inline_js() {
header('Content-Type: application/javascript; charset=uft-8');
?>
//<script>
(function() {
	function $(id) {return document.getElementById(id);}
	function Rimg() {if($('codigo')){var d=(new Date()).getTime(),i=$('get-contact-captcha').src;$('get-contact-captcha').src=i.replace(/\?iumx_captcha=.+/, '?get_captcha='+d);$('codigo').value='';}}
	function ajx() {
	if(window.XMLHttpRequest) return new XMLHttpRequest();
	try {return ActiveXObject('MSXML2.XMLHTTP.3.0');} catch(e) {return null;}
	}
	function AjaxMSG() {
	var ids=['mail','nombre','codigo','msg'];
	var url=location.href,e=$('mail').value,n=$('nombre').value,m=$('msg').value,c='',r=$('ref').value;
	if($('codigo')) c=$('codigo').value;
	$('nombre').focus();
	if(e==''||n==''||m==''){$('res').innerHTML='<p class="red">Error: todos los campos son requeridos.</p>';Rimg();return false;}
	var xml = xml || ajx();
	if(xml != null) {
	$('res').innerHTML='<p class="ok">Enviando...</p>';
	xml.onreadystatechange=function() {
		if(xml.readyState == 4) {
			if(xml.status == 200) {
			var str=xml.responseText;$('res').innerHTML=str;Rimg();
			if(str.indexOf('error') == -1) {for(var i=0;i<ids.length;i++) {if($(ids[i]))$(ids[i]).value='';}i=null;}
			else {for(var x=0;x<ids.length;x++){if(str.indexOf('error-'+ids[x]) != -1) $(ids[x]).value='';}x=null;}
			$('nombre').focus();
			}
		}
	};
	xml.open('POST',url);
	xml.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=utf-8');
	xml.send('mail='+encodeURIComponent(e)+'&nombre='+encodeURIComponent(n)+'&msg='+encodeURIComponent(m)+'&codigo='+encodeURIComponent(c)+'&ref='+encodeURIComponent(r));
	return false;
	}
	}
	var inputs = document.createElement('input');
	var supports = {};
	supports.autofocus   = 'autofocus' in inputs;
	supports.required    = 'required' in inputs;
	supports.placeholder = 'placeholder' in inputs;
	if(!supports.autofocus) {}
	if(!supports.required) {}
	if(!supports.placeholder) {}
	if($('contacto')) $('contacto').onsubmit = function() {return AjaxMSG();};
})();
<?php die;
}
# DaxMX captcha library. (c) IUMX/DaxMX
function dax_contact_get_captcha() {
$fnt = __DIR__ . '/captcha.ttf';
$img = imagecreatetruecolor(150, 60);
if(!$img) return false;
$txt = ['a','b','c','d','e','f','g','h','k','n','p','q','r','s','t','u','v','x','y','z','2','3','4','5','6','7','8','9'];
$cap = '';
for($i = 0; $i < 6; $i++) {$cap .= $txt[mt_rand(0,27)];}
$_SESSION['codigo_captcha'] = md5($cap);
$bg = mt_rand(0,2);
switch($bg) {
	case 0: $bg = imagecolorallocate($img, 0xFF,0xCC,0x00);break;
	case 1: $bg = imagecolorallocate($img, 255, 255, 255);break;
	case 2: $bg = imagecolorallocate($img, 0xFF,0x00,0xBB);break;
}
imagefill($img, 0, 0, $bg);
$black = imagecolorallocatealpha($img, 0, 0, 0, 100);
$style = array(
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
$black = imagecolorallocatealpha($img,0,0,0,50);				
$style2 = array(
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, 
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black);
$black = imagecolorallocatealpha($img,0,0,0,30);
$style3 = array(
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		$black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black, $black,
		IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);				
$s = mt_rand(1, 3);
$def = 0;
switch($s) {
	case 1:
	$def = $style;
	$cs = 2; 
	break;
	case 2:
	$def = $style2;
	$cs = 2; 
	break;
	case 3:
	$def = $style3;
	$cs = 1; 
	break;
}				
$s2 = mt_rand(4, 6);
$def2 = 0;
switch($s2) {
	case 4:
	$def2 = $style;
	$cs2 = 2; 
	break;
	case 5:
	$def2 = $style2;
	$cs2 = 2; 
	break;
	case 6:
	$def2 = $style3;
	$cs2 = 1; 
	break;
}
$s3 = mt_rand(6, 8);
$def3 = 0;
switch($s3) {
	case 6:
	$def3 = $style;
	$cs3 = 2; 
	break;
	case 7:
	$def3 = $style2;
	$cs3 = 2; 
	break;
	case 8:
	$def3 = $style3;
	$cs3 = 1; 
	break;
}
$s4 = mt_rand(8, 10);
$def4 = 0;
switch($s4) {
	case 8:
	$def4 = $style;
	$cs4 = 2; 
	break;
	case 9:
	$def4 = $style2;
	$cs4 = 2; 
	break;
	case 10:
	$def4 = $style3;
	$cs4 = 1; 
	break;
}
$s5 = mt_rand(10, 12);
$def5 = 0;
switch($s5) {
	case 10:
	$def5 = $style;
	$cs5 = 2; 
	break;
	case 11:
	$def5 = $style2;
	$cs5 = 2;
	break;
	case 12:
	$def5 = $style3;
	$cs5 = 1; 
	break;
}
imagesetthickness($img, 1);
$j = 6;
$c = imagecolorallocate($img, 0, 0, 0);
for($i =0; $i < 6; $i++) {
	imagefttext($img, 16, 0, $j, 40, imagecolorallocate($img, 0, 0, 0), $fnt, $cap[$i]);
	$j=$j+23;
}
function wave_area($img, $x, $y, $width, $height, $amplitude = 10, $period = 10){
	$height2 = $height * 2;
	$width2 = $width * 2;
	$img2 = imagecreatetruecolor($width2, $height2);
	imagecopyresampled($img2, $img, 0, 0, $x, $y, $width2, $height2, $width, $height);
	if($period == 0) $period = 1;
	for($i = 0; $i < ($width2); $i += 2)
	imagecopy($img2, $img2, $x + $i - 2, $y + sin($i / $period) * $amplitude, $x + $i, $y, 2, $height2);
	imagecopyresampled($img, $img2, $x, $y, 0, 0, $width, $height, $width2, $height2);
	imagedestroy($img2);
}
$amp = mt_rand(6,8);
$per = mt_rand(15,29);
wave_area($img, 0, 0, imagesx($img), imagesy($img), $amp, $per);
imagesetstyle($img,$def);
imagesetthickness($img, $cs);
imageline($img,1,53,149,53,IMG_COLOR_STYLED);
imagesetstyle($img,$def2);
imagesetthickness($img, $cs2);
imageline($img,1,43,149,43,IMG_COLOR_STYLED);
imagesetstyle($img,$def3);
imagesetthickness($img, $cs3);
imageline($img,1,33,149,33,IMG_COLOR_STYLED);
imagesetstyle($img,$def4);
imagesetthickness($img, $cs4);
imageline($img,1,23,149,23,IMG_COLOR_STYLED);
imagesetstyle($img,$def5);
imagesetthickness($img, $cs5);
imageline($img,1,13,149,13,IMG_COLOR_STYLED);
wave_area($img, 0, 0, imagesx($img), imagesy($img), $amp, $per);
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
die;
}
?>