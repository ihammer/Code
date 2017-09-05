<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:24
 * Name: 图片等比例缩放
 */
class ImageSize
{
    /**
     * *
     *等比缩放
     * @param unknown_type $srcImage   源图片路径
     * @param unknown_type $toFile     目标图片路径
     * @param unknown_type $maxWidth   最大宽
     * @param unknown_type $maxHeight  最大高
     * @param unknown_type $imgQuality 图片质量
     * @return unknown
     */
    function resize($srcImage,$toFile,$maxWidth = 100,$maxHeight = 100,$imgQuality=100)
    {

        list($width, $height, $type, $attr) = getimagesize($srcImage);
        if($width < $maxWidth  || $height < $maxHeight) return ;
        switch ($type) {
            case 1: $img = imagecreatefromgif($srcImage); break;
            case 2: $img = imagecreatefromjpeg($srcImage); break;
            case 3: $img = imagecreatefrompng($srcImage); break;
        }
        $scale = min($maxWidth/$width, $maxHeight/$height); //求出绽放比例

        if($scale < 1) {
            $newWidth = floor($scale*$width);
            $newHeight = floor($scale*$height);
            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $newName = "";
            $toFile = preg_replace("/(.gif|.jpg|.jpeg|.png)/i","",$toFile);

            switch($type) {
                case 1: if(imagegif($newImg, "$toFile$newName.gif", $imgQuality))
                    return "$newName.gif"; break;
                case 2: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
                    return "$newName.jpg"; break;
                case 3: if(imagepng($newImg, "$toFile$newName.png", $imgQuality))
                    return "$newName.png"; break;
                default: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
                    return "$newName.jpg"; break;
            }
            imagedestroy($newImg);
        }
        imagedestroy($img);
        return false;
    }
}