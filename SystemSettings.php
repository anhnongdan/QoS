<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{

	/** @var QosLastMinuteUpdate */
	public $qosLastMinuteUpdate;

	/** @var QosApiAddress */
	public $qosApiAddress;

	protected function init()
	{
		$this->qosLastMinuteUpdate = $this->createQoSLastMinuteUpdate();
		$this->qosApiAddress    = $this->createQoSApiAddress();
	}

	private function createQoSLastMinuteUpdate()
	{
        return $this->makeSetting(
            'qosLastMinuteUpdate', $default = 5, FieldConfig::TYPE_INT,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('QoS_LastMinuteUpdate');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = Piwik::translate('QoS_LastMinuteUpdateDescription').". ".Piwik::translate('QoS_LastMinuteUpdateHelp');
            }
        );
	}

	private function createQoSApiAddress()
	{
        return $this->makeSetting(
            'qosApiAddress', $default = 'http://127.0.0.1:8080/api/v1', FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('QoS_ApiAddress');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = Piwik::translate('QoS_ApiAddressDescription').". ".Piwik::translate('QoS_ApiAddressHelp');
            }
        );
	}
}
