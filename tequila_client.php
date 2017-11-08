<?php

class TequilaClient {
    var $sServerUrl = "https://tequila.epfl.ch/cgi-bin/tequila";

  /* GOAL : Launch the user authentication 
   * Precondition: The user is *not* logged in
   */
  function Authenticate ($urlaccess = '') {
    $request_key = $this->createRequest ($urlaccess);
    $url = $this->getAuthenticationUrl ($request_key);
    header ('Location: ' . $url);
    exit;
  }

  /*
      GOAL : Sends an authentication request to Tequila
  */
  function createRequest ($urlaccess = '') {

    /* If application URL not initialized,
       we try to generate it automatically */
    if (empty ($urlaccess)) {
      $urlaccess = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))
        ? 'https://' : 'http://')
	. $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
      if (isset($_SERVER['PATH_INFO'])) {
        $urlaccess .= $_SERVER['PATH_INFO'];
      }
      if (isset($_SERVER['QUERY_STRING'])) {
        $urlaccess .= '?' . $_SERVER['QUERY_STRING'];
      }
    }
    
    /* Request creation */
    $requestInfos = array();
    $requestInfos ['urlaccess'] = $urlaccess;

    if (!empty ($this->sApplicationName))
      $requestInfos ['service'] = $this->sApplicationName;
    if (!empty ($this->aWantedRights))
      $requestInfos ['wantright'] = implode($this->aWantedRights, '+');
    if (!empty ($this->aWantedRoles))
      $requestInfos ['wantrole'] =  implode($this->aWantedRoles, '+');
    if (!empty ($this->aWantedAttributes)) 
      $requestInfos ['request'] = implode ($this->aWantedAttributes, '+');
    if (!empty ($this->aWishedAttributes))
      $requestInfos ['wish'] = implode ($this->aWishedAttributes, '+');
    if (!empty ($this->aWantedGroups))
      $requestInfos ['belongs'] = implode($this->aWantedGroups, '+');
    if (!empty ($this->sCustomFilter))
      $requestInfos ['require'] = $this->sCustomFilter;
    if (!empty ($this->sAllowsFilter))
      $requestInfos ['allows'] = $this->sAllowsFilter;
    if (!empty ($this->iLanguage))
      $requestInfos ['language'] = $this->aLanguages [$this->iLanguage];
	  
    /* Asking tequila */
    $response = $this->askTequila ('createrequest', $requestInfos);
    return substr (trim ($response), 4); // 4 = strlen ('key=')
  }
  function getAuthenticationUrl ($request_key) {
	return sprintf('%s/requestauth?requestkey=%s',
		$this->sServerUrl,
		$request_key);    	   
  }

  /* GOAL : Checks that user has correctly authenticated and retrieves its data.
     Precondition: Call this when the query string contains ?key= on the redirect
     path back from Tequila. $sessionkey should be the value of ?key=

            @return mixed
  */
  function fetchAttributes ($sessionkey) {
    $fields = array ('key' => $sessionkey);
    $response = $this->askTequila ('fetchattributes', $fields);
    if (!$response) die("Unknown Tequila key: $sessionkey");

    $result = array ();
    $attributes = explode ("\n", $response);
    
    /* Saving returned attributes */
    foreach ($attributes as $attribute) {
      $attribute = trim ($attribute);
      if (!$attribute)  continue;	  
      list ($key, $val) = explode ('=', $attribute,2);
      //if ($key ==  'key') { $this->key  = $val; }
      //if ($key ==  'org') { $this->org  = $val; }
      //if ($key == 'user') { $this->user = $val; }
      //if ($key == 'host') { $this->host = $val; }
      $result [$key] = $val;
    }
    return $result;
  }

    function askTequila ($type, $fields = array()) {
        //Use the CURL object in order to communicate with tequila.epfl.ch
        $ch = curl_init ();
    
        curl_setopt ($ch, CURLOPT_HEADER,         false);
        curl_setopt ($ch, CURLOPT_POST,           true);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);

        $url = $this->sServerUrl;
        switch ($type) {
        case 'createrequest':
            $url .= '/createrequest';
            break;
	
        case 'fetchattributes':
            $url .= '/fetchattributes';
            break;
	
        case 'config':
            $url .= '/getconfig';
            break;
	
        case 'logout':
            $url .= '/logout';
            break;
	
        default:
            return;
        }
        // $url contains the tequila server with the parameters to execute 
        curl_setopt ($ch, CURLOPT_URL, $url);

        /* If fields where passed as parameters, */
        if (is_array ($fields) && count ($fields)) {
            $pFields = array ();
            foreach ($fields as $key => $val) {
                $pFields[] = sprintf('%s=%s', $key, $val);
            }
            $query = implode("\n", $pFields) . "\n";
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $query);
        }    
        $response = curl_exec ($ch);
        // If connexion failed (HTTP code 200 <=> OK)
        if (curl_getinfo ($ch, CURLINFO_HTTP_CODE) != '200') {
            $response = false;
        }
        curl_close ($ch);
        return $response;
    }
}

