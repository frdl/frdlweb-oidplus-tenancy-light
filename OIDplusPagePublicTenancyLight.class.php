<?php

/*
 * OIDplus 2.0
 * Copyright 2022 - 2023 Daniel Marschall, ViaThinkSoft / Till Wehowski, Frdlweb
 *
 * Licensed under the MIT License.
 */

namespace Frdlweb\OIDplus;

use ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_4;
use ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_7;
use ViaThinkSoft\OIDplus\OIDplus;
use ViaThinkSoft\OIDplus\OIDplusObject;
use ViaThinkSoft\OIDplus\OIDplusPagePluginPublic;
use ViaThinkSoft\OIDplus\OIDplusPagePluginAdmin;
use ViaThinkSoft\OIDplus\OIDplusGui;
use ViaThinkSoft\OIDplus\OIDplusNotification;
use ViaThinkSoft\OIDplus\OIDplusPagePublicAttachments;
use ViaThinkSoft\OIDplus\OIDplusException;
// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class OIDplusPagePublicTenancyLight extends OIDplusPagePluginAdmin //OIDplusPagePluginPublic // implements RequestHandlerInterface
	implements  //INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_1, /* oobeEntry, oobeRequested */
	           \ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_4,  //Ra+Whois Attributes
	           \ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_2, /* modifyContent */
	           \ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_8 , /* getNotifications */
	        \ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_9/*  restApi* */
	 //
				   /*   \ViaThinkSoft\OIDplus\INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_7 getAlternativesForQuery() */
{

	/**
	 * @param string $actionID
	 * @param array $params
	 * @return array
	 * @throws \ViaThinkSoft\OIDplus\OIDplusException
	 */
	//will be extended?
	//public function action(string $actionID, array $params): array {
	//	return parent::action($actionID, $params);
	//}

			
	  public function gui(string $id, array &$out, bool &$handled) { 
		  


		  
		if (OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', false) && explode('$',$id,2)[0] === 'oidplus:resources') {
			
			
			//oidplus:resources
		}elseif (OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', false)
				 && explode('$',$id,2)[0] === 'oidplus:system') {
			$handled = false;
				throw new OIDplusException(_L('Invalid Welcome-Page for TENANT '.OIDplus::baseConfig()->getValue('COOKIE_DOMAIN')), null, 500);
			//die(OIDplus::baseConfig()->getValue('COOKIE_DOMAIN'));
		}elseif (OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', false)
				 && explode('$',$id,2)[0] === 'oidplus:system:'.OIDplus::baseConfig()->getValue('COOKIE_DOMAIN')) {
			$handled = true;

			$out['title'] = OIDplus::config()->getValue('system_title');
			$out['icon'] = OIDplus::webpath(__DIR__,OIDplus::PATH_ABSOLUTE_CANONICAL).'img/main_icon.png';
 
			if (file_exists(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome/welcome$'.OIDplus::getCurrentLang().'.html')) {
				$cont = file_get_contents(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome/welcome$'.OIDplus::getCurrentLang().'.html');
			} else if (file_exists(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome/welcome.html')) {
				$cont = file_get_contents(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome/welcome.html');
			} else if (file_exists(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome$'.OIDplus::getCurrentLang().'.html')) {
				$cont = file_get_contents(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome$'.OIDplus::getCurrentLang().'.html');
			} else if (file_exists(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome.html') ) {
				$cont = file_get_contents(OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' ) . 'welcome.html');
			} else if (file_exists(__DIR__ . '/welcome.html')) {
				$cont = file_get_contents(__DIR__ . '/welcome.html');
			} else {
				$cont = '';
			}

			if ($cont) {
				list($html, $js, $css) = extractHtmlContents($cont);
				$cont = '';
				if (!empty($js)) $cont .= "<script>\n$js\n</script>";
				if (!empty($css)) $cont .= "<style>\n$css\n</style>";
				$cont .= stripHtmlComments($html);
			}

			$out['text'] = $cont;

			if (strpos($out['text'], '%%OBJECT_TYPE_LIST%%') !== false) {
				$tmp = '<ul>';
				foreach (OIDplus::getEnabledObjectTypes() as $ot) {
					$tmp .= '<li><a '.OIDplus::gui()->link($ot::root()).'>'.htmlentities($ot::objectTypeTitle()).'</a></li>';
				}
				$tmp .= '</ul>';
				$out['text'] = str_replace('%%OBJECT_TYPE_LIST%%', $tmp, $out['text']);
			}		
		}//oidplus:system
				 
	  }
				   
	//+ add table altids
	public function init(bool $html=true) {
		
		/*
		if (!OIDplus::db()->tableExists("###config")) {
                 OIDplus::baseConfig()->setValue('TABLENAME_PREFIX', 
								OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_'));
		}
*/
		
		
		
		
		$htaccessFile_dist =__DIR__.\DIRECTORY_SEPARATOR. 'dist-htaccess-userdata_public.txt';
		$htaccessFile = OIDplus::localpath().'userdata_pub/.htaccess';
		
		     if(!file_exists($htaccessFile) 
			   || filesize($htaccessFile) !== filesize($htaccessFile_dist)
			   || filemtime($htaccessFile) < time() - 3 * 60 * 60
			  ){
				 copy($htaccessFile_dist, $htaccessFile);					
			 }
		
		
            $included_files = get_included_files();
		    $file = __DIR__.\DIRECTORY_SEPARATOR.'config.tenent-light.php';
		    $configfile = OIDplus::localpath().'userdata/baseconfig/config.inc.php';
		    $configfileMod = OIDplus::localpath().'userdata/baseconfig/config.1.3.6.1.4.1.37476.9000.108.1778120633.php';
		    $configfileModBase = basename($configfileMod);
		   
		
	  $remove = 'die(<<<HTMLCODE
$host is not a valid host!<br />
<a href="https://webfan.de/apps/registry">Got to Registry...</a>
HTMLCODE
			 );';
$codext = <<<PHP
	require __DIR__.\\DIRECTORY_SEPARATOR.'$configfileModBase';		   
PHP;		
		    if(!file_exists($configfileMod) 
			   || filesize($configfileMod) !== filesize($file)
			   || filemtime($configfileMod) < time() - 3 * 60 * 60
			  ){
			    copy($file, $configfileMod);	
				$oldcode = file_get_contents($configfile);
			 	$code = rtrim($oldcode, '>?');
				$code = str_replace($remove, '', $code);
				$code = str_replace($codext, '', $code);	
			//	$code = preg_replace("/([\r\n]{2,})/x", '\n', $code);	
				file_put_contents($configfile,$code.\PHP_EOL. $codext.\PHP_EOL);
			}
	 	    if (!in_array($configfileMod, $included_files)) {
                   require $configfileMod;
            }
	
$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
$host_proxy = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : false;
$sys_url = OIDplus::webpath(null,OIDplus::PATH_ABSOLUTE_CANONICAL);

//$web_url =OIDplusPagePublicTenancyLight::getSystemUrl(false );
$web_url = rtrim(OIDplus::baseConfig()->getValue('CANONICAL_SYSTEM_URL', ''),'/').'/';
$parsed_system_url = parse_url($sys_url);
$parsed_web_url = parse_url($web_url);

$host_system = $parsed_system_url['host'];
$host_web = $parsed_web_url['host'];
  
	 $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
 //    $tenantDir = OIDplus::localpath().'userdata/tenant/'.$host;	
	 $tenantDir = 	OIDplus::baseConfig()->getValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/tenant/'.$host );
if(
    $host !== $host_proxy
	 && 
	(false!==$host_proxy && $_SERVER['HTTP_HOST'] !== $host_proxy)   
//   || $_SERVER['SERVER_NAME'] !== $host
//   || $host_system  !== $host
  ){
	 $isTenancyHost = true;
}else{
	$isTenancyHost = false;
}		
		
   //   $plugins = OIDplus::getDatabasePlugins();
   if(is_dir($tenantDir) && 
	  $isTenancyHost 
//	 &&  OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', $isTenancyHost )
	 ){
 	 
		//   die($tenantDir.$host_proxy.$host_system.$host);

        // OIDplus::init();
       //  OIDplus::registerDatabasePlugin(OIDplus::getActiveDatabasePlugin());

//			die($tenantDir.$host_proxy.$host_system.$host);
              $prefix = OIDplus::baseConfig()->getValue('TABLENAME_PREFIX_TENANT', 
																			OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 
																											'oidplus_')
																			.str_replace('.', '_', $host).'_'
																		   );
	   
		if (!OIDplus::db()->tableExists("".$prefix."config")) {			
			OIDplus::db()->query("CREATE TABLE ".$prefix."config  SELECT * FROM "
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_')."config");
		}	

        foreach(['objects', 'asn1id', 'iri', 'ra', 'altids', 'log',  'log_object', 'log_user'] as $tablename){
			if (!OIDplus::db()->tableExists(sprintf("%s%s", $prefix, $tablename)) ) {
			     OIDplus::db()->query(sprintf("CREATE TABLE %s%s LIKE ", $prefix, $tablename)
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').$tablename);
	     	}	 
        }
	 
	 //mkdir($tenantDir, 0755, true);
   }//if(!is_dir(OIDplus::localpath().'userdata/tenant/'.$host) ){	 
		
     
 	
   }

				   
				   

				   
				   
				   
				   
	/**
	 * Implements interface INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_4
	 * @param string $id
	 * @param array $out
	 * @return void
	 * @throws \ViaThinkSoft\OIDplus\OIDplusException
	 */
	public function whoisObjectAttributes(string $id, array &$out) {
		$xmlns = 'oidplus-frdlweb-altids-plugin';
		$xmlschema = 'urn:oid:1.3.6.1.4.1.37553.8.1.8.8.53354196964.641310544.1714020422';
		$xmlschemauri = OIDplus::webpath(__DIR__.'/tenant.xsd',OIDplus::PATH_ABSOLUTE_CANONICAL);

		$handleShown = false;
		$canonicalShown = false;

		$out1 = array();
		$out2 = array();
/*
		$tmp = $this->getAlternativesForQuery($id);
		sort($tmp); // DM 26.03.2023 : Added sorting (intended to sort "alternate-identifier")
		foreach($tmp as $alt) {
			if (strpos($alt,':') === false) continue;

			list($ns, $altIdRaw) = explode(':', $alt, 2);

			if (($canonicalShown === false) && ($ns === 'oid')) {
				$canonicalShown=true;

				$out1[] = [
					'xmlns' => $xmlns,
					'xmlschema' => $xmlschema,
					'xmlschemauri' => $xmlschemauri,
					'name' => 'canonical-identifier',
					'value' => $ns.':'.$altIdRaw,
				];

			}

			if (($handleShown === false) && ($alt === $id)) {
				$handleShown=true;

				$out1[] = [
					'xmlns' => $xmlns,
					'xmlschema' => $xmlschema,
					'xmlschemauri' => $xmlschemauri,
					'name' => 'handle-identifier',
					'value' => $alt,
				];

			}

			if ($alt !== $id) { // DM 26.03.2023 : Added condition that alternate must not be the id itself
				$out2[] = [
					'xmlns' => $xmlns,
					'xmlschema' => $xmlschema,
					'xmlschemauri' => $xmlschemauri,
					'name' => 'alternate-identifier',
					'value' => $ns.':'.$altIdRaw,
				];
			}

		}
*/
		// DM 26.03.2023 : Added this
		$out = array_merge($out, $out1); // handle-identifier and canonical-identifier
		$out = array_merge($out, $out2); // alternate-identifier

	}

	/**
	 * Implements interface INTF_OID_1_3_6_1_4_1_37476_2_5_2_3_4
	 * @param string $email
	 * @param array $out
	 * @return void
	 */
	public function whoisRaAttributes(string $email, array &$out) {

	}
	public function restApiInfo(string $kind='html'): string {
		if ($kind === 'html') {
                   return array_to_html_ul_li([]);
                }
	}
	

		   
	
	public function modifyContent($id, &$title, &$icon, &$text) {

	}
	
	


	public function restApiCall(string $requestMethod, string $endpoint, array $json_in) {
		 
		
	}
					   

	public function getNotifications(string $user=null): array {
		$notifications = array();
//'.OIDplus::gui()->link($row['id']).' 
			 		$notifications[] = 
			new OIDplusNotification('INFO', _L('Running <a href="%1">%2</a>', 
											  // '<a href="https://registry.frdl.de/?goto=oid%3A1.3.6.1.4.1.37553.8.1.8.8.53354196964">'
											     //.htmlentities($row['id'])
											  // '<a href="%1">%2</a>', 
											   OIDplus::gui()->link('oid:1.3.6.1.4.1.37476.9000.108.1778120633'),
											  htmlentities( 'Tenancy-Light Plugin' )
											  )
								   );
		return $notifications;
	}

}
