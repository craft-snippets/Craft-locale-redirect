<?php
/**
 * Locale redirect plugin for Craft CMS 3.x
 *
 * Locale redirect
 *
 * @link      http://craftsnippets.com
 * @copyright Copyright (c) 2020 Piotr Pogorzelski
 */

namespace craftsnippets\localeredirect\variables;

use craftsnippets\localeredirect\LocaleRedirect;
use craftsnippets\localeredirect\services\LocaleRedirectService as LocaleRedirectServiceService;
use craft\web\View;

use Craft;
use craft\helpers\Template as TemplateHelper;


/**
 * @author    Piotr Pogorzelski
 * @package   LocaleRedirect
 * @since     1.0.0
 */
class LocaleRedirectVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param null $optional
     * @return string
     */
    public function getLanguageSwitcherWidget(){
        $redirectService = new LocaleRedirectServiceService();

        $oldMode = \Craft::$app->view->getTemplateMode();
        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = \Craft::$app->view->renderTemplate('locale-redirect/language_switcher',
            [
                'links' => $redirectService->getLanguageSwitcherLinks(),
            ]);
        \Craft::$app->view->setTemplateMode($oldMode);

        return TemplateHelper::raw($html);
    }

    public function getLanguageSwitcherLinks(){
        $redirectService = new LocaleRedirectServiceService();
        return $redirectService->getLanguageSwitcherLinks();
    }
}
