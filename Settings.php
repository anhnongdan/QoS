<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;
use Piwik\Piwik;
use Piwik\Settings\SystemSetting;

/**
 * Defines Settings.
 */
class Settings extends \Piwik\Plugin\Settings
{

	/** @var QosLastMinuteUpdate */
	public $qosLastMinuteUpdate;

	/** @var QosApiAddress */
	public $qosApiAddress;

	protected function init()
	{
		$this->setIntroduction(Piwik::translate('QoS_SettingIntro'));

		$this->createQoSLastMinuteUpdate();
		$this->createQoSApiAddress();
	}

	private function createQoSLastMinuteUpdate()
	{
		$this->qosLastMinuteUpdate        = new SystemSetting('qosLastMinuteUpdate', Piwik::translate('QoS_LastMinuteUpdate') );
		$this->qosLastMinuteUpdate->type  = static::TYPE_INT;
		$this->qosLastMinuteUpdate->uiControlType = static::CONTROL_TEXT;
		$this->qosLastMinuteUpdate->description     = Piwik::translate('QoS_LastMinuteUpdateDescription');
		$this->qosLastMinuteUpdate->inlineHelp      = Piwik::translate('QoS_LastMinuteUpdateHelp');
		$this->qosLastMinuteUpdate->defaultValue    = 5;
		$this->qosLastMinuteUpdate->readableByCurrentUser = !Piwik::isUserIsAnonymous();;

		$this->addSetting($this->qosLastMinuteUpdate);
	}

	private function createQoSApiAddress()
	{
		$this->qosApiAddress        = new SystemSetting('qosApiAddress', Piwik::translate('QoS_ApiAddress') );
		$this->qosApiAddress->type  = static::TYPE_STRING;
		$this->qosApiAddress->uiControlType = static::CONTROL_TEXT;
		$this->qosApiAddress->description     = Piwik::translate('QoS_ApiAddressDescription');
		$this->qosApiAddress->inlineHelp      = Piwik::translate('QoS_ApiAddressHelp');
		$this->qosApiAddress->defaultValue    = 'http://127.0.0.1:8080/api/v1';
		$this->qosApiAddress->readableByCurrentUser    = !Piwik::isUserIsAnonymous();;

		$this->addSetting($this->qosApiAddress);
	}
}
