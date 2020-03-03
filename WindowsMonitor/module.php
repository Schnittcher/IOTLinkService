<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
    class WindowsMonitor extends IPSModule
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

            $this->RegisterProfileInteger('IOT.MemoryMB', 'Graph', '', ' MB', 0, 0, 1);
            $this->RegisterProfileInteger('IOT.NetworkSpeed', 'Network', '', ' Mbps', 0, 0, 1);

            $this->RegisterVariableBoolean('power_state', $this->Translate('State'), '~Switch', 0);
            $this->RegisterVariableBoolean('battery_state', $this->Translate('Battery State'), '~Switch', 1);
            $this->RegisterVariableString('current_user', $this->Translate('Crrent User'), '', 2);
            $this->RegisterVariableInteger('idle_time', $this->Translate('Idle Time'), '', 3);
            $this->RegisterVariableString('uptime', $this->Translate('Uptime'), '', 4);
            $this->RegisterVariableString('boot_time', $this->Translate('Boot Time'), '', 5);
            $this->RegisterVariableInteger('cpu_usage', $this->Translate('CPU Usage'), '~Intensity.100', 6);
            $this->RegisterVariableInteger('memory_usage', $this->Translate('Memory Usage'), '~Intensity.100', 7);
            $this->RegisterVariableInteger('memory_available', $this->Translate('Memory Available'), 'IOT.MemoryMB', 8);
            $this->RegisterVariableInteger('memory_used', $this->Translate('Memory Used'), 'IOT.MemoryMB', 9);
            $this->RegisterVariableInteger('memory_total', $this->Translate('Memory Total'), 'IOT.MemoryMB', 10);

            $this->RegisterVariableInteger('battery_remaining', $this->Translate('Battery Remaning'), '~Battery.100', 11);
            $this->RegisterVariableInteger('battery_remaining_time', $this->Translate('Battery Remaning Time'), '', 12);
            $this->RegisterVariableInteger('battery_predicted_lifetime', $this->Translate('Battery Predicted Lifetime'), '', 13);

            $this->RegisterVariableInteger('hdd_usage', $this->Translate('HDD usage'), '~Intensity.100', 14);
            $this->RegisterVariableInteger('hdd_total_size', $this->Translate('HDD Total Size'), 'IOT.MemoryMB', 15);
            $this->RegisterVariableInteger('hdd_total_free_space', $this->Translate('HDD Total free Space'), 'IOT.MemoryMB', 16);
            $this->RegisterVariableInteger('hdd_free_space', $this->Translate('HDD free Space'), 'IOT.MemoryMB', 17);
            $this->RegisterVariableInteger('hdd_used_space', $this->Translate('HDD used Space'), 'IOT.MemoryMB', 18);
            $this->RegisterVariableString('hdd_format', $this->Translate('HDD format'), '', 19);
            $this->RegisterVariableString('hdd_label', $this->Translate('HDD Label'), '', 20);

            $this->RegisterVariableString('ipv4', $this->Translate('IPv4 Address'), '', 21);
            $this->RegisterVariableString('ipv6', $this->Translate('IPv6 Address'), '', 22);
            $this->RegisterVariableInteger('port_speed', $this->Translate('Port Speed'), 'IOT.NetworkSpeed', 23);
            $this->RegisterVariableBoolean('wired_state', $this->Translate('Wired Network State'), '~Switch', 24);

            IPS_SetIcon($this->GetIDForIdent('current_user'), 'People');
            IPS_SetIcon($this->GetIDForIdent('idle_time'), 'Clock');
            IPS_SetIcon($this->GetIDForIdent('uptime'), 'Clock');
            IPS_SetIcon($this->GetIDForIdent('boot_time'), 'Clock');
            IPS_SetIcon($this->GetIDForIdent('battery_remaining_time'), 'Battery');
            IPS_SetIcon($this->GetIDForIdent('battery_predicted_lifetime'), 'Battery');
            IPS_SetIcon($this->GetIDForIdent('hdd_format'), 'Information');
            IPS_SetIcon($this->GetIDForIdent('hdd_label'), 'Information');
            IPS_SetIcon($this->GetIDForIdent('ipv4'), 'Network');
            IPS_SetIcon($this->GetIDForIdent('ipv6'), 'Network');
            IPS_SetIcon($this->GetIDForIdent('wired_state'), 'Network');
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
                if (fnmatch('*windows-monitor/stats/power/status', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'Offline':
                            $this->SetValue('power_state', false);
                            break;
                        case 'Online':
                            $this->SetValue('power_state', true);
                            break;
                    }
                }
                if (fnmatch('*windows-monitor/stats/battery/status', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'Offline':
                            $this->SetValue('battery_state', false);
                            break;
                        case 'Online':
                            $this->SetValue('battery_state', true);
                            break;
                    }
                }
                if (fnmatch('*windows-monitor/stats/system/current-user', $Buffer->Topic)) {
                    $this->SetValue('current_user', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/system/idle-time', $Buffer->Topic)) {
                    $this->SetValue('idle_time', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/system/uptime', $Buffer->Topic)) {
                    $this->SetValue('uptime', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/system/boot-time', $Buffer->Topic)) {
                    $this->SetValue('boot_time', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/cpu/usage', $Buffer->Topic)) {
                    $this->SetValue('cpu_usage', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/memory/usage', $Buffer->Topic)) {
                    $this->SetValue('memory_usage', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/memory/available', $Buffer->Topic)) {
                    $this->SetValue('memory_available', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/memory/available', $Buffer->Topic)) {
                    $this->SetValue('memory_available', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/memory/used', $Buffer->Topic)) {
                    $this->SetValue('memory_used', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/memory/total', $Buffer->Topic)) {
                    $this->SetValue('memory_total', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/battery/remaining-percent', $Buffer->Topic)) {
                    $this->SetValue('battery_remaining', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/battery/remaining-time', $Buffer->Topic)) {
                    $this->SetValue('battery_remaining_time', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/battery/full-lifetime', $Buffer->Topic)) {
                    $this->SetValue('battery_predicted_lifetime', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/drive-usage', $Buffer->Topic)) {
                    $this->SetValue('hdd_usage', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/total-size', $Buffer->Topic)) {
                    $this->SetValue('hdd_total_size', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/total-free-size', $Buffer->Topic)) {
                    $this->SetValue('hdd_total_free_size', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/available-free-space', $Buffer->Topic)) {
                    $this->SetValue('hdd_free_space', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/used-space', $Buffer->Topic)) {
                    $this->SetValue('hdd_used_space', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/drive-format', $Buffer->Topic)) {
                    $this->SetValue('hdd_format', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/hard-drive/c/volume-label', $Buffer->Topic)) {
                    $this->SetValue('hdd_label', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/network/0/ipv4', $Buffer->Topic)) {
                    $this->SetValue('ipv4', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/network/0/ipv6', $Buffer->Topic)) {
                    $this->SetValue('ipv6', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/network/0/speed', $Buffer->Topic)) {
                    $this->SetValue('port_speed', $Buffer->Payload);
                }
                if (fnmatch('*windows-monitor/stats/network/0/wired', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'False':
                            $this->SetValue('wired_state', false);
                            break;
                        case 'True':
                            $this->SetValue('wired_state', true);
                            break;
                    }
                }
            }
        }
    }