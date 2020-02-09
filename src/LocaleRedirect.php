<?php
/**
 * Locale redirect plugin for Craft CMS 3.x
 *
 * Locale redirect
 *
 * @link      http://craftsnippets.com
 * @copyright Copyright (c) 2020 Piotr Pogorzelski
 */

namespace craftsnippets\localeredirect;

use craftsnippets\localeredirect\services\LocaleRedirectService as LocaleRedirectServiceService;
use craftsnippets\localeredirect\variables\LocaleRedirectVariable;
use craftsnippets\localeredirect\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Class LocaleRedirect
 *
 * @author    Piotr Pogorzelski
 * @package   LocaleRedirect
 * @since     1.0.0
 *
 * @property  LocaleRedirectServiceService $localeRedirectService
 */
class LocaleRedirect extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var LocaleRedirect
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;


        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {

                $redirectService = new LocaleRedirectServiceService();
                $redirectService->tryRedirect();

            }
        );



        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('localeRedirect', LocaleRedirectVariable::class);
            }
        );


        Craft::info(
            Craft::t(
                'locale-redirect',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }


}
