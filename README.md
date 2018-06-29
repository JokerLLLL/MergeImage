# 简单图片合成类
直接引入MergeImge.php就可以直接使用了。
所以坐标都依据生成画布的大小为百分比开始，就是坐标在0-100之间。
字体文件最好为绝对路径。
调用showBrowse()直接可以在浏览器调试。
调用save()保存
```php
<?php
require_once './MergeImage.php';
//构建画布
$merge = new MergeImage(1000,1000);
//绘制网格线
for ($i=1;$i<10;$i++) {
    $merge->mergeLine(0,10*$i,100,10*$i);
    $merge->mergeLine(10*$i,0,10*$i,100);
}
//填充单句话
$merge->mergeFontSimple(50,90,25, __DIR__ . '/dir/black.ttf','神龙啊 吞噬我的敌人吧！！!');
$merge->mergeFontSimple(50,90,75, __DIR__ . '/dir/black.ttf','中心点',1);
//填充红色文字
$merge->setColor(255,0,0);
$content = "文章，中国大陆男演员、导演，满族人，出生于陕西西安，2006年毕业于中央戏剧学院表演系。2008年与女演员马伊琍结婚，两人育有两女。2011年在电视剧《裸婚时代》中出演男主角刘易阳，被誉为灯笼男。 维基百科文章，中国大陆男演员、导演，满族人";
$merge->mergeFontContent(10,10,50,50,20, __DIR__ . '/dir/black.ttf',$content,0.1);
//填充背景
$merge->setColor(165,56,179);
$merge->mergeBackColor(5,0,10,100);
//添加图片
$merge->mergeImage('https://www.baidu.com/img/bd_logo1.png',50,50,100,70);
//绘制多边形
$merge ->setColor(200,255,150);
$merge ->mergePolygon([40,40,50,20,60,40,80,50,60,60,50,80,40,60,20,50]);
//$merge->save(__DIR__.'/dir/1.jpeg'); 保存
$merge->showBrowse();




```