<?php

/**
 * Created by PhpStorm.
 * User: 用于导出excel表类
 * Date: 2017/9/5 0005
 * Time: 15:42
 */
class Excel
{

    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    private $footer = "</Workbook>";
    private $sEncoding = 'UTF-8';
    private $bConvertTypes = false;
    private $sWorksheetTitle;

    public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false, $sWorksheetTitle = 'Sheet1') {
        $this->bConvertTypes = $bConvertTypes;
        $this->setEncoding($sEncoding);
        $this->setWorksheetTitle($sWorksheetTitle);
    }

    public function setEncoding($sEncoding) {
        $this->sEncoding = $sEncoding;
    }

    public function setWorksheetTitle($title) {
        $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
        $title = substr($title, 0, 31);
        $this->sWorksheetTitle = $title;
    }

    public function generate($filename = 'excel-export', $array = []) {
        $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);

        header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        echo stripslashes(sprintf($this->header, $this->sEncoding));
        echo "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";

        foreach ($array as $key => $value) {
            $cells = "";
            foreach ($value as $k => $v) {
                $type = 'String';
                if ($this->bConvertTypes === true && is_numeric($v)) {
                    $type = 'Number';
                }
                $v = htmlentities($v, ENT_COMPAT, $this->sEncoding);
                $cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
            }
            echo "<Row>\n" . $cells . "</Row>\n";
        }

        echo "</Table>\n</Worksheet>\n";
        echo $this->footer;
    }

    /**
     * 生成excel表格
     *
     * @param array $title
     *            不带下标的数组
     * @param array $data
     *            不带下标的数据二维数组
     * @param string $file_name
     *            文件名,不需要带扩展名
     *
     * @return void
     */
    public function export($title, $data, $file_name) {
        // 获取客户当前系统的版本
        // 默认把文字编码由utf8转成gbk
        // 如果是windows则进行转换,否则不进行转换
        $conv = $this->get_os_info();
        if ($conv) {
            header("Content-type:application/vnd.ms-excel;charset=GBk");
        } else {
            header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        }
        header("Content-Disposition:attachment; filename=$file_name.xls");
        // 输出标题
        if (!empty($title) && is_array($title)) {
            foreach ($title as $value) {
                $this->exportXls($value, $conv);
            }
            $this->printEnter();
        }
        if (!empty($data) && is_array($data)) {
            foreach ($data as $item) {
                $list = is_array($item) ? array_values($item) : [];
                if (!empty($list)) {
                    foreach ($list as $row) {
                        $this->exportXls($row, $conv);
                    }
                }
                $this->printEnter();
            }
        }
        exit();
    }

    /**
     * 把字符串转换成gbk格式,并输出tab空格
     *
     * @param string $str
     */
    private function exportXls($str, $conv = true) {
        // chr(9) tab空格
        if ($conv) { // 需要转换
            echo mb_convert_encoding($str, 'gbk', 'UTF-8') . chr(9);
        } else { // 不需要转换
            echo $str . chr(9);
        }
    }

    /**
     * 输出回车/换行
     */
    private function printEnter() {
        echo chr(13);
    }

    /**
     * 获取客户当前操作系统版本,true表示windows系统,false表示非windows系统
     *
     * @access public
     * @return boolean true | false
     */
    private function get_os_info() {
        // 默认初始化为windows系统
        $return = true;
        $os = "";
        $Agent = $_SERVER["HTTP_USER_AGENT"];
        if (eregi('win', $Agent) && strpos($Agent, '95')) {
            $os = "Windows 95";
        } elseif (eregi('win 9x', $Agent) && strpos($Agent, '4.90')) {
            $os = "Windows ME";
        } elseif (eregi('win', $Agent) && ereg('98', $Agent)) {
            $os = "Windows 98";
        } elseif (eregi('win', $Agent) && eregi('nt 5.0', $Agent)) {
            $os = "Windows 2000";
        } elseif (eregi('win', $Agent) && eregi('nt', $Agent)) {
            $os = "Windows NT";
        } elseif (eregi('win', $Agent) && eregi('nt 5.1', $Agent)) {
            $os = "Windows XP";
        } elseif (eregi('win', $Agent) && ereg('32', $Agent)) {
            $os = "Windows 32";
        } elseif (eregi('linux', $Agent)) {
            $os = "Linux";
        } elseif (eregi('unix', $Agent)) {
            $os = "Unix";
        } elseif (eregi('sun', $Agent) && eregi('os', $Agent)) {
            $os = "SunOS";
        } elseif (eregi('ibm', $Agent) && eregi('os', $Agent)) {
            $os = "IBM OS/2";
        } elseif (eregi('Mac', $Agent) && eregi('PC', $Agent)) {
            $os = "Macintosh";
        } elseif (eregi('PowerPC', $Agent)) {
            $os = "PowerPC";
        } elseif (eregi('AIX', $Agent)) {
            $os = "AIX";
        } elseif (eregi('HPUX', $Agent)) {
            $os = "HPUX";
            if (eregi('NetBSD', $Agent)) {
                $os = "NetBSD";
            } elseif (eregi('BSD', $Agent)) {
                $os = "BSD";
            } elseif (ereg('OSF1', $Agent)) {
                $os = "OSF1";
            } elseif (ereg('IRIX', $Agent)) {
                $os = "IRIX";
            } elseif (eregi('FreeBSD', $Agent)) {
                $os = "FreeBSD";
            }
        }
        if (!empty($os)) {
            if (stristr($os, 'Win')) {
                $return = true;
            } else {
                $return = false;
            }
        }

        return $return;
    }
}
