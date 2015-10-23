<?php
session_start();
$image = imagecreatetruecolor(85,28);//返回一个图像标识符，代表了一幅大小为 x_size 和 y_size 的黑色图像
$bgcolor = imagecolorallocate($image,255,255,255);//#ffffff 为一幅图像分配颜色
imagefill($image,0,0,$bgcolor);//在 image 图像的坐标 x，y（图像左上角为 0, 0）处用 color 颜色执行区域填充
/***
//数字随机
for($i=0;$i<4;$i++){
	$fontsize = 6;
	$fontcolor = imagecolorallocate($image,rand(0,120),rand(0,120),rand(0,120));
	$fontcontent = rand(0,9);
	$x=($i*100/4)+rand(5,10);
	$y=rand(5,10);
	imagestring($image,$fontsize,$x,$y,$fontcontent,$fontcolor);
}
**/
//字母数字随机
$verify_code = "";
for($i=0;$i<4;$i++){
	$fontsize = 5;
	$fontcolor = imagecolorallocate($image,rand(0,120),rand(0,120),rand(0,120));
	$data = "ABCDEFHIJKMNPQRTUVWXY3456789";
	$dataShuffle = str_shuffle($data);
	$fontcontent = substr($dataShuffle,0,1);
	$verify_code .= $fontcontent;
	$x=$i*15+15;
	//$x=rand(2,intval((100-14)/4));
        $y=mt_rand(1,8);
        imagestring($image,$fontsize,$x,$y,$fontcontent,$fontcolor);
}
$_SESSION["verify_code"] = $verify_code;
//生成干扰元素
for($i=0;$i<200;$i++){
	$pointcolor = imagecolorallocate($image,rand(5,200),rand(5,200),rand(5,200));
	imagesetpixel($image,rand(1,99),rand(1,29),$pointcolor);//在 image 图像中用 color 颜色在 x，y 坐标（图像左上角为 0，0）上画一个点
}
//生成干扰线
for($i=0;$i<3;$i++){
	$linecolor = imagecolorallocate($image,rand(5,200),rand(5,200),rand(5,220));
	imageline($image,rand(1,99),rand(1,29),rand(22,99),rand(1,29),$linecolor);
}
header("Content-type:image/png");
imagepng($image);
imagedestroy($image);
