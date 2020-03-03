# WindowsMonitor
Der WindowsMonitor liefert Systemdaten, welche in IP-Symcon visualisiert werden.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)

### 1. Funktionsumfang

* Auflistung der Systeminformationen von dem verbundenen Windows Rechner

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0
- IOTLink mit WindowsMonitor

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' ist das 'IOT Link Service - Windows Monitor'-Modul unter dem Hersteller '(Gerät)' aufgeführt.

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Prefix        | MQTT Prefix für das Topic Standard ist "iotlink"
Domainname    | Der Domainname des Rechners, wenn nichts eingestellt wurde ist es die Arbeitsgruppe "workgroup"
Computrername | Der Computername mit dem sich das Modul verbinden soll.