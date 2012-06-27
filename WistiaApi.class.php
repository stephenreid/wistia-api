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
			$this->apiKey = $apiKey;
		}
	}
	/**
	 * projectCreate
	 * Enter description here ...
	 * @param array $projectData assosciative array. Keys: name,(adminEmail),(anonymousCanUpload),(anonymousCanDownload),(public)
	 * @return stdObject wistiaProject
	 */
	public function projectCreate($projectData)
	{
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
	public function projectUpdate($project)
	{
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
	public function mediaList($projectId = null)
	{
		if(!isset($this->cache['medias'][$projectId])){
			$params = array();
			if($projectId){
				$params['project_id']=$projectId;
			}
			$this->cache['medias'][$projectId] = $this->sendRequest('medias',$params);
		}
		return $this->cache['medias'][$projectId];
	}
	/**
	 * mediaShow
	 * Get a video's details including its name, url, embed code, thumbnails, etc.
	 * @param int $id ie 7880 the wistia identifier for a video
	 * @return stdObject Video
	 */
	public function mediaShow($id)
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
	public function mediaShowStats($id)
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
	public function mediaUpdate($media)
	{
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
	private function sendRequest($module,$params=null)
	{
		//build our url
		$url = self::WISTIA_BASE_URL.$module.'.'.$this->format;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, 'api'.':'.$this->apiKey);
		//Set our aparams if we have them
		if($params){
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
		}
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		$result = json_decode($result);
		return $result;	
	}
}

