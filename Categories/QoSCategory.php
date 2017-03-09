<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QoS\Categories;

use Piwik\Category\Category;

class QoSCategory extends Category
{
    protected $id = 'QoS_QoS';
    protected $order = 10;
    protected $icon = 'icon-chart-bar';
}