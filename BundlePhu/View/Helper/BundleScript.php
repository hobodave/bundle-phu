<?php
/**
 * BundlePhu
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. This license can also be viewed
 * at http://hobodave.com/license.txt
 * 
 * @category    BundlePhu
 * @package     BundlePhu_View
 * @subpackage  Helper
 * @author      David Abdemoulaie <dave@hobodave.com>
 * @copyright   Copyright (c) 2010 David Abdemoulaie (http://hobodave.com/)
 * @license     http://hobodave.com/license.txt New BSD License
 */

/**
 * Helper for bundling of all included javascripts into a single file
 *
 * @category    BundlePhu
 * @package     BundlePhu_View
 * @subpackage  Helper
 * @author      David Abdemoulaie <dave@hobodave.com>
 * @copyright   Copyright (c) 2010 David Abdemoulaie (http://hobodave.com/)
 * @license     http://hobodave.com/license.txt New BSD License
 **/
class BundlePhu_View_Helper_BundleScript extends Zend_View_Helper_HeadScript
{
    /**
     * local Zend_View reference
     *
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'BundlePhu_View_Helper_BundleScript';

    /**
     * Local reference to $view->baseUrl()
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * Directory in which to write bundled javascript
     *
     * @var string
     */
    protected $_cacheDir;

    /**
     * Directory in which to look for js files
     *
     * @var string
     */
    protected $_docRoot;

    /**
     * Path the generated bundle is publicly accessible under
     *
     * @var string
     */
    protected $_urlPrefix = "/javascripts";

    /**
     * Use gzencode() ?
     *
     * @var bool
     */
    protected $_doGzip = false;

    /**
     * Gzip level passed to gzencode()
     *
     * @var string 0 through 9 only
     */
    protected $_gzipLevel = 1;

    /**
     * Encoding type passed to gzencode()
     *
     * @var int FORCE_GZIP|FORCE_DEFLATE
     */
    protected $_gzipEncoding = FORCE_GZIP;

    /**
     * Use minification?
     *
     * @var bool
     */
    protected $_doMinify = false;

    /**
     * External command used to minify javascript
     *
     * This command must write the bundled file to disk, STDOUT will be ignored.
     * The token ':filename'  must be present in command, this will be replaced
     * with the generated bundle name.
     *
     * @var string
     */
    protected $_minifyCommand;

    /**
     * Callback to minify javascript within PHP
     *
     * When defined, this will take precedence over the _minifyCommand.
     * Callback must accept a single string param which is the JS to be minified.
     * Callback must return a string which is the minified JS.
     *
     * @var callback
     */
    protected $_minifyCallback;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->_baseUrl = $this->view->baseUrl();
    }

    /**
     * Proxies to Zend_View_Helper_HeadScript::headScript()
     *
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function bundleScript()
    {
        return parent::headScript();
    }

    /**
     * Sets the cache dir
     *
     * This is where the bundled files are written.
     *
     * @param string $dir
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setCacheDir($dir)
    {
        $this->_cacheDir = $dir;
        return $this;
    }

    /**
     * DocRoot is the base directory on disk where the relative js files can be found.
     *
     * e.g.
     *
     * if $docRoot == '/var/www/foo' then '/js/foo.js' will be found in '/var/www/foo/js/foo.js'
     *
     * @param string $docRoot
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setDocRoot($docRoot)
    {
        $this->_docRoot = $docRoot;
        return $this;
    }

    /**
     * Sets the URL prefix used for the generated script tag
     *
     * e.g. if $urlPrefix == '/javascripts' then '/javascripts/bundle_123fdfc3fe8ba8.js'
     * will be the src for the script tag.
     *
     * @param string $prefix
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setUrlPrefix($prefix)
    {
        $this->_urlPrefix = $prefix;
        return $this;
    }

    /**
     * Command used to generate the minified output file
     *
     * The output of this command is not returned, it must write the output to
     * the generated filename for the bundle. The ':filename' token will be
     * replaced with the generated filename.
     *
     * @param string $command Must contain :filename token
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setMinifyCommand($command)
    {
        $this->_minifyCommand = $command;
        return $this;
    }

    /**
     * Sets the callback to be used for minification.
     *
     * The callback will be passed the raw JS as a single string parameter.
     *
     * A callback, if defined, will take precedence over a minifyCommand.
     *
     * @param callback $callback
     * @return BundlePhu_View_Helper_BundleScript
     **/
    public function setMinifyCallback($callback)
    {
        $this->_minifyCallback = $callback;
        return $this;
    }

    /**
     * Toggles whether to compress files.
     *
     * When turned on, both uncompressed and compressed versions will be generated.
     * The compressed filename will be identical, except with a .gz extension appended.
     *
     * Sample configurations:
     *
     * apache:
     *
     * Options +MultiViews
     * AddEncoding x-gzip .gz
     *
     * nginx:
     *
     * gzip_static on;
     *
     * @param bool $bool
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setUseGzip($bool)
    {
        $this->_doGzip = $bool;
        return $this;
    }

    /**
     * Toggles whether to generate minified files.
     *
     * Minification always occurs before compression.
     *
     * @param bool $bool
     * @return BundlePhu_View_Helper_BundleScript
     */
    public function setUseMinify($bool)
    {
        $this->_doMinify = $bool;
        return $this;
    }

    /**
     * Sets the level of compression to pass to gzencode()
     *
     * @param int $level
     * @return BundlePhu_View_Helper_BundleScript
     **/
    public function setGzipLevel($level)
    {
        $this->_gzipLevel = $level;
        return $this;
    }

    /**
     * Sets the encoding mode to be passed to gzencode()
     *
     * @param int $encodingMode FORCE_GZIP|FORCE_DEFLATE
     * @return BundlePhu_View_Helper_BundleScript
     **/
    public function setGzipEncoding($encodingMode)
    {
        $this->_gzipEncoding = $encodingMode;
        return $this;
    }

    /**
     * Iterates over scripts, concatenating, optionally minifying, 
     * optionally compressiong, and caching them.
     * 
     * This detects updates to the source javascripts using filemtime.
     * A file with an mtime more recent than the mtime of the cached bundle will
     * invalidate the cached bundle.
     * 
     * Modifications of captured scripts cannot be detected by this.
     * DONT USE DYNAMICALLY GENERATED CAPTURED SCRIPTS.
     * 
     * 
     *
     * @param string $indent 
     * @return void
     * @throws UnexpectedValueException if item has no src attribute or contains no captured source
     */
    public function toString($indent = null)
    {
        if (isset($_REQUEST['bundle_off'])) {
            return parent::toString($indent);
        }

        $this->getContainer()->ksort();

        $filelist = '';
        $mostrecent = 0;
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            if (isset($item->attributes['src'])) {
                $src = $item->attributes['src'];
                if ($this->_baseUrl && strpos($src, $this->_baseUrl) !== false) {
                    $src =  substr($src, strlen($this->_baseUrl));
                }

                $mtime = filemtime($this->_docRoot . $src);
                if ($mtime > $mostrecent) {
                    $mostrecent = $mtime;
                }
            } else if (!empty($item->source)) {
                // BEWARE: Cannot detect modification of captured scripts!
                $src = $item->source;
            } else {
                throw new UnexpectedValueException("Item has no src attribute nor captured source.");
            }
            $filelist .= $src;
        }

        $hash = md5($filelist);
        $cacheFile = "{$this->_cacheDir}/bundle_{$hash}.js";

        // suppress warning for file DNE
        $cacheTime = @filemtime($cacheFile);
        if (false === $cacheTime || $cacheTime < $mostrecent) {
            $data = $this->_getJsData();

            $this->_writeUncompressed($cacheFile, $data);

            if ($this->_doGzip) {
                $this->_writeCompressed($cacheFile, $data);
            }
            $cacheTime = filemtime($cacheFile);
        }

        $urlPath = "{$this->_baseUrl}/{$this->_urlPrefix}/bundle_{$hash}.js?{$cacheTime}";
        $ret = '<script type="text/javascript" src="' . $urlPath . '"></script>';
        return $ret;
    }

    /**
     * Iterates through the scripts and returning a concatenated result.
     *
     * Assumes the container is sorted prior to entry.
     *
     * @return string Concatenated javascripts
     */
    protected function _getJsData()
    {
        ob_start();
        foreach ($this as $item) {
            if (isset($item->attributes['src'])) {
                $src = $item->attributes['src'];
                if ($this->_baseUrl && strpos($src, $this->_baseUrl) !== false) {
                    $src =  substr($src, strlen($this->_baseUrl));
                }

                echo file_get_contents($this->_docRoot . $src), PHP_EOL;
            } else if (!empty($item->source)){
                echo $item->source, PHP_EOL;
            }
        }
        $data = ob_get_clean();
        return $data;
    }

    /**
     * Writes uncompressed bundle to disk
     *
     * @param string $cacheFile name of bundle file to write
     * @param string $data bundled JS data
     * @throws BadMethodCallException When neither _minifyCommand or _minifyCallback are defined
     * @return void
     */
    protected function _writeUncompressed($cacheFile, $data)
    {
        if ($this->_doMinify) {
            if (!empty($this->_minifyCallback)) {
                $data = call_user_func($this->_minifyCallback, $data);
                file_put_contents($cacheFile, $data);
            } else if (!empty($this->_minifyCommand)) {
                $command = str_replace(':filename', $cacheFile, $this->_minifyCommand);
                $handle = popen("$command" , 'w');
                fwrite($handle, $data);
                pclose($handle);
            } else {
                throw new BadMethodCallException("Neither _minifyCommand or _minifyCallback are defined.");
            }
        } else {
            file_put_contents($cacheFile, $data);
        }
    }

    /**
     * Writes compressed copy of data to disk
     *
     * $cacheFile will have the .gz extension automatically appended
     *
     * @param string $cacheFile name of bundle file to write
     * @param string $data bundled JS data
     * @return void
     */
    protected function _writeCompressed($cacheFile, $data)
    {
        $data = gzencode($data, $this->_gzipLevel);
        file_put_contents("$cacheFile.gz", $data);
    }
}