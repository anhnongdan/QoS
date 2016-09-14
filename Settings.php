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

	/** @var QoSSetting */
	public $qosSettings;

    /** @var httpCode */
	public $httpCode;

	protected function init()
	{
		$this->setIntroduction(Piwik::translate('QoS_SettingIntro'));

		$this->createQoSSetting();
        $this->createHttpCodeSetting();
	}

	private function createQoSSetting()
	{
		$this->qosSettings        = new SystemSetting('qosSettings', Piwik::translate('QoS_SettingLabel') );
		$this->qosSettings->type  = static::TYPE_STRING;
		$this->qosSettings->uiControlType = static::CONTROL_TEXT;
		$this->qosSettings->description     = Piwik::translate('QoS_SettingDescription');
		$this->qosSettings->inlineHelp      = Piwik::translate('QoS_ServerSettingHelp');
		$this->qosSettings->defaultValue    = false;

		$this->addSetting($this->qosSettings);
	}

    private function createHttpCodeSetting()
    {
        $this->httpCode        = new SystemSetting('httpCode', 'Metrics Http Code');
        $this->httpCode->type  = static::TYPE_ARRAY;
        $this->httpCode->uiControlType = static::CONTROL_MULTI_SELECT;
        $this->httpCode->availableValues  = array('request_count_200' => 'http code 200', 'request_count_204' => 'http code 200', 'request_count_206' => 'http code 206');
        $this->httpCode->description   = 'The value will be only displayed in the following http code 2xx';
        $this->httpCode->defaultValue  = array('request_count_200','request_count_204','request_count_206');
        $this->httpCode->readableByCurrentUser = true;

        $this->addSetting($this->httpCode);
    }
}
