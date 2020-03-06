<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
class Control extends IPSModule
{
    use VariableProfileHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('Prefix', 'iotlink');
        $this->RegisterPropertyString('DomainName', 'workgroup');
        $this->RegisterPropertyString('Computername', '');

        $this->RegisterProfileString('IOT.People', 'People');

        $this->RegisterProfileIntegerEx('IOT.Control', 'Power', '', '', [
            [0, $this->translate('Shutdown'),  '', -1],
            [1, $this->translate('Reboot'),  '', -1],
            [2, $this->translate('Hibernate'),  '', -1],
            [3, $this->translate('Suspend'),  '', -1],
        ]);

        $this->RegisterProfileIntegerEx('IOT.Playback', 'Remote', '', '', [
            [0, $this->translate('Back'),  '', -1],
            [1, $this->translate('Play / Pause'),  '', -1],
            [2, $this->translate('Stop'),  '', -1],
            [3, $this->translate('Next'),  '', -1],
        ]);

        $this->RegisterProfileIntegerEx('IOT.Display', 'TV', '', '', [
            [0, $this->translate('Off'),  '', 0xFF0000],
            [1, $this->translate('On'),  '', 0x3ADF00],
        ]);

        $this->RegisterVariableInteger('control', $this->Translate('Control'), 'IOT.Control', 1);
        $this->RegisterVariableInteger('display', $this->Translate('Display'), 'IOT.Display', 2);
        $this->RegisterVariableInteger('playback', $this->Translate('Playback'), 'IOT.Playback', 3);
        $this->RegisterVariableInteger('volume', $this->Translate('Volume'), '~Intensity.100', 4);
        $this->RegisterVariableBoolean('mute', $this->Translate('Mute'), '~Switch', 5);
        $this->RegisterVariableString('userlock', $this->Translate('Lock User'), 'IOT.People', 6);
        $this->RegisterVariableString('userlogoff', $this->Translate('Logoff User'), 'IOT.People', 7);

        $this->SetValue('control', -1);
        $this->SetValue('display', -1);
        $this->SetValue('playback', -1);

        $this->EnableAction('control');
        $this->EnableAction('display');
        $this->EnableAction('playback');
        $this->EnableAction('volume');
        $this->EnableAction('mute');
        $this->EnableAction('userlock');
        $this->EnableAction('userlogoff');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $topic = $this->ReadPropertyString('Prefix') . '/' . $this->ReadPropertyString('DomainName') . '/' . $this->ReadPropertyString('Computername');
        $this->SetReceiveDataFilter('.*' . $topic . '.*');
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);

        if (!empty($this->ReadPropertyString('Computername'))) {
            $data = json_decode($JSONString);
            switch ($data->DataID) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data->Buffer);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
            }
            if (fnmatch('*windows-monitor/stats/media/volume', $Buffer->Topic)) {
                $this->SetValue('volume', $Buffer->Payload);
            }

            if (fnmatch('*windows-monitor/stats/media/muted', $Buffer->Topic)) { // Payload gibt immer False zurück ... Bug?
                switch ($Buffer->Payload) {
                    case 'False':
                        $this->SetValue('mute', false);
                        break;
                    case 'True':
                        $this->SetValue('mute', true);
                        break;
                }
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        $topic = $this->ReadPropertyString('Prefix') . '/' . $this->ReadPropertyString('DomainName') . '/' . $this->ReadPropertyString('Computername') . '/commands/';
        switch ($Ident) {
            case 'control':
                switch ($Value) {
                    case 0:
                        $this->sendMQTT($topic . 'shutdown', '');
                        break;
                    case 1:
                        $this->sendMQTT($topic . 'reboot', '');
                        break;
                    case 2:
                        $this->sendMQTT($topic . 'hibernate', '');
                        break;
                    case 3:
                        $this->sendMQTT($topic . 'suspend', '');
                        break;
                }
                break;
            case 'playback':
                switch ($Value) {
                    case 0:
                        $this->sendMQTT($topic . 'media/previous', '');
                        break;
                    case 1:
                        $this->sendMQTT($topic . 'media/playpause', '');
                        break;
                    case 2:
                        $this->sendMQTT($topic . 'media/stop', '');
                        break;
                    case 3:
                        $this->sendMQTT($topic . 'media/next', '');
                        break;
                }
                break;
            case 'display':
                switch ($Value) {
                    case 0:
                        $this->sendMQTT($topic . 'displays/off', '');
                        break;
                    case 1:
                        $this->sendMQTT($topic . 'displays/on', '');
                        break;
                }
                break;
            case 'userlock':
                $this->sendMQTT($topic . 'lock', $Value);
                break;
            case 'userlogoff':
                $this->sendMQTT($topic . 'logoff', $Value);
                break;
            case 'volume':
                $this->sendMQTT($topic . 'volume/set', strval($Value));
                break;
            case 'mute':
                switch ($Value) {
                    case true:
                        $this->sendMQTT($topic . 'volume/mute', 'true');
                        break;
                    case false:
                        $this->sendMQTT($topic . 'volume/mute', 'false');
                        break;
                }
                break;
            default:
                $this->LogMessage('Invalid Ident', KL_WARNING);
                break;
        }
    }

    protected function sendMQTT($Topic, $Payload)
    {
        $resultServer = true;
        $resultClient = true;
        //MQTT Server
        $Server['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Server['PacketType'] = 3;
        $Server['QualityOfService'] = 0;
        $Server['Retain'] = false;
        $Server['Topic'] = $Topic;
        $Server['Payload'] = $Payload;
        $ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__ . 'MQTT Server', $ServerJSON, 0);
        $resultServer = @$this->SendDataToParent($ServerJSON);

        //MQTT Client
        $Buffer['PacketType'] = 3;
        $Buffer['QualityOfService'] = 0;
        $Buffer['Retain'] = false;
        $Buffer['Topic'] = $Topic;
        $Buffer['Payload'] = $Payload;
        $BufferJSON = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

        $Client['DataID'] = '{97475B04-67C3-A74D-C970-E9409B0EFA1D}';
        $Client['Buffer'] = $BufferJSON;

        $ClientJSON = json_encode($Client);
        $this->SendDebug(__FUNCTION__ . 'MQTT Client', $ClientJSON, 0);
        $resultClient = @$this->SendDataToParent($ClientJSON);

        if ($resultServer === false && $resultClient === false) {
            $last_error = error_get_last();
            echo $last_error['message'];
        }
    }
}