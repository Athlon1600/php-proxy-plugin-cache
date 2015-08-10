<?php

namespace Proxy\Plugin;

use Proxy\Plugin\AbstractPlugin;
use Proxy\Event\ProxyEvent;

use Proxy\Http\Request;
use Proxy\Http\Response;

use Predis\Client;

class CachePlugin extends AbstractPlugin {

	private $redis;
	
	public function __construct(){
		$this->redis = new Client();
	}
	
	private function getCacheKey(Request $request){
	
		// If Vary headers have been passed in, fetch each header and add it to the cache key.
		
		$url = $request->getUri();
		$key = 'cache:'.base64_encode($url);
		
		return $key;
	}
	
	private function parseCacheControl($header){

		$cacheControl = array();
	
		if(preg_match_all('/([a-zA-Z_-]+)\s*(?:=([^,]+))?/', $header, $matches, PREG_SET_ORDER)){
		
			foreach($matches as $match){
				$cacheControl[strtolower($match[1])] = isset($match[2]) ? $match[2] : true;
			}
		}
		
		return $cacheControl;
	}
	
	private function canCache(Response $response){
	
		// mime types to cache
		$types = array(
			'image/jpeg', 'image/png', 'image/gif', 'image/x-icon',
			'application/javascript', 'text/javascript', 'application/x-javascript',
			'text/css'
		);
		
		$headers = $response->headers;
		
		$cache_control = $headers->get('cache-control');
		//$cache_control = $this->parseCacheControl($cache_control);
		$expires = $headers->get('expires');
		
		$content_type = $headers->get('content-type');
		
		// is one of cacheable types
		if(in_array($content_type, $types)){
		
			// does not refuse to be cached
			if(preg_match('/private|no-cache/i', $cache_control, $matches) === 0){
				return true;
			}
		}
		
		return false;
	}
	
	private function store(Request $request, Response $response){
	
		$key = $this->getCacheKey($request);
	
		$data = serialize($response);
		
		// store it!
		$this->redis->set($key, $data);
		$this->redis->expire($key, 60 * 60 * 24 * 7);
	}
	
	private function fetch(Request $request){
		
		$key = $this->getCacheKey($request);
		
		// do we have a response ready stashed in our cache? serialized response when?
		$response = $this->redis->get($key);
		
		if(!$response){
			return false;
		}
		
		return unserialize($data);
	}
	
	// check to see if we need to make a request at all and if response does not already exist in cache
	public function onBeforeRequest(ProxyEvent $event){
	
		$response = $this->fetch($event['request']);
		
		if($response){
		
			// tell proxy client that we do not need to actually perform any HTTP requests
			$event['request']->params->set('request.complete', true);
			
			// replace response
			// TODO: must be a better way
			$event['response']->setContent($data->getContent());
			$event['response']->headers->replace($data->headers->all());
			
			$event['response']->headers->set('X-Cache', 'hit');
			
			// don't need any plugin to modify this request any further because response is already in place
			$event->stopPropagation();
		
		} else {
		
			$event['response']->headers->set('X-Cache', 'miss');
		}
	}
	
	// this is where we decide whether the incoming data is cache worthy
	// TODO: conditional requests
	public function onHeadersReceived(ProxyEvent $event){
	
		if($this->canCache($event['response'])){
		
			$event['request']->params->set('do_cache', true);
			
			// tell any potential plugin that disables buffering (StreamPlugin) to buffer contents anyway
			$event['request']->params->set('force_buffering', true);
		}
	}
	
	// this better be fired as early as possible to avoid storing "proxified" responses
	public function onCompleted(ProxyEvent $event){
	
		if($event['request']->params->has('do_cache')){
			$this->store($event['request'], $event['response']);
		}
	}
}

?>