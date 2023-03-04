<?php
/**
 * PHP代码查杀工具
 *
 * 输入需要扫描的目录，即可扫描该目录下的所有文件，查找是否有危险的函数、可疑的操作行为等
 *
 * 支持的危险函数：exec、system、passthru、shell_exec、popen、proc_open、eval、assert
 *
 * 扫描结果以表格形式展示，支持导出扫描结果为CSV文件
 *
 * By ChatGPT x AVANS TEAM
 */

// 设置错误报告级别
error_reporting(E_ALL & ~E_NOTICE);

// 检查PHP版本
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('本工具需要PHP版本不小于5.3.0，当前PHP版本为' . PHP_VERSION);
}

// 定义可疑函数
$suspicious_functions = array('exec', 'system', 'passthru', 'shell_exec', 'popen', 'proc_open', 'eval', 'assert');

// 获取当前脚本所在路径
$current_dir = dirname(__FILE__);

// 初始化
$scan_dir = '';
$result = array();
$csv_data = '';



// 扫描目录
function scan_directory($dir, &$result) {
    global $suspicious_functions, $csv_data;
    // 打开目录
    $handle = opendir($dir);
    if ($handle) {
        // 循环读取目录下的文件
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                $file_path = $dir . DIRECTORY_SEPARATOR . $file;
                // 如果是目录则递归扫描
                if (is_dir($file_path)) {
                    scan_directory($file_path, $result);
                } else {
                    // 检查文件类型是否为PHP
                    if (substr($file_path, -4) == '.php') {
                        // 打开文件
                        $content = file_get_contents($file_path);
                        // 检查文件内容是否包含可疑函数
                        foreach ($suspicious_functions as $function) {
                            if (stripos($content, $function) !== false) {
                                                        $result[] = array(
                                'file' => $file_path,
                                'function' => $function,
                            );
                            $csv_data .= '"' . $file_path . '","' . $function . '"' . PHP_EOL;
                        }
                    }
                }
            }
        }
    }
    closedir($handle);
}
}



// 处理表单提交
if (isset($_POST['submit'])) {
$scan_dir = isset($_POST['scan_dir']) ? $_POST['scan_dir'] : '';
if (!empty($scan_dir)) {
// 扫描目录并存储结果
scan_directory($scan_dir, $result);
}
}

// 输出结果
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Backdoor Scanner</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .form-control{
            
            width: 70%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            display:inline;
        }
        
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn {
                display: inline-block;
                vertical-align: baseline;
            }
            

    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-5"><strong>PHP Backdoor Scanner</strong> <font style="font-size:20px">V1.0.1</font></h1>
    <form method="post">
        <div class="form-group">
            <strong>扫描目录：</strong>
            <input  class="form-control" name="scan_dir" value="<?php echo dirname(__FILE__); ?>">
            <input type="submit" name="submit"  class="btn btn-primary" value="开始扫描">
            <?php if (!empty($result)): ?>
                <a href="data:application/csv;charset=utf-8,<?php echo urlencode($csv_data); ?>" download="<?php echo date('Y-m-d H:i:s',time());?>扫描结果(<?php echo $scan_dir; ?>).csv" class="btn btn-primary">下载CSV文件</a>
            <?php endif; ?>
        </div>
    </form>
    
   
    <?php if (!empty($result)): ?>
        <h2>扫描结果：</h2>
        <table class="mb-12">
            <thead>
                <tr>
                    <th class="mb-8">文件路径</th>
                    <th class="mb-4">可疑函数</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo $row['file']; ?></td>
                        <td><?php echo $row['function']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php endif; ?>
</div>
    <footer>
        <div style="text-align:center;margin:100px 0px 20px 0px">
            注：本程序初始模型为CHATGPT生成，其他代码均为安全研究人员开发，未经授权禁止将本程序代码进行商业或用于非法用途<br>
            By <strong>ChatGPT</strong> x <strong>AVANS TEAM</strong>
        </div>
    </footer>
</body>
</html>
