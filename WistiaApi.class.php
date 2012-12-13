<?php
/**
 * WistiaApi
 * A simple PHP Class for interfacing with the Wistia Data API
 * Starting Point can be seen here http://dev-forum.wistia.com/discussion/6/php-libraries - Thanks Brian Kutyah
 * I Don't need to write anything in my current scope, but this should be added at some point
 * Not sure why I prematurely optimized with a cache array
 * @since 6/26/2012
 */


class WistiaApi
{
	protected $format = 'json';
	protected $apiKey = null;
	protected $cache = array();
	protected $debug = false;
	public $response = '';


	const WISTIA_BASE_URL = "https://api.wistia.com/v1/";//not https is not secure.
	
	/**
	 * constructor
	 * Builds a new instance of this class, stores an authenticator api key
	 * @param string $apiKey get an api key from your wistia account
	 * @return boolean read or not
	 */
	public function __construct($apiKey = null)
	{
		if($apiKey){
			$this->cache = array();
			$this->apiKey = $apiKey;
		}
	}
	/**
	 * accountRead
	 * Gets the account as a stdObject
	 * Properties id,name,url
	 * @return stdClass account
	 */
	public function accountRead()
	{
		if(!isset($this->cache['account'])){
			$this->cache['account'] = $this->sendRequest('account');
		}
		return $this->cache['account'];
	}
	/**
	 * accountStats
	 * Get some overall statistics for the account
	 * @since 12/13/2012
	 * @return stdClass accountStat
	 */
	public function accountStats()
	{
		if(!isset($this->cache['accountStats'])){
			$this->cache['accountStats'] = $this->sendRequest('stats/account');
		}
		return 	$this->cache['accountStats'] = $this->sendRequest('stats/account');
		
	}
	/**
	 * eventRead
	 * gets the details from any given event
	 * @param string $key
	 * @return stdClass event
	 */
	public function eventRead($key = null)
	{
		if(!isset($this->cache['events'][$key])){
			$this->cache['events'][$key] = $this->sendRequest('events',array('event_key'=>$key));
		}
		return $this->cache['events'][$key];
	}
	/**
	 * projectCreate
	 * Enter description here ...
	 * @param array $projectData assosciative array. Keys: name,(adminEmail),(anonymousCanUpload),(anonymousCanDownload),(public)
	 * @return stdObject wistiaProject
	 */
	public function projectCreate($projectData = null)
	{
		if(!$projectData){
			return null;
		}
		//empty our cache
		$this->cache['projects']=null;
		return $this->sendRequest('projects',$projectData);
	}
	/**
	 * projectList
	 * Fetches all of the projects in this account
	 * @return array of stdObjects
	 */
	public function projectList()
	{
		if(!isset($this->cache['projects'])){
			$this->cache['projects'] = $this->sendRequest('projects');
		}
		return $this->cache['projects'];
	}
	/**
	 * projectUpdate
	 * Enter description here ...
	 * @param int $id wistiaProjectId
	 * @param stdObject $project name,(adminEmail),(anonymousCanUpload),(anonymousCanDownload),(public)
	 * @return stdObject $project
	 */
	public function projectUpdate($project = null)
	{
		if(!$project){
			return false;
		}
		//make sure that they are different
		$id = $project->id;
		if(count(array_diff(get_object_vars($this->cache['projects'][$id]),get_object_vars($project)))==0){
			return $this->cache['projects'][$id];
		}
		//empty our cache
		$this->cache['projects']=null;
		return $this->sendRequest('projects/'.$id,$projectData);
	}
	/**
	 * mediaList
	 * Enter description here ...
	 * @param int $projectId an optional filter to show only videos from a specific project
	 * @return array stdObjects
	 */
	public function mediaList($projectId = null, $page = 1, $perPage = 100, $full=true)
	{
		if(!isset($this->cache['medias'][$projectId]) || $page > 1){
			$params = array(
				'page' => $page,
				'per_page' => $perPage
			);
			if($projectId){
				$params['project_id']=$projectId;
			}
			$medias = $this->sendRequest('medias',$params);

		}
		//if we received the max possible, query the next page
		if($full && count($medias) == $perPage){
			$nextPage = $this->mediaList($projectId,$page+=1,$perPage);
			if(count($nextPage)>0){
				$medias = array_merge($medias,$nextPage);
			}
		}
		return $medias;
	}
	/**
	 * mediaShow
	 * Get a video's details including its name, url, embed code, thumbnails, etc.
	 * @param int $id ie 7880 the wistia identifier for a video
	 * @return stdObject Video
	 */
	public function mediaShow($id = null)
	{
		if(!isset($this->cache['media'][$id])){
			$this->cache['media'][$id] = $this->sendRequest('medias/'.$id);
		}
		return $this->cache['media'][$id];
	}
	/**
	 * mediaShowStats
	 * Gets the cumulative stats for a given video id
	 * @param int $id a wistia video id
	 * @return stdObject videoStats
	 */
	public function mediaShowStats($id = null)
	{
		if(!isset($this->cache['mediaStats'][$id])){
			$this->cache['mediaStats'][$id] = $this->sendRequest('medias/'.$id.'/stats');
		}
		return $this->cache['mediaStats'][$id];
	}
	/**
	 * mediaUpdate
	 * Update the media's name, description, and new_still_media_id
	 * @param stdObject $media
	 */
	public function mediaUpdate($media = null)
	{
		if(!$media){
			return false;
		}
		$id = $media->id;
		$params = array();
		if($media->name != $this->cache['media']['id']->name){
			$params['name']=$media->name;
		}
		if($media->description != $this->cache['media']['id']->description){
			$params['descriptions']=$media->descriptions;
		}
		return $this->cache['media'][$id] = $this->sendRequest('medias/'.$id,$params);
		
		
	}
	/**
	 * sendRequest
	 * Enter description here ...
	 * @param strings $module
	 * @param array $params
	 * @return mixed array/stdobject (from json_decode)
	 */
	protected function sendRequest($module, $params=null)
	{
		//build our url
		$url = self::WISTIA_BASE_URL.$module.'.'.$this->format;
		
		//Set our aparams if we have them
		if($params){
			$url.='?'.http_build_query($params);
		}
		if($this->debug){
			echo 'Sending Request: '.$url;
		}
	
		$result = $this->__send($url,$params);

		if($this->debug){
			echo 'Received: '.$result;
		}
		$result = json_decode($result);
		return $result;
	}
	protected function __send($url)
	{
		$username = 'api';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $username .':'.$this->apiKey);
		
		$result = curl_exec($ch);
		curl_close($ch);
		$this->response = $result;
		return $result;
	}
	public function enableDebugging()
	{
		$this->debug = true;
	}
}
class WistiaException extends Exception{
	
}

