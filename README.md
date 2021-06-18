# SpringView

#### 介绍
在PHP末法时代诞生的小创新视图模板引擎，综合了各类PHP开发框架及团队大量项目经验基础上，以符号体系为核心，取长补短，力求简洁易用，服务于高智工程师。

**本视图模板引擎主要特色如下：**
1. 采用最简单易记的符号构建模板语法，比如 {=$abc}、{?($a==1)}、{@($list)}、{#(header)} 等
2. 基于使用场景的最小化类库方法设计；
3. PHP文件缓存，并可以被 Opcache 预编译而运行提速；
4. 比其他模板对高智开发工程师更友好，增加开发效率；
5. 保持代码开源，方便自行改造适配不同开发框架。

#### 类库方法
1. public 方法
    + __construct($viewPath, $cachePath)  类库初始化
    + assign($spec, $value) 设置视图模板中的变量值
    + render($viewFile, $viewVars) 返回指定视图模板的输出内容
    + display($viewFile, $viewVars) 直接显示指定视图模板的输出内容
    + setLayout($layoutFile, $blockFlagForView) 使用指定的布局视图模板
    + setBlock($blockFlag, $viewFile) 设置布局视图模板中内容块的对应视图文件
    + setViewPath($viewDirectory) 设置视图模板文件所在目录
    + getViewPath() 返回视图模板文件所在目录
    + setCachePath($cacheDirectory) 设置视图模板缓存文件所在目录
    + getCachePath() 返回视图模板缓存文件所在目录
    + setCacheLifetime($cacheSeconds) 设置缓存文件的有效时间（秒）
    + getCacheLifetime() 返回缓存文件的有效时间设定值
    + cleanCache($viewFile) 删除指定视图模板的缓存文件
2. private 方法
    - getCacheFile($viewPathFile) 返回指定视图路径文件的缓存文件（若缓存失效则重新生成）
    - buildCacheFile($viewPathFile, $cachePathFile) 将指定视图模板转换为PHP代码缓存文件并保存
    - parseView($code) 分析处理指定模板代码，返回转换后的PHP代码
    - parseFileTags($code) 分析处理模板代码中的文件引用标签，返回经过转换的代码
    - parseLoopTags($code) 分析处理模板代码中的循环结构（含子循环）标签，返回经过转换的代码
    - parseIfTags($code) 分析处理模板代码中的条件判断结构标签，返回经过转换的代码
    - parseVariableTags($code) 分析处理模板代码中的变量（含表达式）输出标签，返回经过转换的代码
    - parseHtmlTags($code) 分析处理模板代码中的特定html模板标签，返回经过转换的代码
    - parseBlockTags($code) 分析处理模板代码中的内容区块标签，返回经过转换的代码
    - isValidChar($x) 判断指定字符是否PHP变量名允许使用的字符

#### 使用说明

1.  单类库文件( SpringView.class.php )可以在项目中直接 include/require 引入使用；
2.  兼容 php5, php7, php8 主流PHP版本；
3.  若需使用命名空间，工程师可以直接在类库文件中自行定义；
4.  可以继承或直接扩展类库，增加私有模板数据处理方法，并在模板中直接使用；
5.  注意：必须要正确设置视图文件所在路径和缓存使用路径才能正确。

#### 举例演示

```
<?php
    //引入 SpringView 类库
    require 'lib/SpringView.class.php';

    $viewPath = __DIR__;  //视图模板文件所在目录
    $viewCachePath = $viewPath . '/cache/';  //视图模板文件缓存目录

    //初始化类库
    $tpl = new SpringView($viewPath, $viewCachePath);

    //设置模板变量
    $tpl->assign('userid', 108);
    $tpl->assign('username', '大卫普拉斯');
    $tpl->assign('userGender', 1);
    $tpl->assign('userCountry', 88);

    //显示视图内容
    $tpl->display('view_simple');
?>
```

**更多功能演示及代码例子，请访问 [http://SpringView.SpringPHP.cn/](http://springview.springphp.cn/)**



#### 参与贡献

1.  Fork 本仓库
2.  新建 Feat_xxx 分支
3.  提交代码
4.  新建 Pull Request


#### 感谢

1.  感谢每个来到这里的**码匠**
2.  感恩送出 Star 的你
3.  祝福 PHP 不死