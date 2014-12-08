<?php
	require_once dirname(__FILE__).'/../src/JsonStreamingParser/Listener.php';
	require_once dirname(__FILE__).'/../src/JsonStreamingParser/Parser.php';

	class OnaListener implements JsonStreamingParser_Listener {
	  private $_json;

	  private $_stack;
	  private $_key;
	  
	  private $functional = 0;
	  private $non_functional = 0;
	  
	  private $objects_read = 0;
	  
	  private $result = array(
		'functional' => 0,
		'non_functional' => 0,
		'communities' => array()
	  );

	  public function file_position($line, $char) {
		
	  }

	  public function get_json() {
		return $this->_json;
	  }

	  public function start_document() {
		$this->_stack = array();

		$this->_key = null;
	  }

	  public function end_document() {
		//done
	  }

	  public function start_object() {
		array_push($this->_stack, array());
	  }

	  public function end_object() {
		$obj = array_pop($this->_stack);
		
		if(!empty($this->_stack)) {
		
		  if(array_key_exists('water_functioning', $obj)){
		  
			if($obj['water_functioning'] == 'yes'){
				$this->result['functional']+=1;
			} else {
				$this->result['non_functional']+=1;
			}
		  }
		  
		  if(array_key_exists('communities_villages', $obj)){
		 
			$communities = $this->result['communities'];
			$community_name = $obj['communities_villages'];
			
			if(array_key_exists($community_name, $communities)){
				
				//$comunity_functional = $community['functional'];
				//$comunity_non_functional = $community['non_functional'];
				
				if($obj['water_functioning'] == 'yes'){
				
					$this->result['communities'][$community_name]['functional'] += 1;
				
				} else {
				
					$this->result['communities'][$community_name]['non_functional'] += 1;
					
				}
				
			} else {
				$community = array(
					'functional' => 1,
					'non_functional' => 1
				);
				
				$this->result['communities'][$community_name] = $community;
				
			}
		  }
		  //echo("Read ". $this->objects_read++." Objects\n");
		} else {
			//echo ("Done!");
			$this->end_document();
		}
	  }

	  public function start_array() {
		$this->start_object();
	  }

	  public function end_array() {
		$this->end_object();
	  }

	  // Key will always be a string
	  public function key($key) {
		$this->_key = $key;
	  }

	  // Note that value may be a string, integer, boolean, null
	  public function value($value) {
		$obj = array_pop($this->_stack);
		if ($this->_key) {
		  $obj[$this->_key] = $value;
		  $this->_key = null;
		} else {
		  array_push($obj, $value);
		}
		array_push($this->_stack, $obj);
	  }

	  public function whitespace($whitespace) {
		// do nothing
	  }
	  
	  public function get_result(){
		return $this->result;
	  }
	}