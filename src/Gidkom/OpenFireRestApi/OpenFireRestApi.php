<?php
	
namespace Gidkom\OpenFireRestApi;

use GuzzleHttp\Client;

class OpenFireRestApi
{
    public $host		= 'localhost';
		public $port		= '9090';
		public $secret		= 'SuperSecret';
		public $useSSL		= false;
    public $client;
		
		protected $params   = [];
		protected $plugin;

    /**
     * Class Contructor
     *
		 * @param	string	$host					Name of the host to connect to
		 * @param string	$pluginPath		Path to where the plugin is located
     */
    public function __construct($host, $pluginPath = '/plugins/restapi/v1')
    {
        $this->client = new Client();
				$this->host = $host;
        $this->plugin = $pluginPath;
    }

    /**
     * Make the request and analyze the result
     *
     * @param   string          $type           Request method
     * @param   string          $endpoint       Api request endpoint
     * @param   array           $params         Parameters
     * @return  array|false                     Array with data or error, or False when something went fully wrong
     */
    
    private function doRequest($type, $endpoint, $params=[])
    {
    	$base = ($this->useSSL) ? "https" : "http";
    	$url = $base . "://" . $this->host . ":" .$this->port.$this->plugin.$endpoint;
    	$headers = array(
  			'Accept' => 'application/json',
  			'Authorization' => $this->secret
  		);

        $body = json_encode($params);

        switch ($type) {
            case 'get':
                $result = $this->client->get($url, compact('headers'));
                break;
            case 'post':
                $headers += ['Content-Type'=>'application/json'];
                $result = $this->client->post($url, compact('headers','body'));
                break;
            case 'delete':
                $headers += ['Content-Type'=>'application/json'];
                $result = $this->client->delete($url, compact('headers','body'));
                break;
            case 'put':
                $headers += ['Content-Type'=>'application/json'];
                $result = $this->client->put($url, compact('headers','body'));
                break;
            default:
                $result = null;
                break;
        }
        
        if ($result->getStatusCode() == 200 || $result->getStatusCode() == 201) {
					return json_decode($result->getBody());
        }
        return array('status'=>false, 'message'=>$result->getBody());
    	
    }
    

    /**
     * Get all registered users
     *
     * @return json|false       Json with data or error, or False when something went fully wrong
     */
    public function getUsers()
    {
    	$endpoint = '/users';        
    	return $this->doRequest('get',$endpoint);
    }


    /**
     * Get information for a specified user
     *
     * @return json|false       Json with data or error, or False when something went fully wrong
     */
    public function getUser($username)
    {
        $endpoint = '/users/'.$username; 
        return $this->doRequest('get', $endpoint);
    }


    /**
     * Creates a new OpenFire user
     *
     * @param   string          $username   Username
     * @param   string          $password   Password
     * @param   string|false    $name       Name    (Optional)
     * @param   string|false    $email      Email   (Optional)
     * @param   string[]|false  $groups     Groups  (Optional)
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function addUser($username, $password, $name=false, $email=false, $groups=false)
    {
        $endpoint = '/users'; 
        return $this->doRequest('post', $endpoint, compact('username', 'password','name','email', 'groups'));
    }


    /**
     * Deletes an OpenFire user
     *
     * @param   string          $username   Username
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function deleteUser($username)
    {
        $endpoint = '/users/'.$username; 
        return $this->doRequest('delete', $endpoint);
    }

    /**
     * Updates an OpenFire user
     *
     * @param   string          $username   Username
     * @param   string|false    $password   Password (Optional)
     * @param   string|false    $name       Name (Optional)
     * @param   string|false    $email      Email (Optional)
     * @param   string[]|false  $groups     Groups (Optional)
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function updateUser($username, $password, $name=false, $email=false, $groups=false)
    {
        $endpoint = '/users/'.$username; 
        return $this->doRequest('put', $endpoint, compact('username', 'password','name','email', 'groups'));
    }

     /**
     * locks/Disables an OpenFire user
     *
     * @param   string          $username   Username
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function lockoutUser($username)
    {
        $endpoint = '/lockouts/'.$username; 
        return $this->doRequest('post', $endpoint);
    }


    /**
     * unlocks an OpenFire user
     *
     * @param   string          $username   Username
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function unlockUser($username)
    {
        $endpoint = '/lockouts/'.$username; 
        return $this->doRequest('delete', $endpoint);
    }


    /**
     * Adds to this OpenFire user's roster
     *
     * @param   string          $username       Username
     * @param   string          $jid            JID
     * @param   string|false    $name           Name         (Optional)
     * @param   int|false       $subscription   Subscription (Optional)
     * @return  json|false                     Json with data or error, or False when something went fully wrong
     */
    public function addToRoster($username, $jid, $name=false, $subscription=false)
    {
        $endpoint = '/users/'.$username.'/roster';
        return $this->doRequest('post', $endpoint, compact('jid','name','subscription'));
    }


    /**
     * Removes from this OpenFire user's roster
     *
     * @param   string          $username   Username
     * @param   string          $jid        JID
     * @return  json|false                 Json with data or error, or False when something went fully wrong
     */
    public function deleteFromRoster($username, $jid)
    {
        $endpoint = '/users/'.$username.'/roster/'.$jid;
        return $this->doRequest('delete', $endpoint, $jid);
    }

    /**
     * Updates this OpenFire user's roster
     *
     * @param   string          $username           Username
     * @param   string          $jid                 JID
     * @param   string|false    $nickname           Nick Name (Optional)
     * @param   int|false       $subscriptionType   Subscription (Optional)
     * @return  json|false                          Json with data or error, or False when something went fully wrong
     */
    public function updateRoster($username, $jid, $nickname=false, $subscriptionType=false)
    {
        $endpoint = '/users/'.$username.'/roster/'.$jid;
        return $this->doRequest('put', $endpoint, $jid, compact('jid','username','subscriptionType'));     
    }

    /**
     * Get all groups
     *
     * @return  json|false      Json with data or error, or False when something went fully wrong
     */
    public function getGroups()
    {
        $endpoint = '/groups';
        return $this->doRequest('get', $endpoint);
    }

    /**
     *  Retrieve a group
     *
     * @param  string   $name                       Name of group
     * @return  json|false                          Json with data or error, or False when something went fully wrong
     */
    public function getGroup($name)
    {
        $endpoint = '/groups/'.$name;
        return $this->doRequest('get', $endpoint);
    }

    /**
     * Create a group 
     *
     * @param   string   $name                      Name of the group
     * @param   string   $description               Some description of the group
     *
     * @return  json|false                          Json with data or error, or False when something went fully wrong
     */
    public function createGroup($name, $description = false)
    {
        $endpoint = '/groups/';
        return $this->doRequest('post', $endpoint, compact('name','description'));
    }

    /**
     * Delete a group
     *
     * @param   string      $name               Name of the Group to delete
     * @return  json|false                          Json with data or error, or False when something went fully wrong
     */
    public function deleteGroup($name)
    {
        $endpoint = '/groups/'.$name;
        return $this->doRequest('delete', $endpoint);
    }

    /**
     * Update a group (description)
     *
     * @param   string      $name               Name of group
     * @param   string      $description        Some description of the group
     *
     */
    public function updateGroup($name,  $description)
    {
        $endpoint = '/groups/'.$name;
        return $this->doRequest('put', $endpoint, compact('name','description'));
    }
		
		/**
		* Update a chatroom's settings
		*
		*	@param 	string 	$roomName The name of the room to update
		* @param	array 	$settings Associative array containing the settings to apply to the room
		*/
		public function updateRoom($roomName, array $settings)
		{
			$settings['roomName'] = $roomName;
			
			$ep = '/chatrooms/' . $settings['roomName'];
			
			return $this->doRequest('put', $ep, $settings);
		}
		
		/**
		* Creates a chatroom
		* @param  array $roomSettings Settings of the room
		*/
		public function createRoom(array $roomSettings)
		{
			$ep = '/chatrooms/';
			
			return $this->doRequest('post', $ep, $roomSettings);
		}
		
		/**
		* @param string $roomName 
		*/
		public function getRoomInfo($roomName)
		{
			$ep = '/chatrooms/' . $roomName;
			
			return $this->doRequest('get', $ep);
		}
		
		/**
		* Deletes a chatroom
		* @param	string	$roomName Name of the room to delete
		*/
		public function deleteRoom($roomName)
		{
			$ep = '/chatrooms/' . $roomName;
			
			return $this->doRequest('delete', $ep);
		}
}