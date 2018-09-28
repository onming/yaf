<?php

// 公共助手函数

/**
 * 快速调试使用
 *
 * @param mixed	$var 需打印的变量
 */
function d($var)
{
    var_dump($var);
}

/**
 * 快速调试使用（带die）
 *
 * @param mixed	$var 需打印的变量
 */
function dd($var)
{
    var_dump($var);die();
}

/**
 * 将字节转换为可读文本
 *
 * @param int $size 大小
 * @param string $delimiter 分隔符
 * @return string
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 6; $i++)
        $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}


/**
 * 将时间戳转换为日期时间
 *
 * @param int $time 时间戳
 * @param string $format 日期时间格式
 * @return string
 */
function datetime($time, $format = 'Y-m-d H:i:s')
{
    $time = is_numeric($time) ? $time : strtotime($time);
    return date($format, $time);
}

/**
 * 判断文件或文件夹是否可写
 *
 * @param    string $file 文件或目录
 * @return    bool
 */
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR === '/') {
        return is_writable($file);
    }
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/' . md5(mt_rand());
        if (($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        @chmod($file, 0777);
        @unlink($file);
        return TRUE;
    } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
        return FALSE;
    }
    fclose($fp);
    return TRUE;
}

/**
 * 删除文件夹
 * @param string $dirname 目录
 * @param bool $withself 是否删除自身
 * @return boolean
 */
function rmdirs($dirname, $withself = true)
{
    if (!is_dir($dirname))
        return false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    if ($withself) {
        @rmdir($dirname);
    }
    return true;
}

/**
 * 复制文件夹
 * @param string $source 源文件夹
 * @param string $dest 目标文件夹
 */
function copydirs($source, $dest)
{
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            $sontDir = $dest . DS . $iterator->getSubPathName();
            if (!is_dir($sontDir)) {
                mkdir($sontDir, 0755, true);
            }
        } else {
            copy($item, $dest . DS . $iterator->getSubPathName());
        }
    }
}

/**
 * 返回打印数组结构
 *
 * @param string $var 数组
 * @param string $indent 缩进字符
 * @return string
 */
function var_export_short($var, $indent = "")
{
    switch (gettype($var)) {
        case "string":
            return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
        case "array":
            $indexed = array_keys($var) === range(0, count($var) - 1);
            $r = [];
            foreach ($var as $key => $value) {
                $r[] = "$indent    "
                    . ($indexed ? "" : var_export_short($key) . " => ")
                    . var_export_short($value, "$indent    ");
            }
            return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
        case "boolean":
            return $var ? "TRUE" : "FALSE";
        default:
            return var_export($var, TRUE);
    }
}

/***
 * 获取客户端ip
 *
 * @return bool
 */
function get_client_ip()
{
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }
        for ($i = 0; $i < count($ips); $i++) {
            preg_match("/^(10│172.16│192.168)./i", $ips[$i], $matchs);
            if (empty($matchs)) {
                $ip = $ips[$i];
                break;
            }
        }
    }

    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/**
 * 判断是否线上环境（匹配10.0|172.16|192.168号段为非线上环境）
 *
 * @param bool $exit 是否exit
 * @return bool
 */
function is_online_env($exit = false)
{
    if(filter_var(get_client_ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
        if($exit){
            exit();
        }else{
            return true;
        }
    }else{
        return false;
    }
}

/**
 * 使用CURL方式发送post请求
 *
 * @param  string   $url     请求地址
 * @param  array    $data    提交数据
 * @param  int      $timeout 设置超时秒数
 * @return array
 */
function curl_post_data($url, $data, $timeout = 10)
{
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_POST, 1);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);//post提交方式
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); //处理http证书问题
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    $exec = curl_exec($ch);
    $result = json_decode($exec, true);
    if(empty($result)){
        $result = [];
        $result['curl_exec'] = strip_tags($exec);
        $result['curl_info'] = curl_getinfo($ch);
        $result['curl_error'] = curl_errno($ch);
    }
    curl_close($ch);
    return $result;
}
