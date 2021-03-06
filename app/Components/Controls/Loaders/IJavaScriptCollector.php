<?php

namespace FKSDB\Application;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IJavaScriptCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerJSFile($file);

    /**
     *
     * @param string $code JS code taht should be inserted into the page
     * @param string $tag tag of the code for later reference
     * @deprecated Leads to eval for AJAX requests.
     */
    public function registerJSCode($code, $tag = null);

    /**
     * @param string $file path relative to webroot
     */
    public function unregisterJSFile($file);

    /**
     *
     * @param string $tag code tag to be removed
     */
    public function unregisterJSCode($tag);
}
