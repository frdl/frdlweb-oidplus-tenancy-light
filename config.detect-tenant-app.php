<?php

namespace Frdlweb\OIDplus\patch;

use ViaThinkSoft\OIDplus\OIDplus;
use Frdlweb\OIDplus\OIDplusPagePublicTenancyLight;
 

function http_parse_query($query) {
    $parameters = array();
    $queryParts = explode('&', $query);
    foreach ($queryParts as $queryPart) {
        $keyValue = explode('=', $queryPart, 2);
        $parameters[$keyValue[0]] = isset($keyValue[1]) ? $keyValue[1] : '';
    }
    return $parameters;
}

function build_url(array $parts) {
    return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') . 
        ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') . 
        (isset($parts['user']) ? "{$parts['user']}" : '') . 
        (isset($parts['pass']) ? ":{$parts['pass']}" : '') . 
        (isset($parts['user']) ? '@' : '') . 
        (isset($parts['host']) ? "{$parts['host']}" : '') . 
        (isset($parts['port']) ? ":{$parts['port']}" : '') . 
        (isset($parts['path']) ? "{$parts['path']}" : '') . 
        (isset($parts['query']) ? "?{$parts['query']}" : '') . 
        (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
}

// Example
/*
$parts = parse_url($url);

if (isset($parts['query'])) {
    $parameters = http_parse_query($parts['query']);
    foreach ($parameters as $key => $value) {
        $parameters[$key] = $value; // do stuff with $value
    }
    $parts['query'] = http_build_query($parameters);
}

$url = build_url($parts);
*/
if(!isset($_SERVER['SERVER_NAME']))$_SERVER['SERVER_NAME']= \php_uname("n");
if(!isset($_SERVER['HTTP_HOST']))$_SERVER['HTTP_HOST']=$_SERVER['SERVER_NAME'];

$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
$host_requested = $host;
$host_proxy = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : false;
$sys_url = 'https://'.$_SERVER['SERVER_NAME'].OIDplus::webpath(null,OIDplus::PATH_RELATIVE_TO_ROOT_CANONICAL);

//$web_url =OIDplusPagePublicTenancyLight::getSystemUrl(false );
$web_url = rtrim(OIDplus::baseConfig()->getValue('CANONICAL_SYSTEM_URL', ''),'/').'/';
$parsed_system_url = parse_url($sys_url);
$parsed_web_url = parse_url($web_url);

$host_system = $parsed_system_url['host'];
$host_web = $parsed_web_url['host'];


OIDplus::baseConfig()->setValue('ROOT_INSTANCE_TABLENAME_PREFIX', OIDplus::baseConfig()->getValue('TABLENAME_PREFIX', 'oidplus_'));

OIDplus::baseConfig()->setValue('TABLENAME_PREFIX_TENANT', 
								OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').str_replace('.', '_', $host).'_');


$tenantDir = OIDplus::localpath().'userdata/tenant/'.$host;
$tenantDirOld = $tenantDir;
OIDplus::baseConfig()->setValue('TENANT_REQUESTED_HOST', $host );	



/*
$tenantConfigFiles =[
	$tenantDir.'/config.inc.php',
	$tenantDir.'/config.tenant.php',					
	$tenantDir.'/config.'.$host.'.php'					
]; 
 */
$tenantDir = OIDplus::localpath().'userdata/tenant/'.$host;
$tenantConfigFiles =[
	'/config.inc.php',
	'/baseconfig/config.inc.php',
	'/config.tenant.php',					
	//'/config.'.$host.'.php'					
]; 


 if(file_exists($tenantDir.$tenantConfigFiles[0])){
	require $tenantDir.$tenantConfigFiles[0]; 
 }

$host = OIDplus::baseConfig()->getValue('TENANT_REQUESTED_HOST', $host );
$tenantDir = OIDplus::localpath().'userdata/tenant/'.$host;

//foreach($tenantConfigFiles as $num => $file){
 //$tenantConfigFiles[$num] = str_replace($tenantDirOld, $tenantDir, $tenantConfigFiles[$num]);
//}


$tenantConfigFiles[] = '/config.'.$host.'.php';

OIDplus::baseConfig()->setValue('TABLENAME_PREFIX_TENANT', 
								OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').str_replace('.', '_', $host).'_');



switch($host_requested){
	case $host_web :
		 OIDplus::baseConfig()->setValue('COOKIE_PATH', OIDplus::webpath(null,OIDplus::PATH_RELATIVE_TO_ROOT_CANONICAL) );
		 OIDplus::baseConfig()->setValue('COOKIE_DOMAIN', $host);
		break;
	case $host_system :
		 OIDplus::baseConfig()->setValue('COOKIE_PATH', '(auto)' );
		 OIDplus::baseConfig()->setValue('COOKIE_DOMAIN', $host );	
		break;		
	default:
		 OIDplus::baseConfig()->setValue('COOKIE_PATH', '(auto)' );
		 OIDplus::baseConfig()->setValue('COOKIE_DOMAIN', $host );		 
		break;
}
/*
if(  //$host_system !== $host_web
    // && 
//	$_SERVER['SERVER_NAME'] !== $host
	//  &&
	  is_dir($tenantDir)
	&&
   (       
	       $host !== $host_system
	   ||  $host_system !== $host_web
       || (false!==$host_proxy && $_SERVER['HTTP_HOST'] !== $host_proxy)
       || $_SERVER['SERVER_NAME'] !== $host
	  // || is_dir($tenantDir)
	)
   
   ){
	 $isTenant = true;
}else{
	$isTenant = false;
}
 */

if(is_dir($tenantDir) ){
	 $isTenant = true;
	OIDplus::baseConfig()->setValue('USERDATA_WITHTENANT_DIRECTORY', $tenantDir.'/' );
}else{	
	OIDplus::baseConfig()->setValue('USERDATA_WITHTENANT_DIRECTORY', OIDplus::localpath().'userdata/' );
	$isTenant = false;
}
 //die($isTenant.'<br />'.$host_system.'<br />'.$host_web.'<br />'.$host.'<br />'.$host_proxy.'<br />'.$_SERVER['SERVER_NAME']);
 

	 OIDplus::baseConfig()->setValue('TENANT_IS_TENANT', $isTenant );	


switch($isTenant){
	case true :
	     OIDplus::baseConfig()->setValue('CANONICAL_SYSTEM_URL', 'https://'.$host);
		 OIDplus::baseConfig()->setValue('COOKIE_PATH', OIDplus::webpath(null,OIDplus::PATH_RELATIVE_TO_ROOT_CANONICAL) );
		 OIDplus::baseConfig()->setValue('COOKIE_DOMAIN', $host);
		break;
	case false :
		 OIDplus::baseConfig()->setValue('COOKIE_PATH', '(auto)' );
		 OIDplus::baseConfig()->setValue('COOKIE_DOMAIN', $host );	
		break;		
	default:
		 //die($host.$parsed_system_url['host'].$_SERVER['SERVER_NAME']);
		break;
}





OIDplus::baseConfig()->setValue('PUBSUB_CACHE_DIRECTORY',  OIDplus::localpath().'userdata/cache/'.$host.'/pubsub/' );
OIDplus::baseConfig()->setValue('SCHEMA_CACHE_DIRECTORY',  OIDplus::localpath().'userdata/cache/-unique-/schema/' );


OIDplus::baseConfig()->setValue('TABLENAME_PREFIX', 
								true===$isTenant
								 ? OIDplus::baseConfig()->getValue('TABLENAME_PREFIX_TENANT', 'oidplus_')
							     :  OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_') 
							   );
/*
OIDplus::baseConfig()->setValue('TABLENAME_PREFIX', 
								 OIDplus::baseConfig()->getValue('TABLENAME_PREFIX_TENANT',
								OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').str_replace('.', '_', $host).'_')
							   );
*//*
try{
   		if (true===$isTenant && !OIDplus::db()->tableExists($prefix."config")) {

			OIDplus::baseConfig()->setValue('TABLENAME_PREFIX',
											OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_'));			
		}else{
			
		}
}catch(\Exception $e){
			OIDplus::baseConfig()->setValue('TABLENAME_PREFIX',
											OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_'));	
}



if(is_dir($tenantDir) ){
              $prefix = OIDplus::baseConfig()->getValue('TABLENAME_PREFIX_TENANT', 
									OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_')
			  );
	   


}else{
	              $prefix = OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 
									OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_')
			  );
}
		if (!OIDplus::db()->tableExists($prefix."config")) {			
			OIDplus::db()->query("CREATE TABLE ".$prefix."config  SELECT * FROM "
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_')."config");
		}	

        foreach(['objects', 'asn1id', 'iri', 'ra', 'altids', 'log',  'log_object', 'log_user'] as $tablename){
			if (!OIDplus::db()->tableExists(sprintf("%s%s", $prefix, $tablename)) ) {
			     OIDplus::db()->query(sprintf("CREATE TABLE %s%s LIKE ", $prefix, $tablename)
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').$tablename);
	     	}	 
        }
		


*/
OIDplus::baseConfig()->setValue('XFF_TRUSTED_PROXIES',
								OIDplus::baseConfig()->getValue('XFF_TRUSTED_PROXIES', 
																['212.53.140.43', '212.72.182.211'])
							   );

   if(isset($_SERVER['SERVER_ADDR'])){
	 $trusted =    OIDplus::baseConfig()->getValue('XFF_TRUSTED_PROXIES');
	 $trusted[]=   $_SERVER['SERVER_ADDR'];
	   OIDplus::baseConfig()->setValue('XFF_TRUSTED_PROXIES',$trusted);	   
   }

if(false === $isTenant)return;

if($tenantDirOld === $tenantDir || $host_requested === $host){
	//array_shift($tenantConfigFiles);
}

foreach($tenantConfigFiles as $file){
 if(file_exists($tenantDir.$file)){
	require $tenantDir.$file; 
 }
}

if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
     (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
     (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
    $server_request_scheme = 'https';
} else {
    $server_request_scheme = 'http';
}

$url = $server_request_scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$parts = parse_url($url);
$search = http_parse_query(isset($parts['query']) ? $parts['query'] : '');

if ('GET' === $_SERVER['REQUEST_METHOD'] && isset($search['goto']) && OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', false) 
    && (explode('$',$search['goto'],2)[0] === 'oidplus:system' || explode('$',$search['goto'],2)[0] === urlencode('oidplus:system') )) {
	
     $goto =  'oidplus:system:'.OIDplus::baseConfig()->getValue('COOKIE_DOMAIN');
	 $search['goto'] = $goto;
	 $parts['query'] = http_build_query($search);
	 $urlNew = build_url($parts);
//	die($urlNew);
	 $_REQUEST['goto'] = $search['goto'];
	if($urlNew !== $url){
	//  header_remove();
	 // header('Location: '.$urlNew);//, 302);
	 // die('<a href="'.$urlNew.'">Goto: '.$urlNew.'</');
	} 
}

if ('GET' === $_SERVER['REQUEST_METHOD'] && isset($search['id']) && OIDplus::baseConfig()->getValue('TENANT_IS_TENANT', false)
				 && ($search['id'] === 'oidplus:system' || $search['id'] === urlencode('oidplus:system') )) {
	
     $goto =  'oidplus:system:'.OIDplus::baseConfig()->getValue('COOKIE_DOMAIN');
	 $search['id'] = $goto;
	 $parts['query'] = http_build_query($search);
	 $urlNew = build_url($parts);
	 $_REQUEST['id'] = $search['id'];
	/*
 	die($urlNew);
	if($urlNew !== $url){
	  header_remove();
	  header('Location: '.$urlNew);//, 302);
	  die(
		//  '<a href="'.$urlNew.'">Goto: '.$urlNew.'</'
	  );
	} 
	*/
}
 /*

 		if (!OIDplus::db()->tableExists("###config")) {
			OIDplus::db()->query("CREATE TABLE ###config  SELECT * FROM "
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_')."config");
		}	

        foreach(['objects', 'asn1id', 'iri', 'ra', 'altids', 'log',  'log_object', 'log_user'] as $tablename){
			if (!OIDplus::db()->tableExists(sprintf("###%s", $tablename)) ) {
			     OIDplus::db()->query(sprintf("CREATE TABLE ###%s LIKE ", $tablename)
			  .OIDplus::baseConfig()->getValue('ROOT_INSTANCE_TABLENAME_PREFIX', 'oidplus_').$tablename);
	     	}	 
        }
		

 if(isset($_GET['test'])){
 die(OIDplus::baseConfig()->getValue('OIDINFO_API_URL') . OIDplus::baseConfig()->getValue('TENANT_REQUESTED_HOST', $host ));	
}*/
$_SERVER['HTTP_HOST'] =  OIDplus::baseConfig()->getValue('TENANT_REQUESTED_HOST', $host );
OIDplus::forceTenantSubDirName($host);
