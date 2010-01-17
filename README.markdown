bundle-phu
==========

bundle-phu is a set of [Zend Framework][1] view helpers that automagically concatenate, minify, and gzip
your javascript and stylesheets into bundles. This reduces the number of HTTP requests to your servers as
well as your bandwidth.

bundle-phu is inspired by [bundle-fu][2] a [Ruby on Rails][3] equivalent.

### Before

    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/js/foo.js"></script>
    <script type="text/javascript" src="/js/bar.js"></script>
    <script type="text/javascript" src="/js/baz.js"></script>
    <link media="screen" type="text/css" href="/css/jquery.css" />
    <link media="screen" type="text/css" href="/css/foo.css" />
    <link media="screen" type="text/css" href="/css/bar.css" />
    <link media="screen" type="text/css" href="/css/baz.css" />

### After

    <script type="text/javascript" src="bundle_3f84da97fc873ca8371a8203fcdd8a82.css?1234567890"></script>
    <link type="text/css" src="bundle_3f84da97fc873ca8371a8203fcdd8a82.css?1234567890"></script>

Installation
------------

1. Place the BundlePhu directory somewhere in your include_path:

    your_project/
    |-- application
    |-- library
    |   `-- BundlePhu
    |-- public

2. Add the BundlePhu view helpers to your view's helper path, and configure the helpers:

This would typically be done inside your Bootstrap.php

    <?php
    class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {
        protected function $_initView()
        {
            $view = new Zend_View();
            $view->addHelperPath(
                PATH_PROJECT . '/library/BundlePhu/View/Helper',
                'BundlePhu_View_Helper'
            );

            $view->getHelper('BundleScript')
                ->setCacheDir(PATH_PROJECT . '/data/cache/js')
                ->setDocRoot(PATH_PROJECT . '/public')
                ->setUrlPrefix('/javascripts');

            $view->getHelper('BundleLink')
                ->setCacheDir(PATH_PROJECT . '/data/cache/css')
                ->setDocRoot(PATH_PROJECT . '/public')
                ->setUrlPrefix('/javascripts');

            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $viewRenderer->setView($view);
            return $view;
        }
    }

3.  Ensure your CacheDir is writable by the user your web server runs as
4.  Using either an Alias (apache) or location/alias (nginx) map the UrlPrefix to CacheDir.
    You can also do this using a symlink from your /public directory.
    e.g. /public/javascripts -> /../data/cache/js

Usage
-----

As both these helpers extend from the existing HeadScript and HeadLink helpers in [Zend Framework][1],
you can use them just as you do those.

    <? $this->bundleScript()->offsetSetFile(00, $this->baseUrl('/js/jquery.js')) ?>
    <? $this->bundleScript()->appendFile($this->baseUrl('/js/foo.js')) ?>

[1]: http://framework.zend.com/
[2]: http://code.google.com/p/bundle-fu/
[3]: http://rubyonrails.org/