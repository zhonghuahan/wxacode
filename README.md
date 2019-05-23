# wxacode
微信小程序二维码插件，以及授权登陆

WXACODE插件 ThinkPHP开发拿来即用
===============

## 安装

* 在目录`extend/wxacode`下，粘贴到该文件目录下。 并创建文件夹：`wxacode`, 并赋权: `chmod -R 777 wxacode`
```
   test
    |- extend
        |- wxacode
            |- wxacode.php // 封装库
    ...
    |- wxcode   // 保存生成的二维码
```

## 使用

* 选择不同的场景（默认生成B场景二维码）
```
    <!-- 生成 A场景二维码 -->
    $wx->data($data, 'A');

    <!-- 生成 B场景二维码 -->
    $wx->data($data, 'B')

    <!-- 生成 C场景二维码 -->
    $wx->data($data, 'C');
```

* 自定义二维码名称，及保存路径
```
    $wx = new wxacode($config);

    $wx->data($data)->save('自定义名称', '自定义路径')->getFileName();

    // 仅自定义路径
    $wx->setPath('自定义路径')->data($data)->save()->getFileName();

    // 默认存储路径：根目录/wxacode/Ymd/xxxx.png
    $wx->data($data)->save()->getFileName();
```

* 生成二维码：
```
    use wxacode\wxacode;

    $config = [
        'appid' => '你的小程序APPID',
        'secret' => '你的小程序秘钥'
    ];

    $wx = new wxacode($config);

    $qrcode = $wx->data($data)->save()->getFileName();

    // 生成二维码路径：wxacode/20190520/131420520.png
    echo '<img src="'.$qrcode.'">";

    $qrname = $wx->data($data)->save()->getName();

    echo $qrname; // 只获取二维码名称：131420520.png
    die;

```

* 小程序授权登陆，获取`OpenID`
```
    use wxacode\wxacode;

    $config = [
        'appid' => '你的小程序APPID',
        'secret' => '你的小程序秘钥'
    ];

    $wx = new wxacode($config);

    $ret = $wx->setCode($code)->getOpenID();

    dump($ret);
    // array('openid'=>'...', 'session_key'=>'...', 'unionid'=>'...')
```
