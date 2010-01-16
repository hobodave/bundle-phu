## Hobo_View_Helper_BundleScript

### Usage

Place the Hobo directory somewhere in your include_path.

Add it to your Zend_View helper path:
    
    $view->addHelperPath(
        APPLICATION_PATH . 'library/Hobo/View/Helper',
        'Hobo_View_Helper'
    );
    
Grab it from your View and configure it appropriately:

    $view->getHelper('BundleScript')
        ->setCacheDir(PATH_PROJECT . '/data/cache/js')
        ->setDocRoot(PATH_PROJECT . '/public')
        ->setUrlPrefix('/javascripts');

With a typical ZF app this should all be done in your Bootstrap, inside an _initView method