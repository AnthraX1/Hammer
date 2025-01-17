#!/usr/bin/python2.7
#coding:utf-8

'''
Link: http://soroush.secproject.com/downloadable/iis_tilde_shortname_disclosur
e.txt

Exploit-db link: www.exploit-db.com/exploits/19525/

----------------------------

Security Research - IIS Short File/Folder Name Disclosure

Website : http://soroush.secproject.com/blog/

I. BACKGROUND
---------------------

"IIS is a web server application and set of 
feature extension modules created by Microsoft for use with Microsoft Windows.
IIS is the third most popular server in the world." (Wikipedia)

II. DESCRIPTION
---------------------

Vulnerability Research Team discovered a vulnerability
in Microsoft IIS.

The vulnerability is caused by a tilde character "~" in a Get request, which could allow remote attackers
to diclose File and Folder names.

III. AFFECTED PRODUCTS
---------------------------

IIS 1.0, Windows NT 3.51 
IIS 2.0, Windows NT 4.0
IIS 3.0, Windows NT 4.0 Service Pack 2
IIS 4.0, Windows NT 4.0 Option Pack
IIS 5.0, Windows 2000
IIS 5.1, Windows XP Professional and Windows XP Media Center Edition 
IIS 6.0, Windows Server 2003 and Windows XP Professional x64 Edition
IIS 7.0, Windows Server 2008 and Windows Vista
IIS 7.5, Windows 7 (error remotely enabled or no web.config)
IIS 7.5, Windows 2008 (classic pipeline mode)

Note: Does not work when IIS uses .Net Framework 4.

IV. Binary Analysis & Exploits/PoCs
---------------------------------------
Tilde character "~" can be used to find short names of files and folders when the website is running on IIS.
The attacker can find important file and folders that they are not normaly visible.
In-depth technical analysis of the vulnerability and a functional exploit
are available through:

http://soroush.secproject.com/blog/2012/06/microsoft-iis-tilde-character
-vulnerabilityfeature-short-filefolder-name-disclosure/

V. SOLUTION
----------------

There are still workarounds through Vendor and security vendors.
Using a configured WAF may be usefull (discarding web requests including the tilde "~" character).

VI. CREDIT
--------------

This vulnerability was discovered by:

Soroush Dalili (@irsdl)
Ali Abbasnejad

VII. REFERENCES
----------------------

http://support.microsoft.com/kb/142982/en-us
http://soroush.secproject.com/blog/2010/07/iis5-1-directory-authenticati
on-bypass-by-using-i30index_allocation/

VIII. DISCLOSURE TIMELINE
-----------------------------

2010-08-01 - Vulnerability Discovered
2010-08-03 - Vendor Informed
2010-12-01 - Vendor 1st Response
2011-01-04 - Vendor 2nd Response (next version fix)
2012-06-29 - Public Disclosure

Research Link (More Details): http://soroush.secproject.com/downloadable/microsoft_iis_tilde_character
_vulnerability_feature.pdf
'''

import requests
from dummy import *

info = {
	'NAME':'IIS Short File/Folder Name Disclosure',
	'AUTHOR':'zero,yangbh',
	'TIME':'20140731',
	'WEB':'https://www.yascanner.com/#!/n/52',
	'DESCRIPTION':''
}

def Audit(services):
	retinfo = None
	output = ''
	if services.has_key('url'):
		if services.has_key('HTTPServer') and services['HTTPServer'].lower().find('iis') == -1:
			return (retinfo,output)
		output += 'plugin run' + os.linesep
		url = services['url']
		try:
			# changed
			respone = requests.get(url + '/*~1.*/x.aspx')
			if respone.status_code == 404:
				respone = requests.get(url + '/ooxx*~1.*/x.aspx')
				if respone.status_code == 400:
					retinfo = {'level':'medium','content':url}
		except Exception,e:
			print 'Exception:\t',e
	return (retinfo,output)

# ----------------------------------------------------------------------------------------------------
#	untest yet
# ----------------------------------------------------------------------------------------------------
if __name__ == '__main__':
	url='http://fxs-test.eguan.cn/'
	if len(sys.argv) ==  2:
		url = sys.argv[1]
	services = {'url':url}
	pprint(Audit(services))
	pprint(services)