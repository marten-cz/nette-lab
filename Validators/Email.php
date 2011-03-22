<?php
namespace NetteLab\Validators;

class Email
{
	/**
	 * Validate an email address.
	 * Provide email address (raw input)
	 * Returns true if the email address has the email 
	 * address format and the domain exists.
	 */
	public static function validateEmail($email)
	{
		$return = array(
			'format' => null,
			'hasMx' => null,
			'isIp' => null,
			'isBlacklisted' => null,
			'isSpammer' => null,
			'isAnonymous' => null,
			'isTemporary' => null,
			'message' => null,
		);
		
		$tmp = self::validateFormat($email);
		$return['format'] = $tmp;
		
		if($tmp == false)
		{
			return $ret;
		}
		
		$atIndex = strrpos($email, "@");
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);

		$return['isIp'] = preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', str_replace("\\\\","",$domain));
		if(!$return['isIp'] && !preg_match('/^[-a-zA-Z0-9][-.a-zA-Z0-9]*@[-.a-zA-Z0-9]+(\.[-.a-zA-Z0-9]+)*\.(name|museum|coop|aero|[a-zA-Z]{2,3})$/', str_replace("\\\\","",$local)))
		{
			$return['format'] = false;
			return $return;
		}
		
		//$return['isBlacklisted'] = self::isBlacklisted($domain);
		$return['hasMx'] = self::checkMx($domain);
		$return['isTemporary'] = self::isTemporary($domain);
		
		return $return;
	}

	protected static function validateFormat($email)
	{
		$isValid = true;
		
		$atIndex = strrpos($email, "@");
		
		if(is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			elseif($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			elseif($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			elseif(preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			elseif(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
			}
			elseif(preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			elseif(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
			{
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
				{
					$isValid = false;
				}
			}
		}
		
		return $isValid;
	}
	
	protected static function checkMx($domain)
	{
		return (checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"));
	}
	
	protected static function isBlacklisted($ip)
	{
		$dnsbl_lists = array("bl.spamcop.net", "list.dsbl.org", "sbl.spamhaus.org");
		if ($ip && preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/', $ip))
		{
			$reverse_ip = implode(".", array_reverse(explode(".", $ip)));
			$on_win = substr(PHP_OS, 0, 3) == "WIN" ? 1 : 0;
			foreach ($dnsbl_lists as $dnsbl_list)
			{
				if (function_exists("checkdnsrr"))
				{
					if (checkdnsrr($reverse_ip . "." . $dnsbl_list . ".", "A"))
					{
						return $reverse_ip . "." . $dnsbl_list;
					}
				}
				else if ($on_win == 1)
				{
					$lookup = "";
					@exec("nslookup -type=A " . $reverse_ip . "." . $dnsbl_list . ".", $lookup);
					foreach ($lookup as $line)
					{
						if (strstr($line, $dnsbl_list))
						{
							return $reverse_ip . "." . $dnsbl_list;
						}
					}
				}
			}
		}
		
		return false;
	}
	
	protected static function isTemporary($domain)
	{
		$temporary = array('10minutemail.com','disposeamail.com','dontreg.com','e4ward.com','guerrillamail.com','inbox2.info','jetable.com','kasmail.com','killmail.net','maileater.com','mailexpire.com','mailinator.com','mailmoat.com','mytrashmail.com','netmails.net','noclickemail.com','nullbox.info','pookmail.com','shortmail.net','sneakemail.com','spambob.com','spambob.org','spambox.info','spambox.org','spambox.us','spamex.com','spamfree24.net','spamfree24.org','spamgourmet.com','spamhole.com','spammotel.com','tempinbox.com','temporaryforwarding.com','temporaryinbox.com','trashmail.net','xemaps.com','yopmail.com');
		
		return in_array($domain, $temporary);
	}
}