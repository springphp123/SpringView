<?php
/**
 * Spring View Template Engine
 * new creative and powerfull template engine for smart projects.
 * this templating engine has three main features:
 * 1. innovation comes from captivating symbols
 * 2. minimal design based on rich experience
 * 3. high development efficiency for geeker
 *
 * @version:  1.2 <2021-05-25 15:00>
 * @author:   james zhang <james@springphp.com>
 * @license:  Apache Licence 2.0
 */

class SpringView
{
    /**
     * template parammeters
     * @var array
     */
    public $viewVars = array();

    /**
     * template error
     * @var string
     */
    public $error = '';

    /**
     * template path
     * @var string
     */
    protected $viewPath = '';

    /**
     * template file extension
     * @var string
     */
    protected $viewExt = 'html';

    /**
     * cached file path
     * @var string
     */
    protected $cachePath = '';

    /**
     * cached file extension
     * @var string
     */
    protected $cacheExt = 'php';

    /**
     * cached file lifetime
     * @var integer(s)
     */
    protected $cacheLifetime = 300;

    /**
     * layout view file
     * @var string
     */
    protected $layout = '';

    /**
     * block flags in layout
     * @var array
     */
    protected $blocks = array();

    /**
     * Constructor
     *
     * @param   string      $viewPath
     * @param   string      $cachePath
     * @return  void
     */
    final public function __construct($viewPath = null, $cachePath = null)
    {
        if (null !== $viewPath) {
            $this->setViewPath($viewPath);
        }
        if (null !== $cachePath) {
            $this->setCachePath($cachePath);
        }
    }

    /**
     * Assign variables to the view template
     *
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     *
     * @param   string|array    $spec   The assignment strategy to use
     * @param   mixed           $value  (Optional)
     * @return  void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            foreach ($spec as $k => $v) {
                $this->viewVars[$k] = $v;
            }
        }
        else if (null !== $value) {
            $this->viewVars[$spec] = $value;
        }
    }

    /**
     * Process the template and returns rendered content.
     *
     * @param   string          $viewFile
     * @param   array           $viewVars  (Optional)
     * @return  string
     */
    public function render($viewFile, $viewVars = null)
    {
        $viewRendered = $this->error = '';
        $viewPathFile = $this->viewPath . $viewFile . '.'. $this->viewExt;
        if (file_exists($viewPathFile)) {
            $cacheFile = $this->getCacheFile($viewPathFile);
            if ($cacheFile != '') {
                //extract view variables
                extract($this->viewVars, EXTR_SKIP);
                if (is_array($viewVars)) { extract($viewVars, EXTR_OVERWRITE); }

                //process layout
                if ($this->layout != '') {
                    $_blocks = array();
                    foreach ($this->blocks as $blockFlag => $blockFile) {
                        if ($blockFlag == $blockFile) { //default view
                            $blockFile = $cacheFile;
                        }
                        ob_start();
                        require $blockFile;
                        $_blocks[$blockFlag] = ob_get_clean();
                    }
                    $cacheFile = $this->layout;
                }
                //render view/layout
                ob_start();
                require $cacheFile;
                $viewRendered = ob_get_clean(); //ob_get_contents() + ob_end_clean()
            }
        } else {
            $this->error = 'the view "' . $viewFile . '" does not exist';
        }
        return $viewRendered;
    }

    /**
     * Process the template and display the rendered content.
     *
     * @param   string          $viewFile
     * @param   array           $viewVars  (Optional)
     * @return  string
     */
    public function display($viewFile, $viewVars = null)
    {
        echo $this->render($viewFile, $viewVars);
    }

    /**
     * Set the layout template for view.
     *
     * @param   string          $layoutFile
     * @param   string          $blockFlagForView  (the flag in layout will be replace with rendered view content)
     * @return  string
     */
    public function setLayout($layoutFile, $blockFlagForView = 'MAIN')
    {
        if (empty($layoutFile) || empty($blockFlagForView)) {
            $this->error = 'wrong layout parameters';
        }
        else if (!file_exists($layoutPathFile = $this->viewPath . $layoutFile . '.'. $this->viewExt)) {
            $this->error = 'layout file does not exist';
        }
        else if ('' != ($cacheFile = $this->getCacheFile($layoutPathFile))) {
            $this->layout = $cacheFile;
            $this->blocks[$blockFlagForView] = $blockFlagForView;
            return true;
        }
        return false;
    }

    /**
     * Assign a view to the block in layout.
     * this function is used when there are multiple blocks in a layout.
     *
     * @param   string          $viewFile
     * @param   array           $viewVars  (Optional)
     * @return  string
     */
    public function setBlock($blockFlag, $viewFile)
    {
        if (empty($blockFlag) || empty($viewFile)) {
            $this->error = 'wrong block parameters';
        }
        else if (!file_exists($viewPathFile = $this->viewPath . $viewFile . '.'. $this->viewExt)) {
            $this->error = 'block view "' . $viewFile . '" does not exist';
        }
        else if ('' != ($cacheFile = $this->getCacheFile($viewPathFile))) {
            $this->blocks[$blockFlag] = $cacheFile;
            return true;
        }
        return false;
    }

    /**
     * set the path of view templates.
     *
     * @param   string          $viewDirectory
     * @return  boolean
     */
    public function setViewPath($viewDirectory)
    {
        if (!is_dir($viewDirectory)) {
            $this->error = 'incorrect directory path of view';
        }
        else if (!is_readable($viewDirectory)) {
            $this->error = 'incorrect permissions for view path';
        }
        else {
            if (DIRECTORY_SEPARATOR === substr($viewDirectory, -1)) {
                $this->viewPath = $viewDirectory;
            } else {
                $this->viewPath = $viewDirectory . DIRECTORY_SEPARATOR;
            }
            return true;
        }
        return false;
    }

    /**
     * return the path of view templates.
     *
     * @param   void
     * @return  string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * set the path of cache files.
     *
     * @param   string          $cacheDirectory
     * @return  boolean
     */
    public function setCachePath($cacheDirectory)
    {
        if (!is_dir($cacheDirectory)) {
            $this->error = 'incorrect directory path of cache';
        }
        else if (!is_writable($cacheDirectory)) {
            $this->error = 'incorrect permissions for cache path';
        }
        else {
            if (DIRECTORY_SEPARATOR === substr($cacheDirectory, -1)) {
                $this->cachePath = $cacheDirectory;
            } else {
                $this->cachePath = $cacheDirectory . DIRECTORY_SEPARATOR;
            }
            return true;
        }
        return false;
    }

    /**
     * return the path of cache files.
     *
     * @param   void
     * @return  string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * set the lifetime of cache files.
     *
     * @param   integer         $cacheSeconds
     * @return  boolean
     */
    public function setCacheLifetime($cacheSeconds)
    {
        if (!is_int($cacheSeconds)) {
            $this->error = 'wrong parameter type';
        }
        else if ($cacheSeconds < 0) {
            $this->error = 'incorrect parameter value';
        }
        else {
            $this->cacheLifetime = $cacheSeconds;
            return true;
        }
        return false;
    }

    /**
     * return the value of cache lifetime.
     *
     * @param   void
     * @return  integer
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * Delete the view's cache file.
     *
     * @param   string          $viewFile
     * @return  boolean
     */
    public function cleanCache($viewFile)
    {
        $success        = false;
        $viewPathFile   = $this->viewPath . $viewFile . '.'. $this->viewExt;
        $cachePathFile  = $this->cachePath . md5($viewPathFile) . '.' . $this->cacheExt;
        if (empty($viewFile) || !file_exists($viewPathFile)) {
            $this->error = 'the view "' . $viewFile . '" does not exist';
        }
        else if (!file_exists($cachePathFile) || unlink($cachePathFile)) {
            $success = true;
        }
        else {
            $this->error = 'failed to delete the cache file of view "' . $viewFile . '"';
        }
        return $success;
    }

    /*--------------------------- private functions ---------------------------*/

    /**
     * Return filename of the view cache, build new cache if invalid
     *
     * @param   string          $viewPathFile
     * @return  boolean
     */
    private function getCacheFile($viewPathFile)
    {
        if ($this->cachePath != '') {
            $cacheFile = $this->cachePath . md5($viewPathFile) . '.' . $this->cacheExt;
            if (!file_exists($cacheFile)) {
                $cacheValid = false;
            }
            else if ($this->cacheLifetime == 0) {
                $cacheValid = true;
            }
            else {
                $cacheLast = filemtime($cacheFile);
                if ($cacheLast > filemtime($viewPathFile) || $this->cacheLifetime >= (time() - $cacheLast)) {
                    $cacheValid = true;
                } else {
                    @unlink($cacheFile);
                    $cacheValid = false;
                }
            }
            if ($cacheValid) {
                return $cacheFile;
            }
            else if ($this->buildCacheFile($viewPathFile, $cacheFile)) {
                return $cacheFile;
            }
        } else {
            $this->error = 'not set cache path correctly';
        }
        return '';
    }

    /**
     * Translate view template and save script code to cache file.
     *
     * @param   string          $viewPathFile
     * @param   string          $cachePathFile
     * @return  boolean
     */
    private function buildCacheFile($viewPathFile, $cachePathFile)
    {
        $code = file_get_contents($viewPathFile);
        if ($code === false) {
            $this->error = 'cannot read view file';
        }
        else if (empty($code)) {
            $this->error = 'view code is empty';
        }
        else {
            $code = $this->parseView($code);
            if ($code == '' || $this->error != '') {    //occurred error during process
                return false;
            }
            else if (file_put_contents($cachePathFile, $code)) {
                return true;
            } else {
                $this->error = 'save cache file failed';
            }
        }
        return false;
    }

    /**
     * Parse template code and translate into php scripts.
     *
     * @param   string          $code
     * @return  string
     */
    private function parseView($code)
    {
        $this->error = '';
        if (!empty($code)) {
            if ($this->error == '') {
                $code = $this->parseFileTags($code);        //embedded files
            }
            if ($this->error == '') {
                $code = $this->parseLoopTags($code);        //loop syntax
            }
            if ($this->error == '') {
                $code = $this->parseIfTags($code);          //if construct
            }
            if ($this->error == '') {
                $code = $this->parseVariableTags($code);    //display variables
            }
            if ($this->error == '') {
                $code = $this->parseHtmlTags($code);        //customized html tags
            }
            if ($this->error == '') {
                $code = $this->parseBlockTags($code);       //block tags
            }
        } else {
            $this->error = 'view code is empty';
        }
        if ($this->error != '') {
            return '';
        }
        return $code;
    }

    /**
     * Parse embedded file tags.
     * {#(global/header)} => require("/view_cache_path/md5_view.php");
     * 1. find and parse the embedded html file
     * 2. render and save cache file
     * 3. require the cache file
     *
     * @param   string          view code
     * @return  string
     */
    private function parseFileTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, '{#('))) {
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 3);
            $poz  = strpos($code, ')}');
            $tag  = substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            if ($poz > 1) {
                $tag = trim($tag);
                $tag = trim($tag, "\"'");
                $vpf = $this->viewPath . $tag . '.' . $this->viewExt;
                if (file_exists($vpf)) {
                    $vcf = $this->getCacheFile($vpf);
                    if ($vcf != '') {
                        $part.= '<?php require("' . $vcf . '"); ?>';
                    }
                } else {
                    $this->error = 'embedded file does not exist';
                    break;
                }
            } else {
                $this->error = 'incorrect embedded filename';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part == '') {
            return $code;
        }
        return $part . $code;
    }

    /**
     * Parse loop tags, max level is 2!
     * {@($data)}...{/@} => <?php foreach ($data as $_i => $_row) { ... } ?>
     * Internal variable: $_first/$_last/$_odd/$_total/$_no/$_key/$_row
     * {=@.name} => <?php echo $_row['name']; ?>
     * {=$gender[@.sex]} => <?php echo $gender[$_row['sex']]; ?>
     * {?(@.age<18)}... => <?php if($_row['age']<18){ ?>...
     *
     * sub loop:
     * {@@}...{/@@} => <?php foreach ($_row as $__key => $__row) { ... } ?>
     * {@@.orders}...{/@@} => <?php foreach ($_row['orders'] as $__key => $__row) { ... } ?>
     * {@@($types)}...{/@@} => <?php foreach ($types as $__key => $__row) { ... } ?>
     * Internal variable: $__first/$__last/$__odd/$__total/$__no/$__key/$__row
     * {=@:name} => <?php echo $__row['name']; ?>
     * {=$gender[@:sex]} => <?php echo $gender[$__row['sex']]; ?>
     * {?(@:age<18)}... => <?php if($__row['age']<18){ ?>...
     *
     * @param   string          view code
     * @return  string
     */
    private function parseLoopTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, '{@('))) { //has loop
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 3);
            $poz  = strpos($code, ')}');
            $tag  = substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            $tag  = trim($tag);
            if ($poz > 1 && '$' == $tag{0}) {
                $part.= '<?php ' . "if(is_array({$tag})&&!empty({$tag})){\$_total=count({$tag});\$_no=1;foreach({$tag} as " . '$_key=>$_row){';
                $part.= 'if($_no==1){$_first=1;}elseif($_no==$_total){$_last=1;}$_odd=(1==($_no%2)); ?>';
                $poz  = strpos($code, '{/@}');
                $loop = substr($code, 0, $poz);
                $code = substr($code, $poz + 4);
                $seg  = '';
                while (false !== ($poz = strpos($loop, '{@@'))) { //sub loop
                    $seg .= substr($loop, 0, $poz);
                    $loop = substr($loop, $poz + 3);
                    $poz  = strpos($loop, '}');
                    $tag  = substr($loop, 0, $poz);
                    $loop = substr($loop, $poz + 1);
                    $tag  = trim($tag);
                    if (empty($tag)) { //{@@}
                        $tag = '$_row';
                    }
                    else if ('.' == substr($tag, 0, 1)) { //{@@.orders}
                        $tag = '$_row["' . substr($tag, 1) . '"]';
                    }
                    else if ('(' == substr($tag, 0, 1)) { //{@@($types)}
                        $tag = trim($tag, "()");
                    }
                    else {
                        $this->error = 'incorrect variable name of sub loop tag "{@@...}"';
                        break 2;
                    }
                    $seg .= '<?php ' . "if(is_array({$tag})&&!empty({$tag})){\$__total=count({$tag});\$__no=1;foreach({$tag} as " . '$__key=>$__row){';
                    $seg .= 'if($__no==1){$__first=1;}elseif($__no==$__total){$__last=1;}$__odd=(1==($__no%2)); ?>';
                    $poz  = strpos($loop, '{/@@}');
                    $unit = substr($loop, 0, $poz);
                    $loop = substr($loop, $poz + 5);
                    while (false !== ($poz = strpos($unit, '@:'))) { //sub loop vars
                        $tag  = substr($unit, $poz + 2);
                        $len  = strlen($tag);
                        for ($i = 0; $i < $len; $i++) {
                            if ($this->isValidChar($tag{$i})) {
                                continue;
                            }
                            break;
                        }
                        $tag  = substr($tag, 0, $i);
                        if (!empty($tag)) {
                            $unit = str_replace("@:{$tag}", '$__row["' . $tag . '"]', $unit);
                        } else {
                            //$unit = substr($unit, 0, $poz) . substr($unit, $poz + 2);
                            $this->error = 'incorrect name of sub loop tag "{@:name}"';
                            break 3;
                        }
                    }
                    $unit = str_replace('{@@!}', '<?php } }else{ { ?>', $unit);
                    $seg .= $unit . '<?php $__no++; }} ?>';
                }
                if ($seg != '') {
                    $loop = $seg . $loop;
                }
                while (false !== ($poz = strpos($loop, '@.'))) { //loop vars
                    $tag  = substr($loop, $poz + 2);
                    $len  = strlen($tag);
                    for ($i = 0; $i < $len; $i++) {
                        if ($this->isValidChar($tag{$i})) {
                            continue;
                        }
                        break;
                    }
                    $tag  = substr($tag, 0, $i);
                    if (!empty($tag)) {
                        $loop = str_replace("@.{$tag}", '$_row["' . $tag . '"]', $loop);
                    } else {
                        //$loop = substr($loop, 0, $poz) . substr($loop, $poz + 2);
                        $this->error = 'incorrect name of loop tag "{@.name}"';
                        break 2;
                    }
                }
                $loop  = str_replace('{@!}', '<?php } }else{ { ?>', $loop);
                $part .= $loop . '<?php $_no++; }} ?>';
            } else {
                $this->error = 'incorrect variable name of loop tag "{@($var_name)}"';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part == '') {
            return $code;
        }
        return $part . $code;
    }

    /**
     * Parse conditional execution tag "IF".
     * {?($age == 18)}...   => if ($age == 18) { ...
     * {??($age < 18)}...   => } else if ($age < 18) { ...
     * {?!}                 => } else {
     * {/?}                 => }
     *
     * @param   string          view code
     * @return  string
     */
    private function parseIfTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, '{?('))) { //if(...)
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 3);
            $poz  = strpos($code, ')}');
            $tag  = substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            $tag  = trim($tag);
            if ($poz > 1) {
                $part.= '<?php if(' . $tag . '){ ?>';
            } else {
                $this->error = 'missing expression of tag "{?(...)}';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part != '') {
            $code = $part . $code;
            $part = '';
        }
        while (false !== ($poz = strpos($code, '{??('))) { //elseif(...)
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 4);
            $poz  = strpos($code, ')}');
            $tag  = substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            $tag  = trim($tag);
            if ($poz > 1) {
                $part.= '<?php }else if(' . $tag. '){ ?>';
            } else {
                $this->error = 'missing expression of tag "{??(...)}';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part != '') {
            $code = $part . $code;
        }
        $part = str_replace('{?!}', '<?php }else{ ?>', $code); //else
        $part = str_replace('{/?}', '<?php } ?>', $part); //endif
        return $part;
    }

    /**
     * Parse variable tags.
     * {=$abc}                      => echo $abc;
     * {=$abc|'success'}            => echo $abc??'success';
     * {=G.name|'default_value'}    => echo $_GET['name'];
     * {=P.name|'default_value'}    => echo $_POST['name'];
     * {=:rmb($val, 2)}             => echo $this->rmb($val, 2);
     * {=($a+$b)|3.14}              => echo ($a+$b);
     * {=number_format($val, 2)}    => echo number_format($val, 2);
     *
     * @param   string          view code
     * @return  string
     */
    private function parseVariableTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, '{='))) {
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            $poz  = strpos($code, '}');
            $tag  = ltrim(substr($code, 0, $poz));
            $code = substr($code, $poz + 1);
            if ($poz <= 1) {
                $this->error = 'incorrect name of variable tag "{=...}"';
                break;
            }
            else if ('$' == substr($tag, 0, 1)) {   //{=$abc|'success'}
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                if (isset($arr[1])) {
                    $part.= '<?php if(empty(' . $tag . ')){echo "' . trim($arr[1], " '\"") . '";}else{echo ' . $tag . ';}  ?>';
                } else {
                    $part.= '<?php if(isset(' . $tag . ')){echo ' . $tag . ';} ?>';
                }
            }
            else if ('G.' == substr($tag, 0, 2)) {  //{=G.name|'default_value'}
                $tag = substr($tag, 2);
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                if (isset($arr[1])) {
                    $part.= '<?php if(empty($_GET["' . $tag . '"])){echo "' . trim($arr[1], " '\"") . '";}else{echo htmlspecialchars($_GET["' . $tag . '"]);}  ?>';
                } else {
                    $part.= '<?php if(isset($_GET["' . $tag . '"])){echo htmlspecialchars($_GET["' . $tag . '"]);} ?>';
                }
            }
            else if ('P.' == substr($tag, 0, 2)) {  //{=P.name|'default_value'}
                $tag = substr($tag, 2);
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                if (isset($arr[1])) {
                    $part.= '<?php if(empty($_POST["' . $tag . '"])){echo "' . trim($arr[1], " '\"") . '";}else{echo htmlspecialchars($_POST["' . $tag . '"]);}  ?>';
                } else {
                    $part.= '<?php if(isset($_POST["' . $tag . '"])){echo htmlspecialchars($_POST["' . $tag . '"]);} ?>';
                }
            }
            else if (':' == substr($tag, 0, 1)) {   //{=:rmb($val, 2)}
                $tag = substr($tag, 1);
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                $poz = strpos($tag, '(');
                $fun = rtrim(substr($tag, 0, $poz));
                if (empty($fun) || !method_exists($this, $fun)) {   //if internal method does not exist
                    if (isset($arr[1])) {                           //display default value if assigned
                        $part.= '<?php echo "' . trim($arr[1], " '\"") . '";  ?>';
                    }
                }
                else if (isset($arr[1])) {
                    $part.= '<?php $_val=$this->' . $tag . ';if(empty($_val)){echo "' . trim($arr[1], " '\"") . '";}  ?>';
                } else {
                    $part.= '<?php echo $this->' . $tag . '; ?>';
                }
            }
            else if ('(' == substr($tag, 0, 1)) {   //{=($a+$b)|3.14}
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                if (2 > strpos($tag, ')')) {
                    $tag = '"' . $tag . '"';
                }
                if (isset($arr[1])) {
                    $part.= '<?php $_val=' . $tag . ';if(empty($_val)){echo "' . trim($arr[1], " '\"") . '";}  ?>';
                } else {
                    $part.= '<?php echo ' . $tag . '; ?>';
                }
            }
            else {                                  //{=number_format($val, 2)}
                $arr = explode('|', $tag);
                $tag = trim($arr[0]);
                $poz = strpos($tag, '(');
                $fun = rtrim(substr($tag, 0, $poz));
                if (empty($fun) || !function_exists($fun)) {        //if the function does not exist
                    if (isset($arr[1])) {                           //display default value if assigned
                        $part.= '<?php echo "' . trim($arr[1], " '\"") . '";  ?>';
                    } else {                                        //or output tag string
                        $part.= '<?php echo "' . trim($tag, " '\"") . '"; ?>';
                    }
                }
                else if (isset($arr[1])) {
                    $part.= '<?php $_val=' . $tag . ';if(empty($_val)){echo "' . trim($arr[1], " '\"") . '";}  ?>';
                } else {
                    $part.= '<?php echo ' . $tag . '; ?>';
                }
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part == '') {
            return $code;
        }
        return $part . $code;
    }

    /**
     * Parse customized html tags.
     * var_username             => echo $username;
     * php_disabled="$online"   => if ($online) {echo 'disabled="disabled";}
     * php_checked="$val==2"    => if ($val==2) {echo 'checked="checked";}
     * php_selected="$age<18"   => if ($age<18) {echo 'selected="selected";}
     * php_readonly="$agc<18"   => if ($agc<18) {echo 'readonly="readonly";}
     *
     * @param   string          view code
     * @return  string
     */
    private function parseHtmlTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, 'var_'))) { //var_name
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 4);
            $len  = strlen($code);
            for ($i = 0; $i < $len; $i++) {
                if ($this->isValidChar($code{$i})) {
                    continue;
                }
                break;
            }
            if ($i >= 1) {
                $part.= '<?php echo $' . substr($code, 0, $i) . '; ?>';
                $code = substr($code, $i);
            } else {
                $this->error = 'incorrect format of html tag "var_name"';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part != '') {
            $code = $part . $code;
            $part = '';
        }
        $elms = array('disabled', 'checked', 'selected', 'readonly');
        foreach ($elms as $elmt) {
            while (false !== ($poz = strpos($code, "php_{$elmt}="))) {
                $part.= substr($code, 0, $poz);
                $code = ltrim(substr($code, $poz + 5 + strlen($elmt)));
                $tag  = substr($code, 0, 1);
                $code = substr($code, 1);
                if ('"' == $tag) {
                    $poz  = strpos($code, '"');
                }
                else if ("'" == $tag) {
                    $poz  = strpos($code, "'");
                }
                else {
                    $poz  = 0;
                }
                if (2 > $poz) {
                    $this->error = 'incorrect format of html tag "php_' . $elmt . '"';
                    break 2;
                }

                $tag  = substr($code, 0, $poz);
                $part.= '<?php if(' . $tag . '){echo \'' . $elmt . '="' . $elmt . '"\';} ?>';
                $code = substr($code, $poz + 1);
            }
            if ($part != '') {
                $code = $part . $code;
                $part = '';
            }
        }
        if ($this->error != '') {
            return '';
        }
        $code = str_replace('<!--{', '<?php ', $code);
        $code = str_replace('}-->', ' ?>', $code);
        return $code;
    }

    /**
     * Parse block flag tags.
     * {&(MAIN)} => if(isset($_blocks["MAIN"])){echo $_blocks["MAIN"];}
     *
     * @param   string          view code
     * @return  string
     */
    private function parseBlockTags($code)
    {
        $part = '';
        while (false !== ($poz = strpos($code, '{&('))) { //{&(MAIN)}
            $part.= substr($code, 0, $poz);
            $code = substr($code, $poz + 3);
            $poz  = strpos($code, ')}');
            $tag  = substr($code, 0, $poz);
            $code = substr($code, $poz + 2);
            if ($poz > 1) {
                $tag  = trim($tag);
                $tag  = trim($tag, '\'"');
                $part.= '<?php if(isset($_blocks["' . $tag . '"])){echo $_blocks["' . $tag . '"];} ?>';
            } else {
                $this->error = 'missing block flag of tag "{&(...)}';
                break;
            }
        }
        if ($this->error != '') {
            return '';
        }
        if ($part == '') {
            return $code;
        }
        return $part . $code;
    }

    /**
     * A valid variable name starts with a letter or underscore, followed by any number of letters, numbers, or underscores.
     * As a regular expression, it would be expressed thus: ^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$
     * Note: a letter is a-z, A-Z, and the bytes from 128 through 255 (0x80-0xff).
     *
     * @param   string          character
     * @return  boolean
     */
    private function isValidChar($x)
    {
        $val = ord($x);
        if ($val >= 65 && $val <= 90 || $val >= 97 && $val <= 122 || $val >= 48 && $val <= 57 || $val == 95) {
            return true;
        }
        return false;
    }


}
