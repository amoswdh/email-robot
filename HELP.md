# Use

- config 目录新建 api.php;放入以下内容

``` php
<?php
return [
    'log_path' => '/export/logs/support.orderplus.com/',
];
```

- 发布

``` php
C:/phpStudy/PHPTutorial/php/php-7.2.1-nts/php.exe artisan vendor:publish
```