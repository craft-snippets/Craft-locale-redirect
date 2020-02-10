<?php
/**
 * Locale redirect plugin for Craft CMS 3.x
 *
 * Locale redirect
 *
 * @link      http://craftsnippets.com
 * @copyright Copyright (c) 2020 Piotr Pogorzelski
 */

namespace craftsnippets\localeredirect\models;


use craft\base\Model;

/**
 * @author    Piotr Pogorzelski
 * @package   LocaleRedirect
 * @since     1.0.0
 */
class Settings extends Model
{

    public $enableRedirect = true;
    public $redirectMapping = array();
    public $redirectToBaseUrlIfNoElement = false;
    public $disableRedirectParam = 'change-language';
    public $ignoreCrawlers = true;

}
