<?php
defined('In718Shop') or exit('Access Invalid!');
class zipfileModel extends Model
{
    /**
     * 下载文件
     * @param $img
     * @return string
     */

    public function Download($img)
    {
        $items = [];
        $names = [];
        if ($img) {
            //用于前端跳转zip链接拼接
            $path_redirect = '/zip/' . date('Ymd');
            //临时文件存储地址
            $path = '/tmp' . $path_redirect;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            foreach ($img as $key => $value) {
                $fileContent = '';
                $fileContent = $this->CurlDownload($value['url']);
                if ($fileContent) {
                    $__tmp = $this->SaveFile($value['url'], $path, $fileContent);
                    $items[] = $__tmp[0];
                    $tmpname = $value['name'] . '.' . $__tmp[1];
                    if(in_array($tmpname,$names)){
                        $names[] = $value['name'] . '_' . ($key + 1) . '.' . $__tmp[1];
                    }else{
                        $names[] = $value['name'] . '.' . $__tmp[1];
                    }
                }
            }
            if ($items) {
                $zip = new ZipArchive();
                $filename = time() . 'download.zip';
                $zipname = $path . '/' . $filename;
                if (!file_exists($zipname)) {
                    $res = $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    if ($res) {
                        foreach ($items as $k => $v) {
                            $value = explode("/", $v);
                            $end = end($value);
                            $zip->addFile($v, $end);
                            $zip->renameName($end, $names[$k]);
                        }
                        $zip->close();
                    } else {
                        return '';
                    }
                    //通过前端js跳转zip地址下载,让不使用php代码下载zip文件
                    //if (file_exists($zipname)) {
                    //拼接附件地址
                    //$redirect = 域名.$path_redirect.'/'.$filename;
                    //return $redirect;
                    //header("Location:".$redirect);
                    //}
                    //直接写文件的方式下载到客户端
                    if (file_exists($zipname)) {
                        set_time_limit(0);
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header('Content-disposition: attachment; filename='.date('Y-m-d',time()).'.zip'); //文件名
                        header("Content-Type: application/zip"); //zip格式的
                        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
                        header('Content-Length: ' . filesize($zipname)); //告诉浏览器，文件大小

                        // 打开文件
                        $fp = fopen($zipname, 'rb');
                        $filesize = filesize($zipname);
                        ob_clean();
                        ob_end_flush();
                        while (!feof($fp)) {
                            echo fread($fp, $filesize);
                            ob_flush(); // 刷新PHP缓冲区到Web服务器
                            flush(); // 刷新Web服务器缓冲区到浏览器
                        }
                        fclose($fp);
                        @readfile($zipname);
                    }
                    //删除临时文件
                    @unlink($zipname);
                }
            }
            return '';
        }
    }

    /**
     * curl获取链接内容
     * @param $url
     * @return mixed|string
     */

    public function CurlDownload($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $res = curl_exec($ch);
        curl_close($ch);

        if ($errno > 0) {
            return '';
        }
        return $res;
    }

    /**
     * 保存临时文件
     * @param $url
     * @param $dir
     * @param $content
     * @return array
     */

    public function SaveFile($url, $dir, $content)
    {
        $fname = basename($url); //返回路径中的文件名部分
        $str_name = pathinfo($fname); //以数组的形式返回文件路径的信息
        $extname = strtolower($str_name['extension']); //把扩展名转换成小写
        $path = $dir . '/' . md5($url) . $extname;
        $fp = fopen($path, 'w+');
        fwrite($fp, $content);
        fclose($fp);
        return array($path, $extname);
    }
}