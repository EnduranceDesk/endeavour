<?php

namespace App\Classes\DNS;

use App\Helpers\Screen;
use Badcow\DNS\AlignedBuilder;
use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;


/**
 * DNS Manager
 */
class DNS
{
    protected $path = "/etc/endurance";
    protected $bindpath = "/etc/endurance/configs/bind";
    protected $zonepath = "/etc/endurance/configs/bind/zones";
    protected $serveripfile = "/etc/endurance/configs/server/ip.conf";
    protected $aclpath = "/etc/endurance/configs/bind/acl.conf";
    public function addDomain($ip_address, $domain_without_www)
    {
        $appended_domain_without_www =  $domain_without_www .  ".";
        $explodedIP = explode(".", $ip_address);
        // $origin = PTR::reverseIpv4($explodedIP[0] . "." . $explodedIP[1] . "." .$explodedIP[2] );
        // $zone = new Zone($origin);

        $zone = new Zone($appended_domain_without_www);
        $zone->setDefaultTtl(60);

        $soa = new ResourceRecord;
        $soa->setName('@');
        $soa->setClass(Classes::INTERNET);
        $soa->setRdata(Factory::Soa(
            $appended_domain_without_www,
            'post.'.$appended_domain_without_www,
            '2014110501',
            3600,
            14400,
            604800,
            3600
        ));

        $ns1 = new ResourceRecord;
        $ns1->setName('@');
        $ns1->setClass(Classes::INTERNET);
        $ns1->setRdata(Factory::Ns('ns1.' .  $appended_domain_without_www));

        $ns2 = new ResourceRecord;
        $ns2->setName('@');
        $ns2->setClass(Classes::INTERNET);
        $ns2->setRdata(Factory::Ns('ns2.' .  $appended_domain_without_www));

        $a1 = new ResourceRecord;
        $a1->setName('@');
        $a1->setRdata(Factory::A($ip_address));
        $a1->setComment('IP of the server');

        $a2 = new ResourceRecord;
        $a2->setName('hello');
        $a2->setRdata(Factory::A($ip_address));
        $a2->setComment('IP of the server');

        $a3 = new ResourceRecord;
        $a3->setName('mail');
        $a3->setRdata(Factory::A($ip_address));
        $a3->setComment('IP of the server');




        $a4 = new ResourceRecord;
        $a4->setName('ns1');
        $a4->setRdata(Factory::A($ip_address));
        $a4->setComment('IP of the server');

        $a5 = new ResourceRecord;
        $a5->setName('ns2');
        $a5->setRdata(Factory::A($ip_address));
        $a5->setComment('IP of the server');


        $a6 = new ResourceRecord;
        $a6->setName('endurance');
        $a6->setRdata(Factory::A($ip_address));
        $a6->setComment('IP of the server');


        $a7 = new ResourceRecord;
        $a7->setName('rover');
        $a7->setRdata(Factory::A($ip_address));
        $a7->setComment('IP of the server');

        $ftp_cname = new ResourceRecord;
        $ftp_cname->setName('ftp');
        $ftp_cname->setRdata(Factory::CNAME($appended_domain_without_www));
        $ftp_cname->setComment('IP of the server');

        $www_cname = new ResourceRecord;
        $www_cname->setName('www');
        $www_cname->setRdata(Factory::CNAME($appended_domain_without_www));
        $www_cname->setComment('IP of the server');

        $txt_identity_record = new ResourceRecord;
        $txt_identity_record->setRdata(Factory::TXT('Hello World Speaking from Endurance send some love to MARK-II'));
        $txt_identity_record->setComment('IP of the server');

        $dmarc_record = new ResourceRecord;
        $dmarc_record->setRdata(Factory::TXT('v=DMARC1; p=reject; rua=mailto:postmaster@'.$domain_without_www));
        $dmarc_record->setComment('IP of the server');


        $dkim_key_path = "/etc/opendkim/keys/". $domain_without_www. ".txt";
        $txt_dkim = null;
        if (file_exists($dkim_key_path)) {
            $data = file_get_contents($dkim_key_path);
            $data = str_replace('"', '', $data);
            $data = explode("(", $data)[1];
            $data = trim($data);
            $data = explode(")",$data)[0];
            $data = str_replace(' ', '', $data);
            $data = str_replace(PHP_EOL, '', $data);
            $txt_dkim = new ResourceRecord;
            $txt_dkim->setName('default._domainkey');
            $txt_dkim->setRdata(Factory::TXT(trim($data)));
            $txt_dkim->setComment('DKIM');

        }


        $txt_spf_record = new ResourceRecord;
        $txt_spf_record->setRdata(Factory::TXT("v=spf1 +a +mx +ip4:{$ip_address} ~all"));

        $mx1 = new ResourceRecord;
        $mx1->setName('@');
        $mx1->setRdata(Factory::Mx(10,  $appended_domain_without_www));

        $ptr1 = ResourceRecord::create('1', Factory::PTR($appended_domain_without_www), null, Classes::INTERNET);

        $zone->addResourceRecord($soa);
        $zone->addResourceRecord($ns1);
        $zone->addResourceRecord($ns2);
        $zone->addResourceRecord($a1);
        $zone->addResourceRecord($a2);
        $zone->addResourceRecord($a3);
        $zone->addResourceRecord($a4);
        $zone->addResourceRecord($a5);
        $zone->addResourceRecord($a6);
        $zone->addResourceRecord($a7);
        $zone->addResourceRecord($ftp_cname);
        $zone->addResourceRecord($www_cname);
        $zone->addResourceRecord($mx1);
        $zone->addResourceRecord($txt_identity_record);
        $zone->addResourceRecord($txt_spf_record);
        $zone->addResourceRecord($dmarc_record);
        if ($txt_dkim) {
            $zone->addResourceRecord($txt_dkim);
        }
        // $zone->addResourceRecord($ptr1);

        $alignedBuilder = new AlignedBuilder();
        $text =  $alignedBuilder->build($zone);

        $path = $this->path;
        $zonepath = $this->zonepath;
        $domain_zone_path = $zonepath . "/" . $domain_without_www;
        try {
            if (file_exists($domain_zone_path)) {
                $this->removeDirectory($domain_zone_path);
            }
        } catch (\Exception $e) {
            // TODO: What is $this->error()?
            $this->error($e->getMessage());
            return false;
        }
        mkdir($domain_zone_path);
        $check = file_put_contents($domain_zone_path . "/zone.txt", $text);

        $zone_info =  'zone "' . $domain_without_www . '" IN {' . PHP_EOL;
        $zone_info .= "    type master;". PHP_EOL;
        $zone_info .= '    file "' . $domain_zone_path . "/zone.txt" . '";'. PHP_EOL;
        $zone_info .= "    allow-transfer { trusted-servers; };". PHP_EOL;
        $zone_info .= "};". PHP_EOL;
        $check = file_put_contents($domain_zone_path . "/info.conf", $zone_info);
        $this->rebuildZones();
        $this->reloadDNS();
        return true;
    }
    public function removeDomain($domain_without_www)
    {
        $zonepath = $this->zonepath;
        $domain_zone_path = $zonepath . "/" . $domain_without_www;
        try {
            if (file_exists($domain_zone_path)) {
                $this->removeDirectory($domain_zone_path);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return false;
        }
        $this->rebuildZones();
        $this->reloadDNS();
        return true;
    }
    public function rebuildZones()
    {
        $bindpath = $this->bindpath;
        $zonepath = $this->zonepath;
        $zones = scandir($zonepath);
        unset($zones[0]);
        unset($zones[1]);
        $text = "#This file is autogenerated via Endurance using indiviual zones already created." .PHP_EOL;
        $text .= "################################################################################" .PHP_EOL . PHP_EOL;
        foreach ($zones as $domain) {
            $text .= 'include "' . $zonepath . "/" . $domain . '/info.conf";' . PHP_EOL;
        }
        $text .= PHP_EOL . PHP_EOL;
        $check = file_put_contents($bindpath . "/zones.conf", $text);
        return $check;
    }
    public function reloadDNS()
    {
        Screen::get()->executeCommand("systemctl reload named.service",[], null,5);
    }
    public function removeDirectory($path) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }
    public function addAsTrustedACL($ip)
    {
        unlink($this->aclpath);
        $content = "acl trusted-servers { $ip; }; ";
        file_put_contents($this->aclpath, $content);
        $this->reloadDNS();
        return true;
    }
}
