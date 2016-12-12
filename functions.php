<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\QoS;

use Piwik\Piwik;
use DeviceDetector\Parser\OperatingSystem AS OperatingSystemParser;
use DeviceDetector\Parser\Device\DeviceParserAbstract AS DeviceParser;
use DeviceDetector\Parser\Client\Browser AS BrowserParser;

function getBrowserFamilyFullName($label)
{
    foreach (BrowserParser::getAvailableBrowserFamilies() as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function getBrowserFamilyLogo($label)
{
    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();
    if (!empty($label) && array_key_exists($label, $browserFamilies)) {
        return getBrowserLogo($browserFamilies[$label][0]);
    }
    return getBrowserLogo($label);
}

function getBrowserNameWithVersion($label)
{
    $short = substr($label, 0, 2);
    $ver = substr($label, 3, 10);
    $browsers = BrowserParser::getAvailableBrowsers();
    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]) . ' ' . $ver);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getBrowserName($label)
{
    $short = substr($label, 0, 2);
    $browsers = BrowserParser::getAvailableBrowsers();
    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]));
    } else {
        return Piwik::translate('General_Unknown');
    }
}

/**
 * Returns the path to the logo for the given browser
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the browser family
 * Return unknown logo otherwise
 *
 * @param string  $short  Shortcode or name of browser
 *
 * @return string  path to image
 */
function getBrowserLogo($short)
{
    $path = 'plugins/DevicesDetection/images/browsers/%s.gif';

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 2) {

        if (in_array($short, BrowserParser::getAvailableBrowsers())) {
            $flippedBrowsers = array_flip(BrowserParser::getAvailableBrowsers());
            $short = $flippedBrowsers[$short];
        } else {
            $short = substr($short, 0, 2);
        }
    }

    $family = getBrowserFamilyFullName($short);

    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();

    if (!empty($short) &&
        array_key_exists($short, BrowserParser::getAvailableBrowsers()) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $short))) {

        return sprintf($path, $short);

    } elseif (!empty($short) &&
        array_key_exists($family, $browserFamilies) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $browserFamilies[$family][0]))) {

        return sprintf($path, $browserFamilies[$family][0]);
    }
    return sprintf($path, 'UNK');
}
