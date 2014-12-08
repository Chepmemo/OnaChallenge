<?php
	require_once dirname(__FILE__).'/OnaListener.php';
	class OnaProcessor {
		private $url = "https://raw.githubusercontent.com/onaio/ona-tech/master/data/water_points.json";
		private $file = "water_points.json";

		private $listener = null;
		private $stream =  null;
		private $result = null;

		private function getResult(){
			try {
			
				$this->stream = fopen($this->url, 'r');
				$this->listener = new OnaListener();
				$this->parser = new JsonStreamingParser_Parser($this->stream, $this->listener);
				$this->parser->parse();
			  
			} catch (Exception $e) {
			
			  fclose($this->stream);
			  throw $e;
			  
			}
			
			return $this->listener->get_result();
		}
		
		public function finalizeResult(){
		
			$this->result = $this->getResult();
			
			//$this->result['functional'] = $this->functional;
			//$this->result['non_functional'] = $this->non_functional;
			
			foreach($this->result['communities'] as $key=>$community){
			
				$total = $community['functional'] + $community['non_functional'];
				
				$percent_no_functional = $community['non_functional'] / $total * 100;
				
				$this->result['communities'][$key]['percent_non_funtional'] = $percent_no_functional;
			}
			$this->rankCommunities();
			return $this->result;
		}
		
		private function rankCommunities(){
			$percentages = $this->initRanks();
			//var_dump($percentages);
			foreach($this->result['communities'] as $key=>$community){
				$percent_non_functional = $this->result['communities'][$key]['percent_non_funtional'];
				
				$rank = array_search($percent_non_functional, $percentages);
				
				$this->result['communities'][$key]['rank'] = $rank+1;//our $percentages array is 0-indexed
			}
		}
		
		private function initRanks(){
			$percentages = array();
			foreach($this->result['communities'] as $key=>$community){
			
				$this->result['communities'][$key]['rank'] = 0;
				array_push($percentages, $this->result['communities'][$key]['percent_non_funtional']);
			}
			
			rsort($percentages);
			
			return $percentages;
		}
	}
	
	$onaProcessor = new OnaProcessor();
	
	$result = $onaProcessor->finalizeResult();
	
	var_dump($result);
?>