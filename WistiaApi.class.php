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
	
	public function __construct($apiKey = null)
	{
		if($apiKey){
			$this->apiKey = $apiKey;
		}
	}
	public function getProjects()
	{
		if(!isset($this->cache['projects'])){
			$this->cache['projects'] = $this->sendRequest('projects');
		}
		return $this->cache['projects'];
	}
	public function getVideos($projectId = null)
	{
		if(!isset($this->cache['videos'.$projectId])){
			$params = array();
			if($projectId){
				$params['project_id']=$projectId;
			}
			$this->cache['videos'.$projectId] = $this->sendRequest('medias',$params);
		}
		return $this->cache['videos'.$projectId];
	}
	public function getVideo($id)
	{
		if(!isset($this->cache['video'.$id])){
			$this->cache['videos'.$id] = $this->sendRequest('medias/'.$id);
		}
		return $this->cache['videos'.$projectId];
	}
	public function getVideoStats($id)
	{
		if(!isset($this->cache['videoStats'.$id])){
			$params = array();

			$this->cache['videoStats'.$id] = $this->sendRequest('medias/'.$id.'/stats');
		}
		return $this->cache['videoStats'.$projectId];
	}
	
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
