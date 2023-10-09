<?php
$MAX_LENGTH = 65536;        // 最大バイト数
$MAX_CHAR   = 1024;         // 1行当たりの最大文字数
$MAX_TAB    = 32;           // 1行当たりの最大タブ文字数
$MAX_LINE   = 512;          // 最大改行数
$MAX_WIDTH  = 4096;         // 最大横幅 (px)
$SIZE       = 12;           // サイズ (pt)

// デフォルト色
$COLOR = array(
	'clR' => '00',   'clG' => '00',   'clB' => '00',
	'bgR' => 'ff', 'bgG' => 'ff', 'bgB' => 'ff',
	'bgTrans' => 0
);
// デフォルトのフォント
$FONT = 'mspg'; 

///////////////////////////////////////////////////////////////////////////////
// メイン処理
////

// POST['color_*']が指定されている場合は代入
// および色情報の変換（'#ff9900' → {'ff', '99', '00'}）
if(isset($_POST['color_char']) && strlen($_POST['color_char']) == 7){
	$colorChar = str_replace('#', '', $_POST['color_char']);
	list($COLOR['clR'], $COLOR['clG'], $COLOR['clB']) = str_split($colorChar, 2);
}

if(isset($_POST['color_back']) && strlen($_POST['color_back']) == 7){
	$colorBack = str_replace('#', '', $_POST['color_back']);
	list($COLOR['bgR'], $COLOR['bgG'], $COLOR['bgB']) = str_split($colorBack, 2);
}

// 色の16進表記を10進に
// 文字列から数値変換  （{'ff', '99', '00'} → {255, 153, 0}）
foreach($COLOR as $ck => $cv) $COLOR[$ck] = hexdec($cv);

// POST['font']が指定されている場合は代入
if(isset($_POST['font'])){
	$tmpFont = $_POST['font'];
	// 不正な指定
	if(preg_match('/[^A-Za-z0-9]/', $tmpFont) || !@file_exists("./$tmpFont.ttf")){
		output('Error: フォントを正しく指定してください。', 412);
	}
	$FONT = $tmpFont;
}

// POST['str']を読み込む
if(isset($_POST['str'])){
	$str = getStr();
}else{
	output('Error: 何も入力されてないです', 412);
}

if(isset($_POST['color_trans'])){
	if(is_array($_POST['color_trans']) || preg_match('/true/i', $_POST['color_trans'])){
		$COLOR['bgTrans'] = 1;
	}
}

// 描画の実行
output($str, 200);


function getStr(){
	global $MAX_LENGTH, $MAX_CHAR, $MAX_TAB, $MAX_LINE, $COLOR;

	// フォームデータ取得
	$str = $_POST['str'];

	// 容量測定
	$byte = strlen($str);
	if($byte > $MAX_LENGTH){
		output("データが大きすぎます。もう少し減らしてください。 $byte / $MAX_LENGTH Byte", 413);
	}
	if($byte < 1){
		output('何も入力されてないです', 412);
	}

	// 改行コード変換
	$str = str_replace("\x0D\x0A", "\n", $str);
	$str = strtr($str, "\x0D\x0A", "\n\n");

	// 1行当たりの文字数を測定
	foreach(explode("\n", $str) as $line => $strl){
		$line++;
		$strCount = mb_strlen($strl);
		if($strCount > $MAX_CHAR){
			output("長すぎる行があります。 → $line 行目 $strCount / $MAX_CHAR Chars", 413);
		}else{
			$tabCount = substr_count($strl, "\t");
			if($tabCount > $MAX_TAB){
				output("タブ文字が多すぎる行があります。 → $line 行目 $tabCount / $MAX_TAB Chars", 413);
			}
		}
	}

	// 改行数測定
	$line = substr_count($str, "\n") + 1;
	if($line > $MAX_LINE){
		output("改行が多すぎます。 $line / $MAX_LINE Line", 413);
	}

	// 特殊文字置換･行末空白削除
	$str = str_replace("\t", '    ', $str);
	$str = str_replace("&#700;", '"', $str);
	$str = str_replace("\xCA\xBC", '"', $str);
	$str = preg_replace("/&#63521;|&#63522;|&#63523;|&#xf821;|&#xf822;|&#xf823;|\xEF\xA0\xA1|\xEF\xA0\xA2|\xEF\xA0\xA3/", ':::', $str);
	$str = preg_replace("/(?: |　)+(?:\n|$)/", "\n", $str);

	return $str;
}

// 画像を描画
function drawing($str, $COLOR){
	global $MAX_WIDTH, $SIZE, $FONT;

	// 文字が占める縦横ピクセルを取得
	$image = imagecreate(1, 1);
	$bound = imagettftext($image, $SIZE, 0, 0, 0, 0, "./$FONT.ttf", $str);

	// 縦･横のいずれかが0ならエラー吐き
	if(!$bound[2] || !$bound[5]){
		output('Error: 空白文字しか入ってないです', 412);
	}

	// 横幅広すぎたらエラー吐き
	if($bound[2] > $MAX_WIDTH){
		output("Error: AAの横幅が広すぎます。\n$bound[2] / $MAX_WIDTH Pixel", 413);
	}

	// bound配列を変数に代入
	$srcx   =           - $bound[6];
	$srcy   =           - $bound[7];
	$width  = $bound[2] - $bound[0] +1;
	$height = $bound[3] - $bound[5] +1;

	// 特定したサイズで画像を作成
	$image = imagecreate($width, $height);
	$bg = imagecolorallocate($image, $COLOR['bgR'], $COLOR['bgG'], $COLOR['bgB']);
	$cl = imagecolorallocate($image, $COLOR['clR'], $COLOR['clG'], $COLOR['clB']);
	if($COLOR['bgTrans']){
		imagecolortransparent($image, $bg);
	}

	// 文字を描画
	imagettftext($image, $SIZE, 0, $srcx, $srcy +1, $cl, "./$FONT.ttf", $str);

	return $image;
}

// HTTPヘッダの指定と画像の出力
function output($str, $status){
	global $COLOR;

	$image = drawing($str, $COLOR);

	http_response_code($status);

	if($_GET['json'] === 'true'){
		output_json($image);
	}
	else{
		output_image($image);
	}
}

function output_json($image){

	// PNGに変換
	// 出力バッファを拾う
	ob_start();
	imagepng($image);
	$tmpPNGImage = ob_get_contents();
	ob_end_clean();
	imagedestroy($image);

	// base64に変換
	$base64Image = base64_encode($tmpPNGImage);

	// json 出力
	header('Content-Type: application/json');
	echo json_encode(array(
		'status' => $status,
		'body' => 'data:image/png;base64,'.$base64Image
	));

	exit();
}

function output_image($image){

	// png 出力
	header('Content-Type: image/png');
	imagepng($image);
	imagedestroy($image);

	exit();
}
?>
