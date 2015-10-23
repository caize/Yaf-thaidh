<?php
//图片水印缩略图类
//操作图片就得把图片大小，类型，信息拿到
//水印：就是把指定的水印复制到目标上，并加透明效果
//缩略图：就是把大图片复制到小尺寸画面上
class L_Image{
    //分析图片信息
    protected static function imageInfo($image){
        //判断图片是否存在
        if(!file_exists($image)){
            return false;
        }
        $info = getimagesize($image);
        if($info == false){
            return false;
        }
        //此时info分析出来，是一个数组
        $img["width"] = $info[0];
        $img["height"] = $info[1];
        $img["ext"] = substr($info["mime"],strpos($info["mime"],"/")+1);
        return $img;
    }
    /*加水印
    *param String $dst 要操作的目标图
    *param String $water 水印小图
    *param String $save 保存路径 不填则默认替换原始图
    *param int $alpha 透明度
    *param int $pos水印位置 默认右下角
    */
    public static function water($dst,$water,$save=NULL,$alpha=50,$pos=2){
        //首先得保证两个图片存在
        if(!file_exists($dst) || !file_exists($water)){
            return false;
        }
        //保证水印不能比待操作图片大
        $dstInfo = self::imageInfo($dst);
        $waterInfo = self::imageInfo($water);
        if($waterInfo["height"] > $dstInfo["height"] || $waterInfo["width"] > $dstInfo["width"]){
            return false;
        }
        //两张图得读到画布上，但是图片可能是png 也可能是jpeg 用什么函数读？
        $dstFunc = "imagecreatefrom".$dstInfo["ext"];
        $waterFunc = "imagecreatefrom".$waterInfo["ext"];
        if(!function_exists($dstFunc) || !function_exists($waterFunc)){
            return false;
        }
        //动态加载函数来创建画布
        $dstImg = $dstFunc($dst);//创建待操作的画布
        $waterImg = $waterFunc($water);//创建水印画布
        //根据水印位置 计算粘贴的坐标
        switch($pos){
            case 0://左上角
                $posx = 0;
                $posy = 0;
                break;
            case 1://右上角
                $posx = $dstInfo["width"] - $waterInfo["width"];
                $posy = 0;
                break;
            case 3://左下角
                $posx=0;
                $posy = $dstInfo["height"] - $waterInfo["height"];
                break;
            default:
                $posx = $dstInfo["width"] - $waterInfo["width"];
                $posy = $dstInfo["height"] - $waterInfo["height"];

        }
        //加水印
        imagecopymerge($dstImg,$waterImg,$posx,$posy,0,0,$waterInfo["width"],$waterInfo["height"],$alpha);
        //保存
        if(!$save){
            $save = $dst;
            unlink($dst);//删掉原图
        }
        $createFunc = "image".$dstInfo["ext"];
        $createFunc($dstImg,$save);
        //释放图片资源
        imagedestroy($dstImg);
        imagedestroy($waterImg);
        return true;
    }
    /**
     *生成缩略图
     *
     *
     */
    public static function thumb($dst,$save=NULL,$width=200,$height=200){
        //首页判断图片存不存在
        $dstInfo = self::imageInfo($dst);
        if($dstInfo == false){
            return false;
        }
        //计算缩放比例 合理应该是按照比例更小的来
        $calc = min($width/$dstInfo["width"],$height/$dstInfo["height"]);
        //创建原始画布
        $dstFunc = "imagecreatefrom".$dstInfo["ext"];
        $dstImg = $dstFunc($dst);
        //创建缩略画布
        $tmpImg = imagecreatetruecolor($width,$height);
        //创建白色填充缩略画布
        $white = imagecolorallocate($tmpImg,255,255,255);
        //填充缩略图画布
        imagefill($tmpImg,0,0,$white);
        //复制并缩略
        $dstWidth = (int)$dstInfo["width"]*$calc;
        $dstHeight = (int)$dstInfo["height"]*$calc;
        $paddingX = (int)($width-$dstWidth) / 2;
        $paddingY = (int)($height-$dstHeight) / 2;
        imagecopyresampled($tmpImg,$dstImg,$paddingX,$paddingY,0,0,$dstWidth,$dstHeight,$dstInfo["width"],$dstInfo["height"]);
        //保存图片
        if(!$save){
            $save = $dst;
            unlink($dst);
        }
        $createFunc = "image".$dstInfo["ext"];
        $createFunc($tmpImg,$save);
        imagedestroy($dstImg);
        imagedestroy($tmpImg);
        return true;
    }
}

