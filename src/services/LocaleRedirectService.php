<?php
/**
 * Locale redirect plugin for Craft CMS 3.x
 *
 * Locale redirect
 *
 * @link      http://craftsnippets.com
 * @copyright Copyright (c) 2020 Piotr Pogorzelski
 */

namespace craftsnippets\localeredirect\services;

use craftsnippets\localeredirect\LocaleRedirect;

use Craft;
use craft\base\Component;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * @author    Piotr Pogorzelski
 * @package   LocaleRedirect
 * @since     1.0.0
 */
class LocaleRedirectService extends Component
{

    /**
     * @return bool
     * Try redirecting to user preferred language.
     */

    public function tryRedirect(){

        if(!$this->isAllowedRedirect()){
            return false;
        }

        $targetSite = $this->getTargetSite();
        if(is_null($targetSite)) {
            Craft::info('Redirect stopped - no site matching handle in mapping', LocaleRedirect::$plugin->name);
            return false;
        }elseif(Craft::$app->getSites()->currentSite->id == $targetSite->id){
            Craft::info('Redirect stopped - you are already on target site', LocaleRedirect::$plugin->name);
            return false;
        }else{
            Craft::info('Selected site with handle '.$targetSite->handle, LocaleRedirect::$plugin->name);
        }

        $targetUrl = $this->getTargetUrl($targetSite);
        if(is_null($targetUrl)){
            Craft::info('Redirect stopped - no proper element found', LocaleRedirect::$plugin->name);
            return false;
        }

        $this->performRedirect($targetUrl);

    }

    /**
     * @return bool
     * Check if redirect is allowed to occur
     */

    public function isAllowedRedirect(){

        $request = Craft::$app->getRequest();
        $allowed = false;

        if($request->isSiteRequest) {
            $allowed = true;
        }

        if($request->isCpRequest){
            Craft::info('Redirect stopped - control panel request', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if($request->isActionRequest) {
            Craft::info('Redirect stopped - action panel request', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if($request->isConsoleRequest) {
            Craft::info('Redirect stopped - console panel request', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if($request->isAjax) {
            Craft::info('Redirect stopped - ajax panel request', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if($request->isPreview) {
            Craft::info('Redirect stopped - preview panel request', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if(Craft::$app->user->checkPermission('accessCp')) {
            Craft::info('Redirect stopped - user has access to control panel', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        if(LocaleRedirect::$plugin->getSettings()->enableRedirect === false) {
            Craft::info('Redirect stopped - redirecting disabled in settings', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        $CrawlerDetect = new CrawlerDetect();
        if (LocaleRedirect::$plugin->getSettings()->ignoreCrawlers === false && $CrawlerDetect->isCrawler()) {
            Craft::info('Redirect stopped - crawler detected', LocaleRedirect::$plugin->name);
            return false;
        }

        $sitesMapping = LocaleRedirect::$plugin->getSettings()->sitesMapping;
        if(!is_array($sitesMapping) || empty($sitesMapping)){
            Craft::info('Redirect stopped - "sitesMapping" setting not defined or incorrect', LocaleRedirect::$plugin->name);
            $allowed = false;
        }

        // url param
        if(Craft::$app->getRequest()->getQueryParam(LocaleRedirect::$plugin->getSettings()->disableRedirectParam)){
            Craft::$app->getSession()->set('language_switched', true);
            Craft::info('Redirect stopped - GET parameter disabling switching in URL', LocaleRedirect::$plugin->name);
            $url = str_replace ('?'.LocaleRedirect::$plugin->getSettings()->disableRedirectParam.'=true', '', $request->absoluteUrl);
            header("Location: {$url}", true, 302);
            exit();
        }
        if(Craft::$app->getSession()->get('language_switched')){
            Craft::info('Redirect stopped - switching was disabled by URL parameter earlier', LocaleRedirect::$plugin->name);
            return false;
        }

        return $allowed;

    }

    /**
     * Get target site for user preferred language.
     */

    public function getTargetSite(){
        $sitesMapping = LocaleRedirect::$plugin->getSettings()->sitesMapping;
        $mappedLanguages = array_keys($sitesMapping);
        $preferredLanguage = Craft::$app->getRequest()->getPreferredLanguage($mappedLanguages);
        Craft::info('Preferred language is '.$preferredLanguage, LocaleRedirect::$plugin->name);
        $handle = $sitesMapping[$preferredLanguage];

        $targetSite = null;

        if(is_array($handle)){
            foreach($handle as $singleHandle){
                $singleSite = Craft::$app->sites->getSiteByHandle($singleHandle);
                if( !is_null($singleSite) && Craft::$app->getSites()->currentSite->getGroup()->id == $singleSite->getGroup()->id){
                    $targetSite = $singleSite;
                }
            }
        }else{
            $singleSite = Craft::$app->sites->getSiteByHandle($handle);
            if(!is_null($singleSite) && Craft::$app->getSites()->currentSite->getGroup()->id == $singleSite->getGroup()->id){
                $targetSite = Craft::$app->sites->getSiteByHandle($handle);
            }
        }

        return $targetSite;
    }

    /**
     * Get target URL for redirect.
     */

    public function getTargetUrl($targetSite, $forceRedirectToBase = false){
        $currentElement = Craft::$app->urlManager->getMatchedElement();
        if($currentElement !== false){
            $targetElement = Craft::$app->elements->getElementById($currentElement->getId(), null, $targetSite->id);
        }else{
            $targetElement = null;
        }

        $targetUrl = null;

        if(!is_null($targetElement) && $targetElement->getEnabledForSite($targetSite->id)){
            Craft::info('Will redirect to matching element.', LocaleRedirect::$plugin->name);
            $targetUrl = $targetElement->url;
        }elseif(LocaleRedirect::$plugin->getSettings()->redirectToBaseUrlIfNoElement === true || $forceRedirectToBase === true){
            Craft::info('Will redirect to base URL of site.', LocaleRedirect::$plugin->name);
            $targetUrl = $targetSite->getBaseUrl();
        }

        return $targetUrl;
    }

    /**
     * Perform redirect
     */

    public function performRedirect($url){
        Craft::info('Redirecing to URL '.$url, LocaleRedirect::$plugin->name);
        header("Location: {$url}", true, 302);
        exit();
    }

    public function getLanguageSwitcherLinks(){
        $currentSite = Craft::$app->getSites()->currentSite->getGroup();
        $sites = Craft::$app->getSites()->getGroupById($currentSite->id)->getSites();
        $links = array();
        foreach($sites as $singleSite){
            $linkTitle = Craft::$app->i18n->getLocaleById($singleSite->language)->nativeName;
            $links[] = [
                'url' =>  $this->getTargetUrl($singleSite, true),
                'title' => $linkTitle,
                'language' => $singleSite->language,
                'current' => $singleSite->id == $currentSite->id ? true : false,
            ];
        }
        return $links;
    }


}
