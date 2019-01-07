<?php

class MergeImage{
    public $width;
    public $height;
    public $multiple;
    public $im;   //操作的图片资源
    public $color;

    /** 画布创建
     * MergeImage constructor.
     * @param int $width
     * @param int $height
     * @param int $multiple
     */
    public function __construct($width = 500,$height = 500,$multiple = 1)
    {
        $this->width = $width*$multiple;
        $this->height = $height*$multiple;
        $this->multiple = $multiple;
        $this->im = imagecreatetruecolor($this->width, $this->height);
        //初始化默认颜色
        $this ->setColor(0,0,0);
        $color = imagecolorallocate($this->im,255,255,255);
        imagefill($this->im,0,0,$color);
    }


    /** 添加单行文字
     * @param $x  文字起始x点
     * @param $y  文字起始y点
     * @param $fontSize 文字大小(受控于放大倍数)
     * @param $fontFile 文字字体
     * @param $string   位子内容
     * @param $center int 文字是否以传的坐标点居中。
     * @return bool
     */
    public function mergeFontSimple($x,$y,$fontSize,$fontFile,$string,$center = 0)
    {
        if(!file_exists($fontFile)) {
            return false;
        }
        $fontSize = $this->multiple*$fontSize;
        $location = imagettfbbox($fontSize, 0, $fontFile, $string);
        if($center) {
            $centerX = ($location[2] + $location[0])/2;
            $centerY = ($location[1] + $location[7])/2;
            imagettftext($this->im,$fontSize,0,$this->width*$x/100-$centerX,$this->height*$y/100-$centerY,$this->color,$fontFile,$string);
        }else{
            imagettftext($this->im,$fontSize,0,$this->width*$x/100-$location[6],$this->height*$y/100-$location[7],$this->color,$fontFile,$string);
        }
        return true;
    }

    /** 在规定区域内添加段落
     * @param $x1  区域起始位置x
     * @param $y1  区域起始位置y
     * @param $x2  区域结束位置x
     * @param $y2  区域结束位置y
     * @param $fontSize  字体大小
     * @param $fontFile  字体样式
     * @param $content   段落内容
     * @param float $rowSpace  行间 以字高为标准 默认0.1字体间距
     * @return bool
     */
    public function mergeFontContent($x1,$y1,$x2,$y2,$fontSize,$fontFile,$content,$rowSpace=0.1)
    {
        if(!file_exists($fontFile)) {
            return false;
        }
        $fontSize = $this->multiple*$fontSize;
        $location = imagettfbbox($fontSize, 0, $fontFile, $content);
        $fontLine = $location[2] - $location[0];
        $fontNum = mb_strlen($content); //总字数
        $fontW = $fontLine/$fontNum;  //每字宽
        $fontH = $location[1]-$location[7];  //每字高
        $area_x = abs($x2-$x1)/100*$this->width; //区域像素x
        $area_y = abs($y2-$y1)/100*$this->height; // 区域像素y
        $tmp_num_x = floor($area_x/$fontW); //每行字数
        $tmp_num_y = floor($area_y/($fontH*(1+$rowSpace)));  //区域行数
        if(!$tmp_num_x || !$tmp_num_y) {
            return false;
        }
        $need_num_y = ceil($fontNum/$tmp_num_x); //所需行数
        if($need_num_y<=$tmp_num_y) {
            $tmp_num_y = $need_num_y;
        }
        $start = 0; //截取字符串真正的起点
        $exampleLine = 0; //实例长度
        for($i=0;$i<$tmp_num_y;$i++) {
            $showContent = mb_substr($content,$start,$tmp_num_x);
            //修正每行字长
            $str_location = imagettfbbox($fontSize, 0, $fontFile, $showContent);
            $line = $str_location[2] - $str_location[0];
            if($i == 0){
                $exampleLine = $line;
            }
            $diffFont = round(($line - $exampleLine)/$fontW); //相差字数个数
            $real_num = $tmp_num_x - $diffFont;
            $showContent = mb_substr($content,$start,$real_num);//真正截取的字长
            $start += $real_num;
            imagettftext($this->im,$fontSize,0,$x1/100*$this->width-$location[6],$y1/100*$this->height-$location[7]+$i*$fontH*(1+$rowSpace),$this->color,$fontFile,$showContent);
        }
        return true;
//        imagettftext($this->im,100,0,100-1,100-(-111),$color,__DIR__.'/black.ttf','神龙');
    }

    /** 背景填充
     * @param $x1 填充x起点的百分比 (0-100)
     * @param $y1 填充y起点的百分比 (0-100)
     * @param $x2 填充x终点的百分比 (0-100)
     * @param $y2 填充y终点的百分比 (0-100)
     * @return bool
     */
    public function mergeBackColor($x1,$y1,$x2,$y2)
    {
        $new_w = abs(($x1-$x2)/100*$this->width);
        $new_h = abs(($y1-$y2)/100*$this->height);
        if($new_w == 0 ||$new_h == 0) {
            return false;
        }
        $im_back = imagecreatetruecolor($new_w,$new_h);
        imagefill($im_back,0,0,$this->color);
        imagecopyresampled($this->im,$im_back,$x1/100*$this->width,$y1/100*$this->height,0,0,$new_w,$new_h,$new_w,$new_h);
        imagedestroy($im_back);
        return true;
    }


    /** 画多边形并填充颜色
     * @param array $postion  [x1,y1,x2,y2,x3,y3....]
     * @return bool
     */
    public function mergePolygon(array $postion = [])
    {
        $count = count($postion);
        if(empty($count)) {
            return false;
        }
        $num_points = floor($count/2);
        $points = [];
        for ($i=0;$i<$num_points*2;$i++) {
            $points[$i] = $this->width*$postion[$i]/100;
            $i++;
            $points[$i] = $this->height*$postion[$i]/100;
        }
        imagefilledpolygon($this->im,$points,$num_points,$this->color);
        return true;
    }

    /** 图片填充 (将不同大小的图片填充到规定位置里)
     * @param $url 图片地址 可以是 路径也可以是url
     * @param $x1  填充x起点的百分比 (0-100)
     * @param $y1  填充y起点的百分比 (0-100)
     * @param $x2  填充s结束的百分比 (0-100)
     * @param $y2  填充y结束的百分比 (0-100)
     * @return bool
     */
    public function mergeImage($url,$x1,$y1,$x2,$y2)
    {
        $new_w = abs(($x1-$x2)/100*$this->width);
        $new_h = abs(($y1-$y2)/100*$this->height);
        if($new_w == 0 ||$new_h == 0) {
            return false;
        }
        if(!$info = @getimagesize($url)) {
            return false;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $im_image = imagecreatefromjpeg($url);
                break;
            case IMAGETYPE_PNG:
                $im_image = imagecreatefrompng($url);
                break;
            case IMAGETYPE_GIF:
                $im_image = imagecreatefromgif($url);
                break;
            default:
                return false;
                break;
        }
        $tmp_im = imagecreatetruecolor($new_w,$new_h);
        $color = imagecolorallocate($tmp_im,255,255,255);
        imagefill($tmp_im,0,0,$color);
        imagecopyresampled($tmp_im,$im_image,0,0,0,0,$new_w,$new_h,$info[0],$info[1]);
        imagecopyresampled($this->im,$tmp_im,$x1/100*$this->width,$y1/100*$this->height,0,0,$new_w,$new_h,$new_w,$new_h);
        imagedestroy($im_image);
        imagedestroy($tmp_im);
        return true;
    }
    
        /** png透明图像合成
     * @param $url
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @return bool
     */
    public function mergeImageAlphaPng($url, $x1, $y1, $x2, $y2)
    {
        $new_w = abs(($x1-$x2)/100*$this->width);
        $new_h = abs(($y1-$y2)/100*$this->height);
        if($new_w == 0 ||$new_h == 0) {
            return false;
        }
        if(!$info = @getimagesize($url)) {
            return false;
        }
        switch ($info[2]) {
            case IMAGETYPE_PNG:
                $im_image = imagecreatefrompng($url);
                break;
            default:
                return false;
                break;
        }
        /**
         * 重采样 并 保留透明度
         */
        $tmp_im = imagecreatetruecolor($new_w,$new_h);
        imagealphablending( $tmp_im, false );
        imagesavealpha( $tmp_im, true );

        $color = imagecolorallocate($tmp_im,255,255,255);
        imagefill($tmp_im,0,0,$color);
        imagecopyresampled($tmp_im,$im_image,0,0,0,0,$new_w,$new_h,$info[0],$info[1]);
        //只能使用  imagecopy imagecopyresampled   imagecopymerge无效
//        imagecopy($this->im, $tmp_im, $x1/100*$this->width,$y1/100*$this->height, 0, 0, $new_w, $new_h);
        imagecopyresampled($this->im,$tmp_im,$x1/100*$this->width,$y1/100*$this->height,0,0,$new_w,$new_h,$new_w,$new_h);
//        imagecopymerge($this->im,$tmp_im, $x1/100*$this->width,$y1/100*$this->height,0,0,$new_w,$new_h, 100);
        imagedestroy($im_image);
        imagedestroy($tmp_im);
        return true;
    }
    

    /** 划线
     * @param $x1 填充x起点的百分比 (0-100)
     * @param $y1 填充x起点的百分比 (0-100)
     * @param $x2 填充x起点的百分比 (0-100)
     * @param $y2 填充x起点的百分比 (0-100)
     * @param int $size 线条像素
     */
    public function mergeLine($x1,$y1,$x2,$y2,$size=1)
    {
        imagesetthickness($this->im,$size);
        imageline($this->im,$x1/100*$this->width,$y1/100*$this->height,$x2/100*$this->width,$y2/100*$this->height,$this->color);
    }

    /** 设置填充字体颜色
     * @param $r
     * @param $g
     * @param $b
     */
    public function setColor($r,$g,$b)
    {
        $this->color = imagecolorallocate($this->im,$r,$g,$b);
    }


    /**
     * 保存图片
     */
    public function save($path = '')
    {
        if(empty($path)) {
            imagejpeg($this->im,__DIR__.'/'.date('YmdHis').rand(10000,99999).'.jpeg',100);
        }else{
            imagejpeg($this->im,$path,100);
        }
    }

    /**
     * 展现在浏览器
     */
    public function showBrowse()
    {
        header('content-type:image/png');
        imagepng($this->im);
        die;
    }


    public function __destruct()
    {
        imagedestroy($this->im);
    }
}

