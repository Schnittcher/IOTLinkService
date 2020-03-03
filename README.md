[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/IOTLinkService/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/IOTLinkService/actions)

# IOTLinkService
Dieses Modul verbindet den Dienst IOTLink (https://gitlab.com/iotlink/iotlink) mit IP-Symcon.
Mit dem Modul ist es möglich Systeminformationen eines Windows Rechners über MQTT auszulesen und diesen fernzusteuern.

## Inhaltverzeichnis
1. [Voraussetzungen](#1-voraussetzungen)
2. [Enthaltene Module](#2-enthaltene-module)
3. [Installation](#3-installation)
4. [Konfiguration in IP-Symcon](#4-konfiguration-in-ip-symcon)
5. [Spenden](#5-spenden)
6. [Lizenz](#6-lizenz)

## 1. Voraussetzungen

* mindestens IPS Version 5.0
* IOTLink mit den Windows Monitor und Commands Addons (https://gitlab.com/iotlink/iotlink)
* Im Windows Monitor Addon das Senden eines Screenshots abschalten, ansonsten stürtzt IP-Symcon ab

## 2. Enthaltene Module

### [Control](Control/README.md)
Mit dem Control Modul ist es möglich, den Windows Rechner über IP-Symcon fernzustern.

### [WindowsMonitor](WindowsMonitor/README.md)
Der WindowsMonitor liefert Systemdaten, welche in IP-Symcon visualisiert werden.

## 3. Installation

Über den Module Store.

## 4. Konfiguration in IP-Symcon
Bitte den einzelnen Modulen entnehme.

## 5. Spenden

Dieses Modul ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
