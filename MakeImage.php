<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:10
 * Name: 生成图片处理类
 */
class MakeImage
{

    private $title = null;

    private $rows = [];

    private $bottom = null;

    private $margin = 0;

    private $border = ImageCommon::DEFAULT_SIZE;

    private $height;

    private $width;

    private $bgColor;

    private $img = null;

    private $font = null;

    public function setTitle(Row $title)
    {
        if ($title instanceof Row) {

            $this->title = $title;
        }
    }

    public function addRow(Row $row)
    {
        if ($row instanceof Row) {
            $this->rows [] = $row;
        }
    }

    public function setBottom(Row $bottom)
    {
        if ($bottom instanceof Row) {
            $this->bottom = $bottom;
        }
    }

    public function setMargin($margin)
    {
        $this->margin = ImageCommon::getSize($margin);
    }

    public function setBorder($border)
    {
        $this->border = ImageCommon::getSize($border);
    }

    public function setBgColor($bgColor)
    {
        $this->bgColor = ImageCommon::getColor($bgColor);
    }

    public function setFont($font)
    {
        $this->font = ImageCommon::getText($font);
    }

    public function draw($path, $ucode)
    {
        $this->toRecalculateTheHeight();

        $this->width = $this->getWidth() + 2 * $this->border;
        $this->height = $this->getHeight() + 2 * $this->border;

        $this->img = imagecreatetruecolor($this->width, $this->height);

        $this->drawBg();

        $x = $this->border;
        $y = $this->border;

        $this->drawTitle($x, $y);
        $this->drawBody($x, $y);
        $this->drawBottom($x, $y);

        header("content-type:image/png");
        $time = mTime();

        $png = $path . $time . $ucode . '.png';

        $sys_p = TONG_ABSOLUTE_PATH . '/' . $png;

        chmod(TONG_ABSOLUTE_PATH, 0777);

        imagepng($this->img, $png);

        copy($png, $sys_p);

        imagedestroy($this->img);

        return substr($png, 1);
    }

    /**
     * 单元格高度重计算
     */
    private function toRecalculateTheHeight()
    {
        if ($this->title != null) {
            $rHeight = 0;
            foreach ($this->title->getCols() as $col) {
                $rHeight = max($this->title->getHeight(), $rHeight);

                if (!is_array($col->getText())) {
                    continue;
                }

                $fontHeight = ImageCommon::getTextHeight("请", $col->getFontSize(), $this->font);
                $nowHeight = $col->getHeight();
                $cHeight = $col->getHeight($fontHeight);

                $rHeight = max(array(
                    $cHeight, $rHeight, $nowHeight
                ));
            }

            foreach ($this->title->getCols() as $col) {
                $col->setHeight($rHeight);
            }
        }

        foreach ($this->rows as $row) {
            $rHeight = 0;
            foreach ($row->getCols() as $col) {
                $rHeight = max($col->getHeight(), $rHeight);

                if (!is_array($col->getText())) {
                    continue;
                }

                $fontHeight = ImageCommon::getTextHeight("请", $col->getFontSize(), $this->font);
                $nowHeight = $col->getHeight();
                $cHeight = $col->getHeight($fontHeight);

                $rHeight = max(array(
                    $cHeight, $rHeight, $nowHeight
                ));
            }

            foreach ($row->getCols() as $col) {
                $col->setHeight($rHeight);
            }
        }

        if ($this->bottom != null) {
            $rHeight = 0;
            foreach ($this->bottom->getCols() as $col) {
                $rHeight = max($this->bottom->getHeight(), $rHeight);

                if (!is_array($col->getText())) {
                    continue;
                }

                $fontHeight = ImageCommon::getTextHeight("请", $col->getFontSize(), $this->font);
                $nowHeight = $col->getHeight();
                $cHeight = $col->getHeight($fontHeight);

                $rHeight = max(array(
                    $cHeight, $rHeight, $nowHeight
                ));
            }

            foreach ($this->bottom->getCols() as $col) {
                $col->setHeight($rHeight);
            }
        }
    }

    /**
     * 画背景色
     */
    private function drawBg()
    {
        $bgColorData = ImageCommon::getColorArray($this->bgColor);
        $bgColor = imagecolorallocate($this->img, $bgColorData ['r'], $bgColorData ['g'], $bgColorData ['b']);
        imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $bgColor);
    }

    /**
     * 画标题头
     *
     * @param int $x
     *            标题头X轴偏移量
     * @param int $y
     *            标题头Y轴偏移量
     */
    private function drawTitle(&$x, &$y)
    {
        $this->drawRow($this->title, $x, $y);
    }

    /**
     * 画主体内容
     *
     * @param int $x
     *            主体内容X轴偏移量
     * @param int $y
     *            主体内容Y轴偏移量
     */
    private function drawBody(&$x, &$y)
    {
        foreach ($this->rows as $row) {
            $this->drawRow($row, $x, $y);
        }
    }

    /**
     * 画尾部
     *
     * @param int $x
     *            尾部X轴偏移量
     * @param int $y
     *            尾部Y轴偏移量
     */
    private function drawBottom(&$x, &$y)
    {
        $this->drawRow($this->bottom, $x, $y);
    }

    /**
     * 画单行
     *
     * @param Row $row
     *            行对象
     * @param int $x
     *            行X轴偏移量
     * @param int $y
     *            行Y轴偏移量
     */
    private function drawRow($row, &$x, &$y)
    {
        if (is_null($row) || !($row instanceof Row)) {
            return;
        }

        $cols = $row->getCols();
        $length = count($cols);

        $displacementY = 0;

        for ($i = 0; $i < $length; $i++) {
            $displacement = $this->drawRect($cols [$i], $x, $y);
            $displacementY = max($displacementY, $displacement ['y']);

            $x += $displacement ['x'] + $row->getMargin();
        }

        $x = $this->border;
        $y += $displacementY + $this->margin;
    }

    /**
     * 画单元格
     *
     * @param Col $col
     *            单元格对象
     * @param int $x
     *            单元格X轴偏移量
     * @param int $y
     *            单元格Y轴偏移量
     * @return int[] 偏移量
     */
    private function drawRect(Col $col, $x, $y)
    {
        $width = $col->getWidth();
        $height = $col->getHeight();

        // 偏移量返回值
        $displacement = [
            'x' => $width,
            'y' => $height
        ];

        // 起止结束坐标
        $xStart = $x;
        $yStart = $y;
        $xEnd = $xStart + $width;
        $yEnd = $yStart + $height;

        $borderSize = $col->getBorderSize();

        $fontSize = $col->getFontSize();

        $text = $col->getText();

        // 画外框
        $borderColorData = ImageCommon::getColorArray($col->getBorderColor());
        $borderColor = imagecolorallocate($this->img, $borderColorData ['r'], $borderColorData ['g'], $borderColorData ['b']);
        imagefilledrectangle($this->img, $xStart, $yStart, $xEnd, $yEnd, $borderColor);

        // 画内框
        $bgColorData = ImageCommon::getColorArray($col->getBgColor());
        $bgColor = imagecolorallocate($this->img, $bgColorData ['r'], $bgColorData ['g'], $bgColorData ['b']);
        imagefilledrectangle($this->img, $xStart + $borderSize, $yStart + $borderSize, $xEnd - $borderSize, $yEnd - $borderSize, $bgColor);

        // 输入文本
        $textColorData = ImageCommon::getColorArray($col->getColor());
        $textColor = imagecolorallocate($this->img, $textColorData ['r'], $textColorData ['g'], $textColorData ['b']);

        if (is_array($text)) {
            $tY = 0;
            $textsHeight = 0;

            Log::record("开始绘制多行文本框 => " . json_encode($text));

            $textBoxs = array();

            for ($i = 0; $i < count($text); $i++) {
                $textBox = ImageCommon::getTextSize($text[$i], $fontSize, $this->font);

                $textBoxs[] = $textBox;

                $textsHeight += $textBox['height'];
            }

            for ($i = 0; $i < count($text); $i++) {
                $t = $text[$i];

                $textBox = $textBoxs[$i];

                // 计算文本偏移位置
                $textX = $xStart + ($width - $textBox ['width']) / 2;
                $textY = $yStart + $tY + ($height - $textsHeight) / 2;

                $tY += $textBox ['height'];

                $data = array(
                    'x' => $textX,
                    'y' => $textY,
                    'w' => $textBox['width'],
                    'h' => $textBox['height'],
                );

                Log::record("绘制多行文本: " . $t . " => " . json_encode($data), Log::DEBUG);

                ImageTTFText($this->img, $fontSize, 0, $textX - $textBox ['x'], $textY - $textBox ['y'], $textColor, $this->font, $t);
            }
        } else {
            $textBox = ImageCommon::getTextSize($text, $fontSize, $this->font);

            // 计算文本偏移位置
            $textX = $xStart + ($width - $textBox ['width']) / 2;
            $textY = $yStart + ($height - $textBox ['height']) / 2;

            $data = array(
                'x' => $textX,
                'y' => $textY,
                'w' => $textBox['width'],
                'h' => $textBox['height'],
            );

            Log::record("绘制单行文本: " . $text . " => " . json_encode($data), Log::DEBUG);

            ImageTTFText($this->img, $fontSize, 0, $textX - $textBox ['x'], $textY - $textBox ['y'], $textColor, $this->font, $text);
        }

        return $displacement;
    }

    public function getWidth()
    {
        $width = 0;
        if ($this->title instanceof Row) {
            $width = $this->title->getWidth();
        }

        if ($this->bottom instanceof Row) {
            $width = max($width, $this->bottom->getWidth());
        }

        foreach ($this->rows as $row) {
            $width = max($width, $row->getWidth());
        }

        return $width;
    }

    public function getHeight()
    {
        $height = 0;
        if ($this->title instanceof Row) {
            $height += $this->title->getHeight();
        }

        foreach ($this->rows as $row) {
            $height += $this->margin;
            $height += $row->getHeight();
        }

        if ($this->bottom instanceof Row) {
            $height += $this->margin;
            $height += $this->bottom->getHeight();
        }

        return $height;
    }
}

class Row
{

    private $margin;

    private $cols = [];

    function __construct($margin = 1)
    {
        $this->margin = ImageCommon::getSize($margin);
    }

    public function addCol(Col $col)
    {
        if ($col instanceof Col) {
            $this->cols [] = $col;
        }
    }

    public function getMargin()
    {
        return $this->margin;
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function getWidth()
    {
        $width = 0;

        foreach ($this->cols as $col) {
            $width += $col->getWidth();
            $width += $this->margin;
        }

        return $width - $this->margin;
    }

    public function getHeight($fontHeight = null)
    {
        $height = 0;

        foreach ($this->cols as $col) {
            $height = max($height, $col->getHeight($fontHeight));
        }

        return $height;
    }

    public function __toString()
    {
        $colsString = '';
        $isFirst = true;
        foreach ($this->cols as $col) {
            $colsString .= ($isFirst ? '' : ', ') . $col;
            $isFirst = false;
        }

        return 'Row [margin=' . $this->margin . ', cols=[' . $colsString . ']]';
    }
}

class Col
{

    private $text;

    private $width;

    private $height;

    private $bgColor;

    private $borderColor;

    private $borderSize;

    private $fontSize;

    private $color;

    function __construct($text, $width, $height, $bgColor = ImageCommon::DEFAULT_COLOR, $borderColor = ImageCommon::DEFAULT_COLOR, $borderSize = ImageCommon::DEFAULT_SIZE, $fontSize = ImageCommon::DEFAULT_SIZE, $color = ImageCommon::DEFAULT_COLOR)
    {
        Log::record(is_array($text) ? "多行" : "单行", Log::DEBUG);

        $this->text = ImageCommon::getText($text);

        $this->width = ImageCommon::getSize($width);
        $this->height = ImageCommon::getSize($height);

        $this->bgColor = ImageCommon::getColor($bgColor);
        $this->borderColor = ImageCommon::getColor($borderColor);
        $this->borderSize = ImageCommon::getSize($borderSize);

        $this->fontSize = ImageCommon::getSize($fontSize);
        $this->color = ImageCommon::getColor($color);
    }

    public function getText()
    {
        return $this->text;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight($fontHeight = null)
    {
        if ($fontHeight != null && is_array($this->text) && intval($fontHeight) > 0) {
            return $this->height + (count($this->text) - 1) * $fontHeight;
        }

        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = ImageCommon::getSize($height);
        Log::record("设置高度->" . $this->height, Log::DEBUG);
    }

    public function getBgColor()
    {
        return $this->bgColor;
    }

    public function getBorderColor()
    {
        return $this->borderColor;
    }

    public function getBorderSize()
    {
        return $this->borderSize;
    }

    public function getFontSize()
    {
        return $this->fontSize;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function __toString()
    {
        return 'Col [color=' . $this->color . ', fontSize=' . $this->fontSize . ', text=' . $this->text . ', width=' . $this->width . ', height=' . $this->height . ', bgColor=' . $this->bgColor . ', borderColor=' . $this->borderColor . ', borderSize=' . $this->borderSize . ']';
    }
}

/**
 * 图片公共方法和常亮
 *
 * @author Administrator
 *
 */
class ImageCommon
{

    /**
     * 默认RGB颜色
     *
     * @var string RBG颜色字符串
     */
    const DEFAULT_COLOR = '#000000';

    /**
     * 默认尺寸
     *
     * @var integer 默认尺寸
     */
    const DEFAULT_SIZE = 20;

    /**
     * 获取颜色RGB信息
     *
     * @param string $color
     *            RGB代码
     * @return number[] RGB数组
     */
    public static function getColorArray($color)
    {
        if (!self::getColor($color)) {
            return [
                'r' => 0,
                'g' => 0,
                'b' => 0
            ];
        }

        return [
            'r' => hexdec(substr($color, 1, 2)),
            'g' => hexdec(substr($color, 3, 2)),
            'b' => hexdec(substr($color, 5, 2))
        ];
    }

    /**
     * 判断该字符串是否为RGB颜色字符串
     *
     * @param string $color
     *            待判断字符串
     * @return boolean 是否为RGB颜色字符串
     */
    public static function isColor($color)
    {
        return preg_match('/\#[0-9a-fA-F]{6}/i', $color);
    }

    /**
     * 判断该字符串是否为RGB颜色字符串，如果不为RGB颜色字符串则返回默认RGB颜色字符串
     *
     * @param string $color
     *            待验证字符串
     * @return string RGB颜色字符串
     */
    public static function getColor($color)
    {
        return self::isColor($color) ? $color : self::DEFAULT_COLOR;
    }

    /**
     * 获取有效尺寸
     *
     * @param int $size
     *            尺寸
     * @return int 有效尺寸
     */
    public static function getSize($size)
    {
        return is_int($size) ? abs($size) : self::DEFAULT_SIZE;
    }

    /**
     * 获取文本内容
     *
     * @param string $text
     *            输入内容
     * @return string 文本字符串
     */
    public static function getText($text)
    {
        Log::record(json_decode($text), Log::DEBUG);
        if (is_array($text)) {
            return $text;
        }

        if (!is_string($text)) {
            return '';
        }

        $nowText = trim($text);

        if (strlen($nowText) == 0) {
            return '';
        }

        return $nowText;
    }

    /**
     * 获取文本框区域大小
     *
     * @param string $text
     *            文本内容
     * @param int $fontSize
     *            字号
     * @param string $font
     *            字体文件路径
     * @throws Exception 当字体文件无法读取的时候抛出异常
     * @return number[] 高度/宽度/X偏移量/Y偏移量
     */
    public static function getTextSize($text, $fontSize, $font)
    {
        if (is_null($font) || strlen($font) == 0 || !is_file($font)) {
            throw new Exception ("font is must be file");
        }

        $textBox = ImageTTFBBox(self::getSize($fontSize), 0, $font, self::getText($text));

        // 文本区域框大小
        return [
            'width' => $textBox [2] - $textBox [0],
            'height' => $textBox [1] - $textBox [7],
            'x' => $textBox [6],
            'y' => $textBox [7]
        ];
    }

    /**
     * 获取文本高度
     * @param string $text
     *            文本内容
     * @param int $fontSize
     *            字号
     * @param string $font
     *            字体文件路径
     * @throws Exception 当字体文件无法读取的时候抛出异常
     * @return number 高度
     */
    public static function getTextHeight($text, $fontSize, $font)
    {
        return self::getTextSize($text, $fontSize, $font)['height'];
    }
}